<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobOpeningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'department' => $this->department,
            'location' => $this->location,
            'employment_type' => $this->employment_type,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'requirements' => $this->requirements,
            'requirements_ar' => $this->requirements_ar,
            'benefits' => $this->benefits,
            'benefits_ar' => $this->benefits_ar,
            'deadline' => $this->deadline?->toDateString(),
        ];
    }
}
