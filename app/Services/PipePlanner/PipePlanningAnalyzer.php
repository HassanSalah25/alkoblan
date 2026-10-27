<?php

namespace App\Services\PipePlanner;

use App\Models\PipePlanningAnalysis;
use App\Models\PipePlanningProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PipePlanningAnalyzer
{
    public function __construct(
        protected OpenRouterClient $client,
        protected PromptBuilder $prompts,
        protected CatalogMatcher $matcher,
        protected QuantityCalculator $calculator,
        protected PlannerSettings $settings,
    ) {}

    /** Hash of every input that affects the AI result; identical inputs reuse the stored result. */
    public function inputHash(PipePlanningProject $project): string
    {
        return hash('sha256', json_encode([
            $this->prompts->projectContext($project),
            $project->locale,
            $project->images->map(fn ($i) => [$i->sha256, $i->caption, $i->has_scale_reference])->all(),
            $this->settings->get('model'),
            $this->matcher->promptSnapshot(),
        ]));
    }

    public function run(PipePlanningAnalysis $analysis): void
    {
        $project = $analysis->project()->with('images')->firstOrFail();
        $started = microtime(true);
        $analysis->update(['status' => 'running', 'stage' => 'analyzing_images', 'started_at' => now()]);
        $project->update(['analysis_status' => 'running']);

        try {
            $model = $this->client->resolveModel();
            $analysis->update(['model' => $model['id']]);

            $images = $project->images->map(fn ($img) => [
                'data_url' => 'data:'.$img->mime_type.';base64,'.base64_encode(Storage::disk(config('pipe_planner.disk'))->get($img->storage_key)),
                'caption' => $img->caption,
                'has_scale_reference' => $img->has_scale_reference,
            ])->all();

            $messages = $this->prompts->messages($project, $this->matcher->promptSnapshot(), $images);
            if (! $model['structured_outputs']) {
                $messages[0]['content'] .= "\n\nJSON schema:\n".json_encode(AnalysisSchema::jsonSchema());
            }

            $result = $this->client->complete($model, $messages, AnalysisSchema::jsonSchema());
            unset($images, $messages);

            $analysis->update(['stage' => 'checking_products']);
            $validated = AnalysisSchema::validate($result['content']);
            $usage = [
                'provider_request_id' => $result['request_id'],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'cost_usd' => $result['cost'],
                'attempts' => $result['attempts'],
            ];

            if ($validated['data'] === null) {
                $this->fail($analysis, 'invalid_output', $validated['errors'], $started, $usage);

                return;
            }

            $this->store($analysis, $project, $validated['data'], $usage, $started);
        } catch (OpenRouterException $e) {
            $this->fail($analysis, $e->category, [], $started);
        } catch (\Throwable $e) {
            Log::error('pipe_planner.analysis.exception', ['analysis_id' => $analysis->id, 'exception' => get_class($e)]);
            $this->fail($analysis, 'internal_error', [], $started);
        }
    }

    /**
     * Apply integrity checks to the schema-valid data, then match and calculate.
     */
    public function store(PipePlanningAnalysis $analysis, PipePlanningProject $project, array $data, array $usage, float $started): void
    {
        $analysis->update(['stage' => 'preparing_report']);
        [$data, $integrity] = $this->enforceIntegrity($data, count($project->images));

        DB::transaction(function () use ($analysis, $project, $data, $integrity, $usage, $started) {
            $analysis->items()->delete();
            $analysis->update($usage + [
                'status' => 'completed',
                'stage' => null,
                'observations' => $data['observations'],
                'routing' => ['options' => $data['routing_options'], 'contradictions' => $data['contradictions']],
                'assumptions' => $data['assumptions'],
                'missing_information' => $data['missing_information'],
                'clarification_questions' => $data['clarification_questions'],
                'safety_flags' => $data['safety_flags'],
                'readiness' => $data['readiness'],
                'validation_status' => $integrity ? 'corrected' : 'valid',
                'validation_errors' => $integrity ?: null,
                'waste_allowance_percent' => $project->waste_allowance_percent,
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
                'completed_at' => now(),
            ]);

            $this->buildItems($analysis, $project, $data);
            $project->update(['analysis_status' => 'completed']);
        });

        Log::info('pipe_planner.analysis.completed', [
            'analysis_id' => $analysis->id, 'model' => $analysis->model, 'request_id' => $analysis->provider_request_id,
            'duration_ms' => $analysis->duration_ms, 'cost_usd' => $analysis->cost_usd,
        ]);
    }

    /**
     * Remove anything the model produced that the catalog or the input cannot support.
     *
     * @return array{0: array, 1: string[]} corrected data and a list of corrections made
     */
    public function enforceIntegrity(array $data, int $imageCount): array
    {
        $issues = [];
        $catalogIds = $this->matcher->catalog()->pluck('id')->all();
        $diameters = $this->matcher->availableDiameters();

        $data['product_suggestions'] = array_values(array_filter($data['product_suggestions'], function ($s) use ($catalogIds, &$issues) {
            if (! in_array($s['product_id'], $catalogIds, true)) {
                $issues[] = "Discarded non-catalog product id {$s['product_id']}";

                return false;
            }

            return true;
        }));

        foreach ($data['observations'] as &$obs) {
            if ($obs['image_index'] !== null && $obs['image_index'] >= $imageCount) {
                $obs['image_index'] = null;
                $obs['basis'] = 'inferred';
            }
        }
        unset($obs);

        $unsupportedDiameter = function (?int $d) use ($diameters, &$issues): ?int {
            if ($d !== null && ! in_array($d, $diameters, true)) {
                $issues[] = "Discarded diameter {$d} mm not offered in the catalog";

                return null;
            }

            return $d;
        };

        foreach ($data['routing_options'] as &$option) {
            $nodeIds = array_column($option['nodes'], 'id');
            $option['segments'] = array_values(array_filter($option['segments'], function ($seg) use ($nodeIds, &$issues) {
                if (! in_array($seg['from'], $nodeIds, true) || ! in_array($seg['to'], $nodeIds, true)) {
                    $issues[] = "Discarded segment {$seg['id']} with unknown endpoints";

                    return false;
                }

                return true;
            }));
            foreach ($option['segments'] as &$seg) {
                $seg['nominal_diameter_mm'] = $unsupportedDiameter($seg['nominal_diameter_mm']);
            }
            unset($seg);
        }
        unset($option);

        foreach ($data['fittings'] as &$f) {
            $nominal = $unsupportedDiameter($f['nominal_diameter_mm']);
            $secondary = $unsupportedDiameter($f['secondary_diameter_mm']);
            // Dropping one side of a reducing fitting would turn it into an equal fitting; drop both.
            $partial = ($f['nominal_diameter_mm'] !== null && $nominal === null) || ($f['secondary_diameter_mm'] !== null && $secondary === null);
            $f['nominal_diameter_mm'] = $partial ? null : $nominal;
            $f['secondary_diameter_mm'] = $partial ? null : $secondary;
        }
        unset($f);

        if ($issues && $data['readiness'] === 'ready_for_preliminary_quote') {
            $data['readiness'] = 'needs_more_information';
        }

        return [$data, array_values(array_unique($issues))];
    }

    public function buildItems(PipePlanningAnalysis $analysis, PipePlanningProject $project, array $data): void
    {
        $requirements = $this->calculator->requirements(
            $data,
            $project->measurements['distances'] ?? [],
            $project->unit,
            $analysis->waste_allowance_percent,
        );
        $suggested = array_column($data['product_suggestions'], 'product_id');

        // Fittings must follow the material of the matched pipe line.
        $pipeMaterial = null;
        usort($requirements, fn ($a, $b) => ($b['category'] === 'pipe') <=> ($a['category'] === 'pipe'));

        foreach ($requirements as $req) {
            $match = $this->matcher->match($req + ['material' => $pipeMaterial], $suggested);
            if ($req['category'] === 'pipe' && $match['product'] && ! $pipeMaterial) {
                $pipeMaterial = $match['product']['material'];
            }

            $derivation = $req['derivation'] + [
                'match_reasons' => $match['reasons'],
                'candidate_count' => $match['candidates'],
            ];
            if ($req['category'] === 'pipe' && $match['product']) {
                $derivation['stock_length_m'] = $match['product']['stock_length_m'];
                $derivation['stock_lengths'] = $this->calculator->stockLengths($req['quantity'], $match['product']['stock_length_m']);
            }

            $analysis->items()->create([
                'product_id' => $match['product']['id'] ?? null,
                'product_variant_id' => $match['variant']['id'] ?? null,
                'category' => $req['category'],
                'water' => $req['water'],
                'diameter_mm' => $req['diameter_mm'],
                'secondary_diameter_mm' => $req['secondary_diameter_mm'],
                'base_quantity' => $req['base_quantity'],
                'quantity' => $req['quantity'],
                'unit' => $req['unit'],
                'quantity_status' => $req['quantity_status'],
                'match_status' => $match['status'],
                'source' => $req['source'],
                'derivation' => $derivation,
                'notes' => $req['notes'],
            ]);
        }
    }

    /** Re-apply a waste allowance to an existing analysis without another AI call. */
    public function applyWaste(PipePlanningAnalysis $analysis, ?float $percent): void
    {
        DB::transaction(function () use ($analysis, $percent) {
            $analysis->update(['waste_allowance_percent' => $percent]);
            foreach ($analysis->items()->where('category', 'pipe')->get() as $item) {
                $qty = $this->calculator->applyWaste($item->base_quantity, $percent);
                $derivation = $item->derivation;
                $derivation['waste_allowance_percent'] = $percent;
                if (! empty($derivation['stock_length_m'])) {
                    $derivation['stock_lengths'] = $this->calculator->stockLengths($qty, $derivation['stock_length_m']);
                }
                $item->update(['quantity' => $qty, 'derivation' => $derivation]);
            }
        });
    }

    protected function fail(PipePlanningAnalysis $analysis, string $category, array $errors, float $started, array $usage = []): void
    {
        $analysis->update($usage + [
            'status' => 'failed',
            'stage' => null,
            'failure_category' => $category,
            'validation_status' => $category === 'invalid_output' ? 'invalid' : null,
            'validation_errors' => $errors ?: null,
            'duration_ms' => (int) ((microtime(true) - $started) * 1000),
            'completed_at' => now(),
        ]);
        $analysis->project()->update(['analysis_status' => 'failed']);

        Log::warning('pipe_planner.analysis.failed', [
            'analysis_id' => $analysis->id, 'model' => $analysis->model, 'failure_category' => $category,
        ]);
    }
}
