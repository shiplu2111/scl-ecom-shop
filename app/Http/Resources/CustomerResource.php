<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'avatar'            => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'is_active'         => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            
            // Statistics
            'total_spent'       => $this->whenLoaded('orders', function() {
                return (float) $this->orders->sum('grand_total');
            }, 0),
            'orders_count'      => $this->whenCounted('orders', $this->orders_count, $this->whenLoaded('orders', function() {
                return $this->orders->count();
            }, 0)),

            // Loaded Relationships
            'addresses'         => UserAddressResource::collection($this->whenLoaded('addresses')),
            'orders'            => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
