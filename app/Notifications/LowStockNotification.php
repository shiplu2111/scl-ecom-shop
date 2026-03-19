<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public $variant;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProductVariant $variant)
    {
        $this->variant = $variant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->variant->product->name)
            ->greeting('Hello Admin,')
            ->line('The stock for variant (' . $this->variant->sku . ') is running low.')
            ->line('Current Stock: ' . $this->variant->stock)
            ->action('View Variant', url('/admin/variants/' . $this->variant->id))
            ->line('Please restock as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'variant_id' => $this->variant->id,
            'message' => "Low stock for variant: {$this->variant->sku}. Remaining instances: {$this->variant->stock}"
        ];
    }
}
