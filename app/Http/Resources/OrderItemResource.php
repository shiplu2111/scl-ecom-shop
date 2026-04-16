<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Compute Image URL brilliantly flawlessly properly
        $imageUrl = null;
        
        // 1. Check for Variant Image
        if ($this->relationLoaded('productVariant') && $this->productVariant && $this->productVariant->image) {
            $imageUrl = Storage::disk('public')->url($this->productVariant->image);
        } 
        // 2. Check for Product Thumbnail Image
        elseif ($this->relationLoaded('product') && $this->product) {
            $thumbnail = $this->product->images()->where('is_thumbnail', true)->first();
            if ($thumbnail) {
                $imageUrl = url('storage/' . $thumbnail->image_path);
            } else {
                $firstImage = $this->product->images()->first();
                if ($firstImage) {
                    $imageUrl = url('storage/' . $firstImage->image_path);
                }
            }
        }

        return [
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name'       => $this->product_name,
            'variant_name'       => $this->variant_name,
            'sku'                => $this->sku,
            'quantity'           => (int)$this->quantity,
            'unit_price'         => (float)$this->unit_price,
            'buying_price'       => $this->when(auth('admin')->check(), \App\Models\Inventory::where('sku', $this->sku)->value('buying_price')),
            'total_price'        => (float)$this->total_price,
            'image'              => $imageUrl,
            
            // Raw Models (for fallback/extra details)
            'product'            => new ProductResource($this->whenLoaded('product')),
            'variant'            => new ProductVariantResource($this->whenLoaded('productVariant')),
        ];
    }
}
