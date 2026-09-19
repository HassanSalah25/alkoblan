<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostListResource extends JsonResource
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
            'featured_image' => $this->whenLoaded('featuredImage', fn () => $this->featuredImage ? new MediaResource($this->featuredImage) : null),
            'views_count' => $this->views_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'name_ar' => $this->category->name_ar,
                'slug' => $this->category->slug,
            ] : null),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'name_ar' => $t->name_ar,
                'slug' => $t->slug,
            ])),
        ];
    }
}
