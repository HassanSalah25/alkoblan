<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    protected $fillable = [
        'title', 'title_ar', 'slug', 'department', 'location', 'employment_type',
        'description', 'description_ar', 'requirements', 'requirements_ar',
        'benefits', 'benefits_ar', 'deadline', 'status',
    ];

    protected $casts = ['deadline' => 'date'];

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
