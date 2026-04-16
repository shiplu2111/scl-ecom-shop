<?php

namespace App\Notifications;

use App\Models\ProductReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $review;

    public function __construct(ProductReview $review)
    {
        $this->review = $review->load(['product', 'user']);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->review->id,
            'product_name' => $this->review->product->name,
            'customer_name' => $this->review->user->name,
            'rating' => $this->review->rating,
            'comment' => $this->review->comment,
            'image' => $this->review->product->images()->where('is_thumbnail', true)->first()?->image_path,
            'message' => "New {$this->review->rating}-star review for {$this->review->product->name} from {$this->review->user->name}",
            'type' => 'new_review'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
