<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = ['job_opening_id', 'name', 'email', 'phone', 'cv_media_id', 'cover_letter', 'status'];

    public function job()
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    public function cv()
    {
        return $this->belongsTo(Media::class, 'cv_media_id');
    }
}
