<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'media_category_id', 'disk', 'path', 'source_url', 'original_name', 'mime_type',
        'type', 'size', 'title', 'title_ar', 'alt_text', 'description',
        'visibility', 'download_count', 'uploaded_by',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function category()
    {
        return $this->belongsTo(MediaCategory::class, 'media_category_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function typeFromMime(?string $mime): string
    {
        if (! $mime) {
            return 'other';
        }
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if ($mime === 'application/pdf') {
            return 'pdf';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return 'document';
        }

        return 'other';
    }
}
