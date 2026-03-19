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
            'description' => $this->description,
            'price'       => $this->price,
            'is_active'   => (bool)$this->is_active,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'brand'       => new BrandResource($this->whenLoaded('brand')),
            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images'      => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
