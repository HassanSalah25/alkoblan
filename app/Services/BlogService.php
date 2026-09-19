<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = BlogPost::published()->with(['category', 'featuredImage', 'tags']);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $filters['tag']));
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('title_ar', 'like', "%{$q}%")
                    ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('published_at');

        $perPage = min(48, max(1, (int) ($filters['per_page'] ?? 9)));
        $page = isset($filters['page']) ? (int) $filters['page'] : null;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        $post = BlogPost::published()->where('slug', $slug)
            ->with(['category', 'featuredImage', 'tags', 'author'])
            ->first();

        if (! $post) {
            return null;
        }

        $post->increment('views_count');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->with(['category', 'featuredImage'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $post->setRelation('relatedPosts', $related);

        return $post;
    }
}
