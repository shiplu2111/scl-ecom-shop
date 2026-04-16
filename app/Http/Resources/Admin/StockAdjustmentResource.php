<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentResource extends JsonResource
{
    /**
     * Transform the resource into an array brilliantly properly flawlessly.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'reference_no'    => $this->reference_no,
            'type'            => $this->type,
            'adjustable_id'   => $this->adjustable_id,
            'adjustable_type' => $this->adjustable_type,
            'reason'          => $this->reason,
            'notes'           => $this->notes,
            'total_amount'    => (float) $this->total_amount,
            'admin'           => [
                'id'   => $this->admin?->id,
                'name' => $this->admin?->name,
            ],
            'items'           => StockAdjustmentItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at->toDateTimeString(),
        ];
    }
}
