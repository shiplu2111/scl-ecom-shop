<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'delivery_charge' => $this->delivery_charge,
            'grand_total' => $this->grand_total,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'order_status' => $this->order_status,
            'delivery_charge_paid' => (bool) $this->delivery_charge_paid,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'courier_name' => $this->courier_name,
            'tracking_number' => $this->tracking_number,
            'consignment_id' => $this->consignment_id,
            'created_at' => $this->created_at,
            'address' => $this->shippingAddress?->address_line,
            'thana' => $this->shippingAddress?->thana ? ['name' => $this->shippingAddress->thana->name] : null,
            'district' => $this->shippingAddress?->district ? ['name' => $this->shippingAddress->district->name] : null,
            'division' => $this->shippingAddress?->division ? ['name' => $this->shippingAddress->division->name] : null,
            'postal_code' => $this->shippingAddress?->postal_code,
            'items_count' => $this->whenCounted('items', $this->items_count, $this->whenLoaded('items', function() {
                return $this->items->count();
            })),
            'invoice_data' => [
                'issue_date' => $this->created_at->format('Y-m-d'),
                'due_date' => $this->payment_method === 'cod' ? 'On Delivery' : 'Paid',
                'billed_to' => $this->whenLoaded('user', function () {
                    return [
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ];
                }),
                'shipping_address' => $this->whenLoaded('shippingAddress'),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'histories' => OrderHistoryResource::collection($this->whenLoaded('histories')),
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
