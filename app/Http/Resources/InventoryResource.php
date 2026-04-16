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
        $name = $this->product?->name;
        if (!$name && $this->variant) {
            $name = $this->variant->product?->name;
            $options = array_filter([$this->variant->size, $this->variant->color]);
            if (!empty($options)) {
                $name .= ' (' . implode(' / ', $options) . ')';
            }
        }

        return [
            'id'              => $this->id,
            'sku'             => $this->sku,
            'product_name'    => $name ?? 'Unknown Product',
            'product'         => [
                'id'    => $this->product_id ?? $this->variant?->product_id,
                'name'  => $name ?? 'Unknown Product',
            ],
            'product_sku'     => $this->sku,
            'current_stock'   => $this->quantity,
            'quantity'        => $this->quantity,
            'min_stock_level' => $this->low_stock_alert,
            'low_stock_alert' => $this->low_stock_alert,
            'alert_quantity'  => $this->low_stock_alert,
            'buying_price'    => (float)$this->buying_price,
            'supplier'        => new SupplierResource($this->whenLoaded('supplier')),
            'product_id'      => $this->product_id ?? $this->variant?->product_id,
            'updated_at'      => $this->updated_at->toDateTimeString(),
        ];
    }
}
