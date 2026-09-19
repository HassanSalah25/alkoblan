<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // images should be eager loaded (ordered by sort_order); prefer the "main" one,
        // otherwise fall back to the first gallery image.
        $image = null;
        if ($this->relationLoaded('images')) {
            $image = $this->images->firstWhere('type', 'main') ?? $this->images->firstWhere('type', 'gallery');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'short_description_ar' => $this->short_description_ar,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'is_on_sale' => (bool) $this->is_on_sale,
            'currency' => $this->currency,
            'stock_status' => $this->stock_status,
            'min_order_qty' => $this->min_order_qty,
            'is_featured' => (bool) $this->is_featured,
            'image' => $image ? [
                'url' => $image->media?->url,
                'alt_text' => $image->media?->alt_text,
            ] : null,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'name_ar' => $this->category->name_ar,
                'slug' => $this->category->slug,
            ] : null),
        ];
    }
}
