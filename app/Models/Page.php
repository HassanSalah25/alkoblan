<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title', 'title_ar', 'slug', 'excerpt', 'excerpt_ar', 'content', 'content_ar',
        'featured_image_id', 'status', 'sort_order', 'seo_title', 'seo_title_ar',
        'seo_description', 'seo_description_ar', 'seo_keywords', 'canonical_url',
        'published_at', 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function featuredImage()
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
