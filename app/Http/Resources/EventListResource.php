<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'slug' => $this->slug,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'location' => $this->location,
            'location_ar' => $this->location_ar,
            'event_date' => $this->event_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'is_featured' => (bool) $this->is_featured,
            'featured_image' => $this->whenLoaded('featuredImage', fn () => $this->featuredImage ? new MediaResource($this->featuredImage) : null),
        ];
    }
}
