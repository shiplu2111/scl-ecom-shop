<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $message;
    protected $redirectUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $redirectUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'redirect_url' => $this->redirectUrl,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => [
                'title' => $this->title,
                'message' => $this->message,
                'redirect_url' => $this->redirectUrl,
            ],
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
