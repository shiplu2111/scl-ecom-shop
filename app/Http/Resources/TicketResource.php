<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'ticket_number' => $this->ticket_number,
            'subject'       => $this->subject,
            'status'        => $this->status,
            'priority'      => $this->priority,
            'last_reply_at' => $this->last_reply_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            
            // Relationships
            'user'          => new CustomerResource($this->whenLoaded('user')),
            'messages'      => TicketMessageResource::collection($this->whenLoaded('messages')),
            'latest_message'=> new TicketMessageResource($this->whenLoaded('latestMessage')),
        ];
    }
}
