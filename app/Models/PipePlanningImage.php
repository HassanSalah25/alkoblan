<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipePlanningImage extends Model
{
    protected $fillable = [
        'project_id', 'storage_key', 'original_name', 'mime_type', 'size_bytes', 'width', 'height',
        'sha256', 'sort_order', 'caption', 'has_scale_reference', 'processing_status',
    ];

    protected $hidden = ['storage_key'];

    protected $casts = ['has_scale_reference' => 'boolean'];

    public function project()
    {
        return $this->belongsTo(PipePlanningProject::class, 'project_id');
    }
}
