<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PipePlanningAnalysis;
use App\Models\PipePlanningImage;
use App\Models\PipePlanningItem;
use App\Models\PipePlanningProject;
use App\Models\PipePlanningQuoteRequest;
use App\Services\PipePlanner\PlannerSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PipePlannerController extends Controller
{
    public function __construct(protected PlannerSettings $settings) {}

    public function index(Request $request)
    {
        $projects = PipePlanningProject::query()
            ->with(['user', 'latestAnalysis'])
            ->withCount(['images', 'quoteRequests'])
            ->when($request->filled('status'), fn ($q) => $q->where('analysis_status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('reference', 'like', '%'.$request->q.'%'))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $monthStart = now()->startOfMonth();
        $month = fn () => PipePlanningAnalysis::where('created_at', '>=', $monthStart);
        $stats = [
            'projects' => PipePlanningProject::count(),
            'analyses_month' => $month()->count(),
            'failed_month' => $month()->where('status', 'failed')->count(),
            'cost_month' => (float) $month()->sum('cost_usd'),
            'tokens_month' => (int) $month()->sum(DB::raw('COALESCE(prompt_tokens, 0) + COALESCE(completion_tokens, 0)')),
            'quotes' => PipePlanningQuoteRequest::count(),
            'matched_items' => PipePlanningItem::where('match_status', 'matched')->count(),
            'unverified_items' => PipePlanningItem::where('match_status', '!=', 'matched')->count(),
        ];

        $failures = PipePlanningAnalysis::with('project')->where('status', 'failed')->latest()->limit(10)->get();
        $failureBreakdown = $month()->where('status', 'failed')
            ->selectRaw('failure_category, count(*) as total')->groupBy('failure_category')->pluck('total', 'failure_category');
        $quotes = PipePlanningQuoteRequest::with('project')->latest()->limit(10)->get();

        return view('admin.pipe-planner.index', compact('projects', 'stats', 'failures', 'failureBreakdown', 'quotes'));
    }

    public function show(PipePlanningProject $project)
    {
        $project->load(['user', 'images', 'analyses.items.product', 'quoteRequests.contactMessage']);

        return view('admin.pipe-planner.show', compact('project'));
    }

    public function image(PipePlanningImage $image)
    {
        return response(Storage::disk(config('pipe_planner.disk'))->get($image->storage_key), 200, [
            'Content-Type' => $image->mime_type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(PipePlanningProject $project)
    {
        Storage::disk(config('pipe_planner.disk'))->deleteDirectory('pipe-planner/'.$project->id);
        $project->delete();

        return redirect()->route('admin.pipe-planner.index')->with('success', 'Project and its photos were deleted.');
    }

    public function settings()
    {
        return view('admin.pipe-planner.settings', [
            'values' => $this->settings->all(),
            'apiKeyConfigured' => $this->settings->apiKeyConfigured(),
            'envModel' => config('pipe_planner.openrouter.model'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'model' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            'max_images' => 'required|integer|min:1|max:20',
            'max_image_kb' => 'required|integer|min:256|max:20480',
            'analyses_per_day' => 'required|integer|min:1|max:500',
            'monthly_budget_usd' => 'required|numeric|min:0|max:100000',
            'max_tokens' => 'required|integer|min:1000|max:32000',
            'retention_days' => 'required|integer|min:7|max:3650',
        ]);
        $data['show_prices'] = $request->boolean('show_prices') ? '1' : '0';

        foreach ($data as $key => $value) {
            $this->settings->set($key, $value);
        }
        // Model capabilities are re-checked against OpenRouter with the new configuration.
        Cache::forget('pipe_planner.openrouter.models');

        return back()->with('success', 'AI Pipe Planner settings saved.');
    }
}
