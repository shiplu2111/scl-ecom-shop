<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->productVariant ? $this->productVariant->price : $this->product->price;
        $total = $price * $this->quantity;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => $price,
            'total_price' => $total,
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
        ];
    }
}
