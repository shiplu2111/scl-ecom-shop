<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->resource->getResolvedPrice();
        $total = $price * $this->quantity;

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unit_price' => $price,
            'total_price' => $total,
            'selected_color' => $this->productVariant ? $this->productVariant->color : null,
            'selected_size' => $this->productVariant ? $this->productVariant->size : null,
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
        ];
    }
}
