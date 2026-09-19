<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'tag', 'tag_ar', 'title', 'title_ar', 'subtitle', 'subtitle_ar',
        'button_text', 'button_text_ar', 'button_url',
        'button2_text', 'button2_text_ar', 'button2_url',
        'image_desktop_id', 'image_mobile_id', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function imageDesktop()
    {
        return $this->belongsTo(Media::class, 'image_desktop_id');
    }

    public function imageMobile()
    {
        return $this->belongsTo(Media::class, 'image_mobile_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
