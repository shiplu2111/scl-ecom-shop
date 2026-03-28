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
            'created_at' => $this->created_at,
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
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
