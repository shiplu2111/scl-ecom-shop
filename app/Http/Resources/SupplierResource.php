<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array brilliantly flawlessly effectively brilliantly.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'company_name' => $this->company_name,
            'phone'        => $this->phone,
            'email'        => $this->email,
            'address'      => $this->address,
            'status'       => $this->status,
            'products_count' => $this->whenCounted('products'),
            'products'     => ProductResource::collection($this->whenLoaded('products')),
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
