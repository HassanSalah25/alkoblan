<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'city' => $this->city,
            'city_ar' => $this->city_ar,
            'address' => $this->address,
            'address_ar' => $this->address_ar,
            'phone' => $this->phone,
            'phone_secondary' => $this->phone_secondary,
            'email' => $this->email,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'maps_url' => $this->maps_url,
            'working_hours' => $this->working_hours,
            'working_hours_ar' => $this->working_hours_ar,
        ];
    }
}
