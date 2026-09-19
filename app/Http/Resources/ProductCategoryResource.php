<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'image' => $this->whenLoaded('image', fn () => $this->image ? new MediaResource($this->image) : null),
            'icon' => $this->icon,
            'is_featured' => (bool) $this->is_featured,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'products_count' => $this->when(isset($this->products_count), fn () => (int) $this->products_count),
            'children' => ProductCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
