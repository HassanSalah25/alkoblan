<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id', 'user_id', 'title', 'title_ar', 'slug', 'slug_ar', 'source_url', 'excerpt', 'excerpt_ar',
        'content', 'content_ar', 'featured_image_id', 'featured_image_alt', 'featured_image_alt_ar', 'status', 'published_at',
        'seo_title', 'seo_title_ar', 'seo_description', 'seo_description_ar', 'seo_keywords', 'seo_keywords_ar',
        'canonical_url', 'canonical_url_ar', 'views_count',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function tags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }
}
