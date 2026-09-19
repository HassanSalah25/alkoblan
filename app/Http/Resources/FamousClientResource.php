<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamousClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'logo' => $this->whenLoaded('logo', fn () => $this->logo ? new MediaResource($this->logo) : null),
            'url' => $this->url,
        ];
    }
}
