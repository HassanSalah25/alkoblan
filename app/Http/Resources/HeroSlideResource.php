<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSlideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'tag_ar' => $this->tag_ar,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'subtitle' => $this->subtitle,
            'subtitle_ar' => $this->subtitle_ar,
            'button_text' => $this->button_text,
            'button_text_ar' => $this->button_text_ar,
            'button_url' => $this->button_url,
            'button2_text' => $this->button2_text,
            'button2_text_ar' => $this->button2_text_ar,
            'button2_url' => $this->button2_url,
            'image_desktop' => $this->whenLoaded('imageDesktop', fn () => $this->imageDesktop ? new MediaResource($this->imageDesktop) : null),
            'image_mobile' => $this->whenLoaded('imageMobile', fn () => $this->imageMobile ? new MediaResource($this->imageMobile) : null),
            'sort_order' => $this->sort_order,
        ];
    }
}
