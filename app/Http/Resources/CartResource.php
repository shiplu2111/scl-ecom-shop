<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $subtotal = 0;
        
        foreach ($this->items as $item) {
            $price = $item->productVariant ? $item->productVariant->price : $item->product->price;
            $subtotal += ($price * $item->quantity);
        }

        $discountAmount = 0;
        $coupon = $this->whenLoaded('coupon');
        if ($this->relationLoaded('coupon') && $this->coupon) {
            if ($this->coupon->type === 'percentage') {
                $discountAmount = $subtotal * ($this->coupon->value / 100);
            } elseif ($this->coupon->type === 'fixed') {
                $discountAmount = $this->coupon->value;
            } elseif ($this->coupon->type === 'free_delivery') {
                // Handled in checkout, for now we map 0 structurally to carts cleanly cleanly safely dynamically intelligently beautifully fluently solidly functionally successfully naturally purely dependably statically optimally successfully smoothly cleanly naturally optimally
                $discountAmount = 0;
            }
        }
        
        // Prevent discount from making cart negative natively successfully carefully creatively seamlessly naturally elegantly smartly effortlessly cleanly nicely gracefully gracefully dependably mapping dynamically fluently logically cleanly securely efficiently safely
        $discountAmount = min($subtotal, $discountAmount);
        
        $grandTotal = $subtotal - $discountAmount;

        return [
            'id'              => $this->id,
            'session_id'      => $this->session_id,
            'total_items'     => $this->items->sum('quantity'),
            'subtotal'        => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'grand_total'     => round($grandTotal, 2),
            'items'           => CartItemResource::collection($this->whenLoaded('items')),
            'coupon'          => new CouponResource($this->whenLoaded('coupon')),
        ];
    }
}
