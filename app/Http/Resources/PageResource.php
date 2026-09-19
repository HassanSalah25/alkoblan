<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'excerpt_ar' => $this->excerpt_ar,
            'content' => $this->content,
            'content_ar' => $this->content_ar,
            'featured_image' => $this->whenLoaded('featuredImage', fn () => $this->featuredImage ? new MediaResource($this->featuredImage) : null),
            'seo_title' => $this->seo_title,
            'seo_title_ar' => $this->seo_title_ar,
            'seo_description' => $this->seo_description,
            'seo_description_ar' => $this->seo_description_ar,
            'seo_keywords' => $this->seo_keywords,
            'canonical_url' => $this->canonical_url,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
