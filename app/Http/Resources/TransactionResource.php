<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'order_id'         => $this->order_id,
            'gateway'          => $this->gateway,
            'transaction_id'   => $this->transaction_id,
            'amount'           => $this->amount,
            'status'           => $this->status,
            'response_payload' => $this->response_payload,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            
            // Relationships
            'order'            => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
