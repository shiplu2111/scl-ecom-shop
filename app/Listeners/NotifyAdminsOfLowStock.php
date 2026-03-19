<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsOfLowStock
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new \App\Notifications\LowStockNotification($event->variant));
    }
}
