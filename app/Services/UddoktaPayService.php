<?php

namespace App\Services;

use App\Models\PaymentCredential;
use Illuminate\Support\Facades\Log;

class UddoktaPayService
{
    protected function getCredentials(string $name = 'UddoktaPay')
    {
        $cred = PaymentCredential::where('name', $name)->where('is_active', true)->first();
        if (!$cred) {
            throw new \Exception("Active credentials for {$name} not found.");
        }
        return $cred;
    }

    public function initiatePayment($order)
    {
        $credentials = $this->getCredentials();
        
        // Placeholder implementation
        $orderId = is_object($order) ? $order->id : $order;
        Log::info("Generating payment URL via UddoktaPay using {$credentials->environment} environment for order id: {$orderId}");
        
        return [
            'status' => true,
            'payment_url' => "{$credentials->callback_url}?order_id={$orderId}&test=true",
        ];
    }
}
