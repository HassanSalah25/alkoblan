<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'position' => $this->position,
            'position_ar' => $this->position_ar,
            'company' => $this->company,
            'company_ar' => $this->company_ar,
            'content' => $this->content,
            'content_ar' => $this->content_ar,
            'image' => $this->whenLoaded('image', fn () => $this->image ? new MediaResource($this->image) : null),
            'rating' => $this->rating,
        ];
    }
}
