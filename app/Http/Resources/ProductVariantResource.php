<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'label' => $this->label,
            'price' => $this->price !== null ? (float) $this->price : null,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'stock_quantity' => $this->stock_quantity,
            'is_active' => (bool) $this->is_active,
            'attribute_values' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
        ];
    }
}
