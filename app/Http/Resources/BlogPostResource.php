<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'slug_ar' => $this->slug_ar,
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
            'seo_keywords_ar' => $this->seo_keywords_ar,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => $this->whenLoaded('author', fn () => $this->author ? ['name' => $this->author->name] : null),
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
            'related_posts' => BlogPostListResource::collection($this->whenLoaded('relatedPosts')),
        ];
    }
}
