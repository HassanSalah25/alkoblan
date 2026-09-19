<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Media;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CareerService
{
    public function paginate(int $perPage = 12, ?int $page = null): LengthAwarePaginator
    {
        return JobOpening::open()->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);
    }

    public function findBySlug(string $slug): ?JobOpening
    {
        return JobOpening::open()->where('slug', $slug)->first();
    }

    /**
     * Store the uploaded CV as a Media record, then create the JobApplication
     * linking to it and to the resolved job opening.
     */
    public function apply(JobOpening $job, array $data, UploadedFile $cv): JobApplication
    {
        $path = $cv->store('media/cv', 'public');

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $cv->getClientOriginalName(),
            'mime_type' => $cv->getClientMimeType(),
            'type' => Media::typeFromMime($cv->getClientMimeType()),
            'size' => $cv->getSize(),
            'visibility' => 'private',
        ]);

        return JobApplication::create([
            'job_opening_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cv_media_id' => $media->id,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => 'new',
        ]);
    }
}
