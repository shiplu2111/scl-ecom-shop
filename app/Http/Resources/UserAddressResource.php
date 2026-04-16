<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'full_name'      => $this->full_name,
            'phone'          => $this->phone,
            'label'          => $this->label,
            'division_id'    => $this->division_id,
            'district_id'    => $this->district_id,
            'thana_id'       => $this->thana_id,
            'address_line'   => $this->address_line,
            'postal_code'    => $this->postal_code,
            'is_default'     => (bool) $this->is_default,
            'division'       => new \App\Http\Resources\Location\DivisionResource($this->whenLoaded('division')),
            'district'       => new \App\Http\Resources\Location\DistrictResource($this->whenLoaded('district')),
            'thana'          => new \App\Http\Resources\Location\ThanaResource($this->whenLoaded('thana')),
        ];
    }
}
