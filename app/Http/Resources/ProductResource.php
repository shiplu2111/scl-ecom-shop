<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'sku'         => $this->sku,
            'description'    => $this->description,
            'price'          => $this->price,
            'discount_price' => $this->discount_price,
            'is_active'      => (bool)$this->is_active,
            'is_featured'    => (bool)$this->is_featured,
            'is_flash_sale'  => (bool)$this->is_flash_sale,
            'is_best_seller' => (bool)$this->is_best_seller,
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'brand'       => new BrandResource($this->whenLoaded('brand')),
            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images'      => ProductImageResource::collection($this->whenLoaded('images')),
            'seo_metadata' => new SeoMetadataResource($this->whenLoaded('seoMetadata')),
            'specifications' => $this->specifications,
        ];
    }
}
