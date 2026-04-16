<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inventory = \App\Models\Inventory::where('sku', $this->sku)->first();
        
        return [
            'id'    => $this->id,
            'sku'   => $this->sku,
            'short_description' => $this->short_description,
            'size'  => $this->size,
            'color' => $this->color,
            'image' => $this->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image) : null,
            'price'          => $this->price,
            'discount_price' => $this->discount_price,
            'is_flash_sale'  => $this->activeFlashSaleItem()->exists(),
            'flash_sale_details' => $this->activeFlashSaleItem ? [
                'id' => $this->activeFlashSaleItem->id,
                'price' => (float)($this->activeFlashSaleItem->price ?? $this->price),
                'sale_price' => (float)$this->activeFlashSaleItem->sale_price,
                'quantity_limit' => (int)$this->activeFlashSaleItem->quantity_limit,
                'sold_quantity' => (int)$this->activeFlashSaleItem->sold_quantity,
                'available_quantity' => max(0, (int)$this->activeFlashSaleItem->quantity_limit - (int)$this->activeFlashSaleItem->sold_quantity),
                'end_time' => $this->activeFlashSaleItem->flashSale->end_time->toISOString(),
                'discount_percentage' => ($this->activeFlashSaleItem->price ?? $this->price) > 0
                    ? round((1 - ($this->activeFlashSaleItem->sale_price / ($this->activeFlashSaleItem->price ?? $this->price))) * 100)
                    : 0,
            ] : null,
            'stock'          => $inventory ? $inventory->quantity : 0,
            'buying_price'   => $inventory ? $inventory->buying_price : null,
            'supplier'       => $inventory ? new SupplierResource($inventory->supplier) : null,
        ];
    }
}
