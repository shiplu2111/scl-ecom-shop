<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminTicketRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_replied',
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'customer_name' => $this->message->sender->name,
            'message' => 'New reply on ticket #' . $this->ticket->ticket_number . ' by ' . $this->message->sender->name,
            'url' => '/tickets?id=' . $this->ticket->id,
        ];
    }
}
