<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (string)$this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'sku'               => $this->sku,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            // When variants exist, price comes from min_price (never manually set)
            'price'             => $this->min_price !== null ? (float)$this->min_price : (float)$this->price,
            'discount_price'    => $this->min_discount_price !== null ? (float)$this->min_discount_price : null,
            'min_price'         => $this->min_price !== null ? (float)$this->min_price : null,
            'max_price'         => $this->max_price !== null ? (float)$this->max_price : null,
            'min_discount_price'=> $this->min_discount_price !== null ? (float)$this->min_discount_price : null,
            'display_price'     => $this->display_price,
            'is_active'         => (bool)$this->is_active,
            'is_featured'       => (bool)$this->is_featured,
            'is_flash_sale'     => $this->activeFlashSaleItem()->exists(),
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
            'is_best_seller'    => (bool)$this->is_best_seller,
            'isNew'             => $this->created_at->diffInDays(now()) <= 7,
            'stock'             => (int)(\App\Models\Inventory::where('sku', $this->sku)->value('quantity') ?? 
                                   ($this->relationLoaded('variants') ? \App\Models\Inventory::whereIn('sku', $this->variants->pluck('sku'))->sum('quantity') : 0)),
            'buying_price'      => $this->when(auth('admin')->check(), \App\Models\Inventory::where('sku', $this->sku)->value('buying_price')),
            'supplier'          => new SupplierResource(\App\Models\Inventory::where('sku', $this->sku)->first()?->supplier),
            'rating'            => (float)$this->average_rating,
            'reviewCount'       => (int)$this->review_count,
            'category'          => new CategoryResource($this->whenLoaded('category')),
            'brand'             => new BrandResource($this->whenLoaded('brand')),
            'variants'          => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images'            => ProductImageResource::collection($this->whenLoaded('images')),
            'seo_metadata'      => new SeoMetadataResource($this->whenLoaded('seoMetadata')),
            'specifications'    => $this->specifications,
            'createdAt'         => $this->created_at->toISOString(),
        ];
    }
}
