<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'value_ar' => $this->value_ar,
            'attribute' => $this->whenLoaded('attribute', fn () => [
                'id' => $this->attribute->id,
                'name' => $this->attribute->name,
                'name_ar' => $this->attribute->name_ar,
                'slug' => $this->attribute->slug,
                'type' => $this->attribute->type,
            ]),
        ];
    }
}
