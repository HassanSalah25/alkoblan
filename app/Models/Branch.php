<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'city', 'city_ar', 'address', 'address_ar', 'phone',
        'phone_secondary', 'email', 'latitude', 'longitude', 'maps_url',
        'working_hours', 'working_hours_ar', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
