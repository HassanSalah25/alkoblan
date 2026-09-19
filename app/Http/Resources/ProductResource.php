<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // group attribute values by their parent attribute, for spec/filter display
        $attributeGroups = [];
        if ($this->relationLoaded('attributeValues')) {
            foreach ($this->attributeValues->groupBy('attribute_id') as $values) {
                $attribute = $values->first()->attribute;
                if (! $attribute) {
                    continue;
                }
                $attributeGroups[] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'name_ar' => $attribute->name_ar,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'values' => $values->map(fn ($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'value_ar' => $v->value_ar,
                    ])->values(),
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'short_description_ar' => $this->short_description_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'specifications' => $this->specifications,
            'technical_specifications' => $this->technical_specifications,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'is_on_sale' => (bool) $this->is_on_sale,
            'currency' => $this->currency,
            'stock_quantity' => $this->stock_quantity,
            'stock_status' => $this->stock_status,
            'min_order_qty' => $this->min_order_qty,
            'is_featured' => (bool) $this->is_featured,
            'seo_title' => $this->seo_title,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_description' => $this->seo_description,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_keywords' => $this->seo_keywords,
            'category' => $this->whenLoaded('category', fn () => $this->category ? new ProductCategoryResource($this->category) : null),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'files' => ProductFileResource::collection($this->whenLoaded('files')),
            'attributes' => $attributeGroups,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'related_products' => ProductListResource::collection($this->whenLoaded('relatedProducts')),
        ];
    }
}
