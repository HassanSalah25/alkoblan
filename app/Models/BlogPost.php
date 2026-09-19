<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'blog_category_id', 'user_id', 'title', 'title_ar', 'slug', 'source_url', 'excerpt', 'excerpt_ar',
        'content', 'content_ar', 'featured_image_id', 'status', 'published_at',
        'seo_title', 'seo_description', 'seo_keywords', 'views_count',
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
