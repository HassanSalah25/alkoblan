<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'position', 'position_ar', 'company', 'company_ar',
        'content', 'content_ar', 'image_id', 'rating', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
