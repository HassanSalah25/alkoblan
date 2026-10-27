<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipePlanningAnalysis extends Model
{
    public const ACTIVE_STATUSES = ['queued', 'running'];

    protected $fillable = [
        'project_id', 'version', 'input_hash', 'provider', 'model', 'status', 'stage',
        'observations', 'routing', 'assumptions', 'missing_information', 'clarification_questions',
        'safety_flags', 'readiness', 'waste_allowance_percent', 'validation_status', 'validation_errors',
        'failure_category', 'provider_request_id', 'prompt_tokens', 'completion_tokens', 'cost_usd',
        'duration_ms', 'attempts', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'observations' => 'array',
        'routing' => 'array',
        'assumptions' => 'array',
        'missing_information' => 'array',
        'clarification_questions' => 'array',
        'safety_flags' => 'array',
        'validation_errors' => 'array',
        'waste_allowance_percent' => 'float',
        'cost_usd' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(PipePlanningProject::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(PipePlanningItem::class, 'analysis_id');
    }

    public function quoteRequest()
    {
        return $this->hasOne(PipePlanningQuoteRequest::class, 'analysis_id');
    }
}
