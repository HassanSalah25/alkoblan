<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cover_letter' => $this->cover_letter,
            'status' => $this->status,
            'job' => $this->whenLoaded('job', fn () => [
                'id' => $this->job->id,
                'title' => $this->job->title,
                'slug' => $this->job->slug,
            ]),
            'cv_url' => $this->whenLoaded('cv', fn () => $this->cv?->url),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
