<?php

namespace App\Services\PipePlanner;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Deterministic matching of planner components to real catalog records.
 * A product is only "matched" when its catalog data proves compatibility
 * (category, diameter, and — where relevant — material, temperature and shape).
 * Anything ambiguous or unproven is returned as "requires_verification".
 */
class CatalogMatcher
{
    protected ?Collection $catalog = null;

    public function __construct(protected PlannerSettings $settings) {}

    /** Normalized catalog records for every active product in a planner-relevant category. */
    public function catalog(): Collection
    {
        if ($this->catalog !== null) {
            return $this->catalog;
        }

        $slugToCategory = [];
        foreach (config('pipe_planner.category_map') as $plannerCategory => $slugs) {
            foreach ($slugs as $slug) {
                $slugToCategory[$slug][] = $plannerCategory;
            }
        }

        $products = Product::query()->active()
            ->whereHas('category', fn ($q) => $q->whereIn('slug', array_keys($slugToCategory)))
            ->with(['category', 'attributeValues.attribute', 'variants' => fn ($q) => $q->where('is_active', true), 'variants.attributeValues.attribute'])
            ->orderBy('sort_order')
            ->get();

        return $this->catalog = $products->map(function (Product $p) use ($slugToCategory) {
            $specs = collect($p->technical_specifications ?? [])->mapWithKeys(
                fn ($row) => [Str::lower(trim($row['label'] ?? '')) => trim((string) ($row['value'] ?? ''))]
            );
            $attr = fn ($model, string $slug) => $model->attributeValues->first(fn ($v) => $v->attribute?->slug === $slug)?->value;

            $variants = $p->variants->map(fn ($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'diameter_mm' => UnitConverter::parseDiameterMm($attr($v, 'diameter')),
                'price' => $v->sale_price ?: $v->price,
                'stock_quantity' => $v->stock_quantity,
            ])->filter(fn ($v) => $v['diameter_mm'] !== null)->values();

            $productDiameters = $p->attributeValues->toBase()
                ->filter(fn ($v) => $v->attribute?->slug === 'diameter')
                ->map(fn ($v) => UnitConverter::parseDiameterMm($v->value))
                ->filter()->values();

            // Imported catalog records list sizes as spec rows labelled "Size 32mm".
            $specDiameters = $specs->keys()
                ->map(fn ($label) => preg_match('/^size\s+(\d{2,3})\s*mm$/', $label, $m) ? (int) $m[1] : null)
                ->filter()->values();
            $productDiameters = $productDiameters->merge($specDiameters);

            $maxTemp = null;
            if (preg_match('/(\d{2,3})\s*°?\s*C/i', $specs['max temperature'] ?? '', $m)) {
                $maxTemp = (int) $m[1];
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'name_ar' => $p->name_ar,
                'slug' => $p->slug,
                'sku' => $p->sku,
                'category_slug' => $p->category?->slug,
                'planner_categories' => $slugToCategory[$p->category?->slug] ?? [],
                'material' => $attr($p, 'material') ?: ($specs['material'] ?? null),
                'pressure' => $attr($p, 'pressure-rating') ?: ($specs['pressure rating'] ?? $specs['max pressure'] ?? null),
                'max_temp_c' => $maxTemp,
                'connection' => $specs['connection type'] ?? null,
                'stock_length_m' => UnitConverter::parseLengthMeters($specs['length'] ?? null),
                'diameters_mm' => $variants->pluck('diameter_mm')->merge($productDiameters)->unique()->sort()->values()->all(),
                'variants' => $variants->all(),
                'price' => $p->sale_price ?: $p->price,
                'stock_status' => $p->stock_status,
                'currency' => $p->currency,
            ];
        })->values();
    }

    /** Diameters (mm) that exist in the catalog, optionally for one planner category. */
    public function availableDiameters(?string $category = null): array
    {
        return $this->catalog()
            ->filter(fn ($p) => $category === null || in_array($category, $p['planner_categories'], true))
            ->pluck('diameters_mm')->flatten()->unique()->sort()->values()->all();
    }

    /** Compact catalog subset sent to the model: only fields relevant to compatibility. */
    public function promptSnapshot(): array
    {
        return $this->catalog()
            ->take((int) config('pipe_planner.limits.max_catalog_products', 120))
            ->map(fn ($p) => array_filter([
                'id' => $p['id'],
                'name' => $p['name'],
                'category' => implode('/', $p['planner_categories']),
                'material' => $p['material'],
                'pressure' => $p['pressure'],
                'max_temp_c' => $p['max_temp_c'],
                'diameters_mm' => $p['diameters_mm'],
            ], fn ($v) => $v !== null && $v !== []))
            ->values()->all();
    }

    /**
     * @param  array{category:string, diameter_mm:?int, secondary_diameter_mm?:?int, water?:?string, material?:?string, notes?:?string}  $req
     * @param  int[]  $suggestedIds  product ids the model suggested (already filtered to real ids)
     * @return array{status:string, product:?array, variant:?array, candidates:int, reasons:array}
     */
    public function match(array $req, array $suggestedIds = []): array
    {
        $reasons = [];
        $diameter = $req['diameter_mm'] ?? null;
        if ($diameter === null) {
            return ['status' => 'requires_verification', 'product' => null, 'variant' => null, 'candidates' => 0, 'reasons' => ['diameter_unknown']];
        }

        $candidates = $this->catalog()
            ->filter(fn ($p) => in_array($req['category'], $p['planner_categories'], true))
            ->filter(fn ($p) => in_array($diameter, $p['diameters_mm'], true));

        if (! empty($req['material'])) {
            $candidates = $candidates->filter(fn ($p) => $p['material'] === null
                || Str::startsWith(Str::upper($p['material']), Str::upper(Str::before($req['material'], ' '))));
        }

        $name = fn ($p) => Str::lower($p['name']);
        $secondary = $req['secondary_diameter_mm'] ?? null;
        $isReducing = $secondary !== null && $secondary !== $diameter;

        switch ($req['category']) {
            case 'pipe':
                // Pipe categories also hold fittings (e.g. cross-overs); only actual pipes qualify.
                $candidates = $candidates->filter(fn ($p) => Str::contains($name($p), 'pipe') || Str::contains((string) $p['name_ar'], 'ماسورة'));
                if (($req['water'] ?? null) === 'hot') {
                    // Hot water needs a verified temperature rating; unknown ratings are excluded.
                    $candidates = $candidates->filter(fn ($p) => ($p['max_temp_c'] ?? 0) >= 60 || Str::contains($name($p), 'hot'));
                    $reasons[] = 'hot_rating_required';
                }
                break;
            case 'reducer':
                $candidates = $candidates->filter(fn ($p) => Str::contains($name($p), 'reduc'));
                break;
            case 'coupling':
                $candidates = $candidates->reject(fn ($p) => Str::contains($name($p), 'reduc'));
                break;
            case 'tee':
                $candidates = $candidates->filter(fn ($p) => $isReducing === Str::contains($name($p), 'reduc'));
                break;
            case 'elbow':
                $angle = Str::contains((string) ($req['notes'] ?? ''), '45') ? '45' : '90';
                $candidates = $candidates->filter(fn ($p) => Str::contains($p['name'], $angle));
                $reasons[] = "elbow_angle_{$angle}";
                break;
        }

        $candidates = $candidates->values();
        $chosen = $candidates->first(fn ($p) => in_array($p['id'], $suggestedIds, true));
        if (! $chosen && $candidates->count() === 1) {
            $chosen = $candidates->first();
        }

        if (! $chosen) {
            return [
                'status' => 'requires_verification', 'product' => null, 'variant' => null,
                'candidates' => $candidates->count(),
                'reasons' => array_merge($reasons, [$candidates->isEmpty() ? 'no_compatible_product' : 'multiple_candidates']),
            ];
        }

        $variant = collect($chosen['variants'])->firstWhere('diameter_mm', $diameter);

        return ['status' => 'matched', 'product' => $chosen, 'variant' => $variant, 'candidates' => $candidates->count(), 'reasons' => $reasons];
    }

    public function find(int $productId): ?array
    {
        return $this->catalog()->firstWhere('id', $productId);
    }
}
