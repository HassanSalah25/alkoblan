<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'url' => $this->url,
            'icon' => $this->icon,
            'open_new_tab' => (bool) $this->open_new_tab,
            'sort_order' => $this->sort_order,
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
