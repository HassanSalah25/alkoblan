<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = ['name', 'name_ar', 'slug', 'sort_order'];

    public function posts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
