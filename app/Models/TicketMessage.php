<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_id', 'sender_id', 'sender_type', 'message', 'attachments'])]
class TicketMessage extends Model
{
    protected $casts = [
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the sender of the message (User or Admin).
     */
    public function sender()
    {
        return $this->morphTo();
    }
}
