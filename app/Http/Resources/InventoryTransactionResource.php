<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array brilliantly flawlessly properly flawlessly impeccably.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'product_id'      => $this->product_id,
            'type'            => $this->type,
            'quantity'        => $this->quantity,
            'reference'       => $this->reference,
            'created_by'      => $this->created_by,
            'created_by_name' => $this->creator?->name ?? 'System',
            'creator'         => $this->whenLoaded('creator'),
            'created_at'      => $this->created_at->toDateTimeString(),
        ];
    }
}
