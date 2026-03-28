<?php

namespace App\Listeners;

use App\Events\DeliveryChargePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ConfirmOrderAfterPayment
{
    /**
     * Handle the event.
     */
    public function handle(DeliveryChargePaid $event): void
    {
        $order = $event->order;
        
        $order->update([
            'order_status' => 'confirmed',
            'payment_status' => 'advanced_paid',
            'delivery_charge_paid' => true,
            'paid_amount' => $order->paid_amount + $event->amount,
            'due_amount' => $order->grand_total - ($order->paid_amount + $event->amount),
        ]);
    }
}
