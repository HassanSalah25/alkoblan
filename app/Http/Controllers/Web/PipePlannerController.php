<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PipePlannerProjectRequest;
use App\Jobs\RunPipePlanningAnalysis;
use App\Models\ContactMessage;
use App\Models\PipePlanningAnalysis;
use App\Models\PipePlanningImage;
use App\Models\PipePlanningItem;
use App\Models\PipePlanningProject;
use App\Models\PipePlanningQuoteRequest;
use App\Services\PipePlanner\PipePlanningAnalyzer;
use App\Services\PipePlanner\PlannerSettings;
use App\Services\PipePlanner\ProjectAccess;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PipePlannerController extends Controller
{
    public function __construct(
        protected ProjectAccess $access,
        protected PlannerSettings $settings,
    ) {}

    protected function ar(): bool
    {
        return app()->getLocale() === 'ar';
    }

    protected function error(string $code, string $en, string $ar, int $status): JsonResponse
    {
        return response()->json(['error' => $code, 'message' => $this->ar() ? $ar : $en], $status);
    }

    // ------------------------------------------------------------------
    // Page + project CRUD
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $limits = [
            'max_images' => $this->settings->get('max_images'),
            'max_image_kb' => $this->settings->get('max_image_kb'),
            'mimes' => config('pipe_planner.allowed_mimes'),
        ];
        $available = $this->settings->apiKeyConfigured() && filled($this->settings->get('model'));

        return view('pages.pipe-planner', [
            'limits' => $limits,
            'available' => $available,
            // Everything the wizard script needs; deliberately contains no secrets.
            'plannerConfig' => [
                'base' => url('/pipe-planner'),
                'contactUrl' => route('contact'),
                'limits' => $limits,
                'available' => $available,
                'authenticated' => (bool) $request->user(),
                'customer' => $request->user()?->only(['name', 'email', 'phone']),
                'initialReference' => is_string($request->query('project')) ? Str::limit($request->query('project'), 20, '') : null,
                'i18n' => $this->uiStrings(),
            ],
        ]);
    }

    /** "pp." strings for the current locale from resources/lang/{locale}.json, for the wizard script. */
    protected function uiStrings(): array
    {
        $lines = app('translator')->getLoader()->load(app()->getLocale(), '*', '*');
        if (app()->getLocale() !== 'en') {
            $lines += app('translator')->getLoader()->load('en', '*', '*');
        }

        return collect($lines)->filter(fn ($v, $k) => str_starts_with($k, 'pp.'))->all();
    }

    public function projects(Request $request): JsonResponse
    {
        $projects = $this->access->query($request)->latest('updated_at')->limit(30)->get();

        return response()->json(['data' => $projects->map(fn ($p) => [
            'reference' => $p->reference,
            'project_type' => $p->project_type,
            'system_type' => $p->system_type,
            'analysis_status' => $p->analysis_status,
            'updated_at' => $p->updated_at->toIso8601String(),
        ])]);
    }

    public function store(PipePlannerProjectRequest $request): JsonResponse
    {
        $project = PipePlanningProject::create($request->validated() + $this->access->ownerAttributes($request) + [
            'locale' => app()->getLocale(),
        ]);

        return response()->json(['data' => $this->projectPayload($project->fresh())], 201);
    }

    public function show(Request $request, PipePlanningProject $project): JsonResponse
    {
        $this->access->authorize($request, $project);

        return response()->json(['data' => $this->projectPayload($project)]);
    }

    public function update(PipePlannerProjectRequest $request, PipePlanningProject $project): JsonResponse
    {
        $this->access->authorize($request, $project);
        $project->update($request->validated() + ['locale' => app()->getLocale()]);

        return response()->json(['data' => $this->projectPayload($project->fresh())]);
    }

    public function destroy(Request $request, PipePlanningProject $project): JsonResponse
    {
        $this->access->authorize($request, $project);
        Storage::disk(config('pipe_planner.disk'))->deleteDirectory('pipe-planner/'.$project->id);
        $project->delete();

        return response()->json(['deleted' => true]);
    }

    // ------------------------------------------------------------------
    // Images (stored on a private disk; served through signed, owner-checked URLs)
    // ------------------------------------------------------------------

    public function uploadImage(Request $request, PipePlanningProject $project): JsonResponse
    {
        $this->access->authorize($request, $project);
        $max = $this->settings->get('max_images');
        if ($project->images()->count() >= $max) {
            return $this->error('too_many_images', "You can upload up to {$max} photos.", "يمكنك رفع {$max} صور كحد أقصى.", 422);
        }

        $request->validate([
            'image' => 'required|file|image|mimetypes:'.implode(',', config('pipe_planner.allowed_mimes')).'|max:'.$this->settings->get('max_image_kb'),
            'caption' => 'nullable|string|max:300',
            'has_scale_reference' => 'nullable|boolean',
        ], PipePlannerProjectRequest::localizedMessages());

        $file = $request->file('image');
        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];
        if (! $width || ! $height) {
            return $this->error('invalid_image', 'The file could not be read as an image.', 'تعذر قراءة الملف كصورة.', 422);
        }

        $mime = $file->getMimeType();
        $bytes = $this->stripMetadata($file->getRealPath(), $mime) ?? file_get_contents($file->getRealPath());
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
        $key = 'pipe-planner/'.$project->id.'/'.Str::uuid().'.'.$ext;
        Storage::disk(config('pipe_planner.disk'))->put($key, $bytes);

        $image = $project->images()->create([
            'storage_key' => $key,
            'original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
            'mime_type' => $mime,
            'size_bytes' => strlen($bytes),
            'width' => $width,
            'height' => $height,
            'sha256' => hash('sha256', $bytes),
            'sort_order' => (int) $project->images()->max('sort_order') + 1,
            'caption' => $request->input('caption'),
            'has_scale_reference' => $request->boolean('has_scale_reference'),
        ]);
        $project->touch();

        return response()->json(['data' => $this->imagePayload($image)], 201);
    }

    /** Re-encode JPEG/PNG/WEBP via GD to drop EXIF (e.g. GPS). Returns null when GD is unavailable. */
    protected function stripMetadata(string $path, string $mime): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
        if (! $img) {
            return null;
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = (@exif_read_data($path) ?: [])['Orientation'] ?? 1;
            $img = match ((int) $orientation) {
                3 => imagerotate($img, 180, 0),
                6 => imagerotate($img, -90, 0),
                8 => imagerotate($img, 90, 0),
                default => $img,
            };
        }
        ob_start();
        match ($mime) {
            'image/jpeg' => imagejpeg($img, null, 88),
            'image/png' => imagepng($img),
            'image/webp' => imagewebp($img, null, 88),
        };

        return ob_get_clean() ?: null;
    }

    public function updateImage(Request $request, PipePlanningProject $project, PipePlanningImage $image): JsonResponse
    {
        $this->access->authorize($request, $project);
        abort_unless($image->project_id === $project->id, 404);
        $data = $request->validate([
            'caption' => 'nullable|string|max:300',
            'has_scale_reference' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0|max:1000',
        ], PipePlannerProjectRequest::localizedMessages());
        $image->update($data);

        return response()->json(['data' => $this->imagePayload($image)]);
    }

    public function deleteImage(Request $request, PipePlanningProject $project, PipePlanningImage $image): JsonResponse
    {
        $this->access->authorize($request, $project);
        abort_unless($image->project_id === $project->id, 404);
        Storage::disk(config('pipe_planner.disk'))->delete($image->storage_key);
        $image->delete();

        return response()->json(['deleted' => true]);
    }

    public function showImage(Request $request, PipePlanningImage $image)
    {
        // Signed URL limits lifetime; ownership is still checked for the current session.
        $this->access->authorize($request, $image->project);
        // StartSession skips AJAX requests when recording the "previous URL" used by back()
        // (e.g. the language switcher); thumbnails must not become that URL.
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        return response(Storage::disk(config('pipe_planner.disk'))->get($image->storage_key), 200, [
            'Content-Type' => $image->mime_type,
            'Cache-Control' => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ------------------------------------------------------------------
    // Analysis
    // ------------------------------------------------------------------

    public function analyze(Request $request, PipePlanningProject $project, PipePlanningAnalyzer $analyzer): JsonResponse
    {
        $this->access->authorize($request, $project);
        $project->load('images');

        if ($project->images->isEmpty()) {
            return $this->error('no_images', 'Upload at least one photo before analysis.', 'يرجى رفع صورة واحدة على الأقل قبل التحليل.', 422);
        }
        if (! $this->settings->apiKeyConfigured() || blank($this->settings->get('model'))) {
            return $this->error('not_configured', 'The AI planner is temporarily unavailable. Please contact our sales team.', 'المخطط الذكي غير متاح حاليًا. يرجى التواصل مع فريق المبيعات.', 503);
        }

        $this->expireStalled($project);
        $active = $project->analyses()->whereIn('status', PipePlanningAnalysis::ACTIVE_STATUSES)->first();
        if ($active) {
            return response()->json(['data' => $this->analysisPayload($active), 'reused' => true], 202);
        }

        $hash = $analyzer->inputHash($project);
        $previous = $project->analyses()->where('status', 'completed')->where('input_hash', $hash)->first();
        if ($previous) {
            return response()->json(['data' => $this->analysisPayload($previous), 'reused' => true]);
        }

        $budget = $this->settings->get('monthly_budget_usd');
        if ($budget > 0 && PipePlanningAnalysis::where('created_at', '>=', now()->startOfMonth())->sum('cost_usd') >= $budget) {
            return $this->error('budget_exhausted', 'The AI planner has reached its usage limit for this month. Please contact our sales team.', 'وصل المخطط الذكي إلى حد الاستخدام لهذا الشهر. يرجى التواصل مع فريق المبيعات.', 503);
        }

        $rateKey = $this->access->rateKey($request);
        if (RateLimiter::tooManyAttempts($rateKey, max(1, $this->settings->get('analyses_per_day')))) {
            return $this->error('rate_limited', 'You have reached the daily analysis limit. Please try again tomorrow.', 'لقد وصلت إلى الحد اليومي للتحليلات. يرجى المحاولة غدًا.', 429);
        }
        RateLimiter::hit($rateKey, 86400);

        $analysis = DB::transaction(function () use ($project, $hash) {
            PipePlanningProject::whereKey($project->id)->lockForUpdate()->first();

            return $project->analyses()->create([
                'version' => (int) $project->analyses()->max('version') + 1,
                'input_hash' => $hash,
                'model' => $this->settings->get('model'),
                'status' => 'queued',
                'stage' => 'uploading',
            ]);
        });
        $project->update(['analysis_status' => 'queued']);

        if (config('pipe_planner.dispatch') === 'after_response') {
            // For hosts without a queue worker: runs in this PHP process once the response is sent.
            RunPipePlanningAnalysis::dispatchAfterResponse($analysis);
        } else {
            RunPipePlanningAnalysis::dispatch($analysis);
        }

        return response()->json(['data' => $this->analysisPayload($analysis->fresh())], 202);
    }

    /**
     * Fail analyses no worker picked up (or that died mid-run) so they stop blocking
     * new attempts and the customer gets a clear message instead of an endless spinner.
     */
    protected function expireStalled(PipePlanningProject $project): void
    {
        $queuedLimit = now()->subMinutes(config('pipe_planner.stall_minutes.queued'));
        $runningLimit = now()->subMinutes(config('pipe_planner.stall_minutes.running'));

        $stalled = $project->analyses()
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('status', 'queued')->where('created_at', '<', $queuedLimit))
                ->orWhere(fn ($q) => $q->where('status', 'running')->where('started_at', '<', $runningLimit)))
            ->get();

        foreach ($stalled as $analysis) {
            $analysis->update([
                'status' => 'failed', 'stage' => null, 'completed_at' => now(),
                'failure_category' => $analysis->status === 'queued' ? 'queue_stalled' : 'job_failed',
            ]);
            Log::warning('pipe_planner.analysis.stalled', ['analysis_id' => $analysis->id, 'failure_category' => $analysis->failure_category]);
        }
        if ($stalled->isNotEmpty()) {
            $project->update(['analysis_status' => 'failed']);
        }
    }

    public function analysis(Request $request, PipePlanningProject $project, int $version): JsonResponse
    {
        $this->access->authorize($request, $project);
        $this->expireStalled($project);
        $analysis = $project->analyses()->where('version', $version)->firstOrFail();

        return response()->json(['data' => $this->analysisPayload($analysis)]);
    }

    public function updateWaste(Request $request, PipePlanningProject $project, int $version, PipePlanningAnalyzer $analyzer): JsonResponse
    {
        $this->access->authorize($request, $project);
        $analysis = $project->analyses()->where('version', $version)->where('status', 'completed')->firstOrFail();
        $data = $request->validate(['waste_allowance_percent' => 'nullable|numeric|min:0|max:30'], PipePlannerProjectRequest::localizedMessages());
        $percent = isset($data['waste_allowance_percent']) ? (float) $data['waste_allowance_percent'] : null;

        $analyzer->applyWaste($analysis, $percent ?: null);
        $project->update(['waste_allowance_percent' => $percent ?: null]);

        return response()->json(['data' => $this->analysisPayload($analysis->fresh())]);
    }

    public function report(Request $request, PipePlanningProject $project, int $version)
    {
        $this->access->authorize($request, $project);
        $analysis = $project->analyses()->where('version', $version)->where('status', 'completed')->firstOrFail();

        return view('pages.pipe-planner-report', [
            'project' => $project,
            'analysis' => $this->analysisPayload($analysis),
        ]);
    }

    // ------------------------------------------------------------------
    // Quotation request -> existing contact_messages workflow
    // ------------------------------------------------------------------

    public function requestQuote(Request $request, PipePlanningProject $project, int $version): JsonResponse
    {
        $this->access->authorize($request, $project);
        $analysis = $project->analyses()->where('version', $version)->where('status', 'completed')->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'message' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1|max:100',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'nullable|numeric|min:0|max:100000',
            'approve_assumptions' => 'accepted',
            'confirm' => 'accepted',
        ], PipePlannerProjectRequest::localizedMessages());

        $itemIds = collect($data['items'])->pluck('id');
        $items = $analysis->items()->with(['product', 'variant'])->whereIn('id', $itemIds)->get()->keyBy('id');
        if ($items->count() !== $itemIds->unique()->count()) {
            return $this->error('invalid_items', 'Some selected items do not belong to this report.', 'بعض العناصر المختارة لا تنتمي إلى هذا التقرير.', 422);
        }

        try {
            $quote = DB::transaction(function () use ($analysis, $project, $data, $items, $request) {
                PipePlanningAnalysis::whereKey($analysis->id)->lockForUpdate()->first();
                if ($analysis->quoteRequest()->exists()) {
                    return null;
                }

                $lines = collect($data['items'])->map(function ($row) use ($items) {
                    /** @var PipePlanningItem $item */
                    $item = $items[$row['id']];

                    return [
                        'item_id' => $item->id,
                        'category' => $item->category,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name,
                        'sku' => $item->variant?->sku ?? $item->product?->sku,
                        'diameter_mm' => $item->diameter_mm,
                        'secondary_diameter_mm' => $item->secondary_diameter_mm,
                        'water' => $item->water,
                        'quantity' => $row['quantity'] ?? $item->quantity,
                        'unit' => $item->unit,
                        'quantity_status' => $item->quantity_status,
                        'match_status' => $item->match_status,
                        'customer_edited' => isset($row['quantity']) && (float) $row['quantity'] !== (float) $item->quantity,
                    ];
                })->values()->all();

                $contact = ContactMessage::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'subject' => 'pipe_planner_quote',
                    'message' => $this->quoteMessage($project, $analysis, $lines, $data['message'] ?? null),
                    'status' => 'new',
                ]);

                return PipePlanningQuoteRequest::create([
                    'project_id' => $project->id,
                    'analysis_id' => $analysis->id,
                    'user_id' => $request->user()?->id,
                    'contact_message_id' => $contact->id,
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'items' => $lines,
                    'approved_assumptions' => $analysis->assumptions ?? [],
                    'message' => $data['message'] ?? null,
                    'status' => 'submitted',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            $quote = null;
        }

        if (! $quote) {
            return $this->error('already_submitted', 'A quotation request was already sent for this report version.', 'تم إرسال طلب عرض سعر لهذه النسخة من التقرير مسبقًا.', 409);
        }

        return response()->json(['data' => ['reference' => $project->reference, 'quote_id' => $quote->id, 'status' => $quote->status]], 201);
    }

    protected function quoteMessage(PipePlanningProject $project, PipePlanningAnalysis $analysis, array $lines, ?string $note): string
    {
        $text = "AI Pipe Planner quotation request\nProject: {$project->reference} (analysis v{$analysis->version})\n"
            ."Type: {$project->project_type} / {$project->project_status} / system: {$project->system_type}\n"
            .'Readiness: '.($analysis->readiness ?? '-')."\n"
            .'Admin: '.route('admin.pipe-planner.show', $project)."\n\nItems:\n";
        foreach ($lines as $l) {
            $text .= sprintf("- %s | %s | %s mm%s | qty: %s %s | %s, %s\n",
                $l['category'], $l['product_name'] ?? 'product match requires verification', $l['diameter_mm'] ?? '?',
                $l['secondary_diameter_mm'] ? 'x'.$l['secondary_diameter_mm'] : '',
                $l['quantity'] ?? 'not calculated', $l['unit'], $l['quantity_status'], $l['match_status']);
        }
        if ($note) {
            $text .= "\nCustomer note:\n".$note;
        }

        return Str::limit($text, 4900);
    }

    // ------------------------------------------------------------------
    // Payloads
    // ------------------------------------------------------------------

    protected function imagePayload(PipePlanningImage $image): array
    {
        return [
            'id' => $image->id,
            'url' => URL::temporarySignedRoute('pipe-planner.images.show', now()->addMinutes(30), ['image' => $image->id]),
            'original_name' => $image->original_name,
            'size_bytes' => $image->size_bytes,
            'width' => $image->width,
            'height' => $image->height,
            'caption' => $image->caption,
            'has_scale_reference' => $image->has_scale_reference,
            'sort_order' => $image->sort_order,
        ];
    }

    protected function projectPayload(PipePlanningProject $project): array
    {
        $project->loadMissing(['images', 'latestAnalysis']);

        return [
            'reference' => $project->reference,
            'project_type' => $project->project_type,
            'project_status' => $project->project_status,
            'system_type' => $project->system_type,
            'scope' => $project->scope,
            'fixtures' => $project->fixtures ?? (object) [],
            'location' => $project->location,
            'notes' => $project->notes,
            'unit' => $project->unit,
            'measurements' => $project->measurements ?? (object) [],
            'answers' => $project->answers ?? [],
            'waste_allowance_percent' => $project->waste_allowance_percent,
            'analysis_status' => $project->analysis_status,
            'images' => $project->images->map(fn ($i) => $this->imagePayload($i))->values(),
            'latest_analysis_version' => $project->latestAnalysis?->version,
            'updated_at' => $project->updated_at?->toIso8601String(),
        ];
    }

    public function analysisPayload(PipePlanningAnalysis $analysis): array
    {
        $showPrices = $this->settings->get('show_prices');
        $analysis->loadMissing(['items.product', 'items.variant', 'quoteRequest']);

        return [
            'version' => $analysis->version,
            'status' => $analysis->status,
            'stage' => $analysis->stage,
            'failure_category' => $analysis->failure_category,
            'readiness' => $analysis->readiness,
            'observations' => $analysis->observations ?? [],
            'routing_options' => $analysis->routing['options'] ?? [],
            'contradictions' => $analysis->routing['contradictions'] ?? [],
            'assumptions' => $analysis->assumptions ?? [],
            'missing_information' => $analysis->missing_information ?? [],
            'clarification_questions' => $analysis->clarification_questions ?? [],
            'safety_flags' => $analysis->safety_flags ?? [],
            'validation_status' => $analysis->validation_status,
            'corrections' => $analysis->validation_status === 'corrected' ? ($analysis->validation_errors ?? []) : [],
            'waste_allowance_percent' => $analysis->waste_allowance_percent,
            'quote_submitted' => (bool) $analysis->quoteRequest,
            'completed_at' => $analysis->completed_at?->toIso8601String(),
            'items' => $analysis->items->map(function (PipePlanningItem $item) use ($showPrices) {
                $product = $item->product;
                $price = $item->variant ? ($item->variant->sale_price ?: $item->variant->price ?: null) : ($product ? ($product->sale_price ?: $product->price) : null);
                $price = ($showPrices && $product?->is_active && $price !== null && (float) $price > 0) ? (float) $price : null;

                return [
                    'id' => $item->id,
                    'category' => $item->category,
                    'water' => $item->water,
                    'diameter_mm' => $item->diameter_mm,
                    'secondary_diameter_mm' => $item->secondary_diameter_mm,
                    'base_quantity' => $item->base_quantity,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'quantity_status' => $item->quantity_status,
                    'match_status' => $product ? $item->match_status : 'requires_verification',
                    'source' => $item->source,
                    'derivation' => $item->derivation,
                    'notes' => $item->notes,
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => trans_field($product, 'name'),
                        'sku' => $item->variant?->sku ?? $product->sku,
                        'url' => route('products.show', $product->slug),
                        'stock_status' => $product->stock_status,
                        'price' => $price,
                        'currency' => $product->currency,
                    ] : null,
                ];
            })->values(),
        ];
    }
}
