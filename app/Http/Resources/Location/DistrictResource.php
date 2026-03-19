<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'division_id' => $this->division_id,
            'name' => $this->name,
            'delivery_charge' => $this->delivery_charge,
            'division' => new DivisionResource($this->whenLoaded('division')),
            'thanas' => ThanaResource::collection($this->whenLoaded('thanas')),
        ];
    }
}
