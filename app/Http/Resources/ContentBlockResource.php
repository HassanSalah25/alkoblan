<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'subtitle' => $this->subtitle,
            'subtitle_ar' => $this->subtitle_ar,
            'content' => $this->content,
            'content_ar' => $this->content_ar,
            'image' => $this->whenLoaded('image', fn () => $this->image ? new MediaResource($this->image) : null),
            'button_text' => $this->button_text,
            'button_text_ar' => $this->button_text_ar,
            'button_url' => $this->button_url,
            'extra' => $this->extra,
        ];
    }
}
