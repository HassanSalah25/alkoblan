<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipePlanningQuoteRequest extends Model
{
    protected $fillable = [
        'project_id', 'analysis_id', 'user_id', 'contact_message_id', 'name', 'email', 'phone',
        'items', 'approved_assumptions', 'message', 'status',
    ];

    protected $casts = ['items' => 'array', 'approved_assumptions' => 'array'];

    public function project()
    {
        return $this->belongsTo(PipePlanningProject::class, 'project_id');
    }

    public function analysis()
    {
        return $this->belongsTo(PipePlanningAnalysis::class, 'analysis_id');
    }

    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class);
    }
}
