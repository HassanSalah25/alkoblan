<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamousClient extends Model
{
    protected $fillable = ['name', 'name_ar', 'logo_id', 'url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function logo()
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
