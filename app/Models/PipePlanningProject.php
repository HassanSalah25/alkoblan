<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PipePlanningProject extends Model
{
    public const PROJECT_TYPES = ['apartment', 'villa', 'bathroom', 'kitchen', 'commercial', 'other'];

    public const PROJECT_STATUSES = ['new_construction', 'renovation', 'existing_installation'];

    public const SYSTEM_TYPES = ['cold', 'hot', 'combined', 'other'];

    public const SCOPES = ['complete', 'partial'];

    public const UNITS = ['mm', 'cm', 'm', 'in', 'ft'];

    protected $fillable = [
        'user_id', 'guest_token_hash', 'project_type', 'project_status', 'system_type', 'scope',
        'fixtures', 'location', 'notes', 'unit', 'measurements', 'answers',
        'waste_allowance_percent', 'locale', 'analysis_status',
    ];

    protected $hidden = ['guest_token_hash'];

    protected $casts = [
        'fixtures' => 'array',
        'measurements' => 'array',
        'answers' => 'array',
        'waste_allowance_percent' => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $project) {
            do {
                $ref = 'PP-'.strtoupper(Str::random(8));
            } while (static::where('reference', $ref)->exists());
            $project->reference = $ref;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(PipePlanningImage::class, 'project_id')->orderBy('sort_order')->orderBy('id');
    }

    public function analyses()
    {
        return $this->hasMany(PipePlanningAnalysis::class, 'project_id')->orderByDesc('version');
    }

    public function latestAnalysis()
    {
        return $this->hasOne(PipePlanningAnalysis::class, 'project_id')->latestOfMany('version');
    }

    public function quoteRequests()
    {
        return $this->hasMany(PipePlanningQuoteRequest::class, 'project_id');
    }
}
