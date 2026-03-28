<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'message'     => $this->message,
            'attachments' => $this->attachments,
            'sender'      => [
                'id'    => $this->sender_id,
                'name'  => $this->sender->name,
                'type'  => str_contains($this->sender_type, 'Admin') ? 'admin' : 'customer',
            ],
            'created_at'  => $this->created_at,
        ];
    }
}
