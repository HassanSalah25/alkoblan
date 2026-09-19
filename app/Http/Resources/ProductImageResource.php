<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'sort_order' => $this->sort_order,
            'url' => $this->whenLoaded('media', fn () => $this->media?->url),
            'alt_text' => $this->whenLoaded('media', fn () => $this->media?->alt_text),
        ];
    }
}
