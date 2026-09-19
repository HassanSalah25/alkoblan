<?php

namespace App\Services;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Filterable/paginated product listing.
     *
     * @param  array{category?:string,q?:string,attributes?:array,min_price?:float,max_price?:float,featured?:bool,sort?:string,per_page?:int,page?:int}  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::query()->active()->with(['category', 'images.media']);

        if (! empty($filters['category'])) {
            $category = ProductCategory::where('slug', $filters['category'])->first();
            if ($category) {
                $query->whereIn('category_id', $category->descendantIds());
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('name_ar', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['attributes'])) {
            $attributeValueIds = array_values(array_filter(array_map('intval', (array) $filters['attributes'])));
            if (! empty($attributeValueIds)) {
                $grouped = AttributeValue::whereIn('id', $attributeValueIds)->get()->groupBy('attribute_id');

                foreach ($grouped as $valuesForAttribute) {
                    $valueIds = $valuesForAttribute->pluck('id')->all();
                    // AND across different attributes, OR within the same attribute;
                    // a variant's attribute values also count the parent product as matching.
                    $query->where(function ($sub) use ($valueIds) {
                        $sub->whereHas('attributeValues', function ($q) use ($valueIds) {
                            $q->whereIn('attribute_values.id', $valueIds);
                        })->orWhereHas('variants.attributeValues', function ($q) use ($valueIds) {
                            $q->whereIn('attribute_values.id', $valueIds);
                        });
                    });
                }
            }
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [(float) $filters['min_price']]);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [(float) $filters['max_price']]);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'name_asc' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min(48, max(1, (int) ($filters['per_page'] ?? 12)));
        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findBySlug(string $slug): ?Product
    {
        $product = Product::active()->where('slug', $slug)
            ->with([
                'category',
                'images' => fn ($q) => $q->orderBy('sort_order'),
                'images.media',
                'files.media',
                'attributeValues.attribute',
                'variants' => fn ($q) => $q->where('is_active', true),
                'variants.attributeValues.attribute',
            ])
            ->first();

        if (! $product) {
            return null;
        }

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'images.media'])
            ->limit(4)
            ->get();

        $product->setRelation('relatedProducts', $related);

        return $product;
    }
}
