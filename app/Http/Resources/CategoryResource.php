<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'parent_id' => $this->parent_id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'children_count' => $this->children()->count(),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'seo_metadata' => new SeoMetadataResource($this->whenLoaded('seoMetadata')),
        ];
    }
}
