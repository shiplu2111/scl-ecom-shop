<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryChargePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $amount;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, $amount)
    {
        $this->order = $order;
        $this->amount = $amount;
    }
}
