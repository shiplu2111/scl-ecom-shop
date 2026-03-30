<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transform the resource into an array flawlessly flawlessly properly impeccably brilliance.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'product_name'    => $this->product?->name,
            'product_sku'     => $this->product?->sku,
            'current_stock'   => $this->quantity,
            'min_stock_level' => $this->low_stock_alert,
            'quantity'        => $this->quantity,
            'low_stock_alert' => $this->low_stock_alert,
            'product'         => new ProductResource($this->whenLoaded('product')),
            'updated_at'      => $this->updated_at->toDateTimeString(),
        ];
    }
}
