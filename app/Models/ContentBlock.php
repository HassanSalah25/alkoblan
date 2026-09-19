<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $fillable = [
        'key', 'title', 'title_ar', 'subtitle', 'subtitle_ar', 'content', 'content_ar',
        'image_id', 'button_text', 'button_text_ar', 'button_url', 'extra', 'is_active',
    ];

    protected $casts = [
        'extra' => 'array',
        'is_active' => 'boolean',
    ];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public static function find_key(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
