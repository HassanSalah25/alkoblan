<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    protected $fillable = ['event_id', 'media_id', 'sort_order'];
    public $timestamps = false;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
