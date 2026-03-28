<?php

namespace App\Services;

use App\Models\Order;
use App\Events\DeliveryChargePaid;
use Illuminate\Support\Facades\DB;

    protected PaymentGatewayConfigService $configService;

    public function __construct(PaymentGatewayConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Initiate UddoktaPay payment.
     */
    public function initiateUddoktaPay(Order $order)
    {
        $config = $this->configService->getActiveGateway('uddoktapay');
        
        if (!$config) {
            throw new \Exception('UddoktaPay is not configured or active.');
        }

        $baseUrl = $config->environment === 'sandbox' 
            ? 'https://sandbox.uddoktapay.com/api/checkout-v2' 
            : 'https://uddoktapay.com/api/checkout-v2';

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Api-Key' => $config->secret_key, // Dynamically decrypted via model cast
            'Content-Type' => 'application/json',
        ])->post($baseUrl, [
            'full_name' => $order->user->name,
            'email' => $order->user->email,
            'amount' => $order->grand_total,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            'redirect_url' => $config->success_url ?? url('/payment/success'),
            'cancel_url' => $config->cancel_url ?? url('/payment/cancel'),
            'webhook_url' => $config->callback_url ?? route('uddoktapay.callback'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Create a pending transaction
            \App\Models\Transaction::create([
                'order_id' => $order->id,
                'gateway' => 'uddoktapay',
                'amount' => $order->grand_total,
                'status' => 'pending',
                'response_payload' => json_encode($data),
            ]);

            return $data['payment_url'];
        }

        throw new \Exception('Failed to initiate UddoktaPay payment: ' . $response->body());
    }

    /**
     * Verify UddoktaPay callback.
     */
    public function verifyUddoktaPay(array $payload, string $receivedSignature)
    {
        $config = $this->configService->getActiveGateway('uddoktapay');
        
        if (!$config) {
            return false;
        }

        // Verify Signature
        $apiKey = $config->secret_key;
        // UddoktaPay typically sends 'x-api-key' for verification in some versions, 
        // but often the signature is verified via standard hashing. 
        // Based on docs, verification might require comparing the received signature.
        
        // Mock verification for demonstration as per requirement
        if ($receivedSignature !== $apiKey) {
            // Log verification failure
            return false;
        }

        return DB::transaction(function() use ($payload, $config) {
            $orderId = $payload['metadata']['order_id'] ?? null;
            $order = Order::find($orderId);

            if (!$order) {
                return false;
            }

            // Prevent duplicate transaction processing
            $transaction = \App\Models\Transaction::where('transaction_id', $payload['transaction_id'])->first();
            if ($transaction && $transaction->status === 'success') {
                return true;
            }

            // Record transaction
            \App\Models\Transaction::updateOrCreate(
                ['transaction_id' => $payload['transaction_id']],
                [
                    'order_id' => $order->id,
                    'gateway' => 'uddoktapay',
                    'amount' => $payload['amount'],
                    'status' => 'success',
                    'response_payload' => json_encode($payload),
                ]
            );

            // Update order status via events or direct call
            $this->recordPayment($order, $payload['amount'], 'online');

            return true;
        });
    }

    /**
     * Handle payment for delivery charge.
     */
    public function payDeliveryCharge(Order $order, $amount)
    {
        return DB::transaction(function() use ($order, $amount) {
            // In a real scenario, this would interface with a payment gateway
            // For now, we assume the payment was successful if this method is called
            
            event(new DeliveryChargePaid($order, $amount));
            
            return $order->fresh();
        });
    }

    /**
     * Handle full or partial payment (future use).
     */
    public function recordPayment(Order $order, $amount, $paymentMethod = 'online')
    {
        return DB::transaction(function() use ($order, $amount, $paymentMethod) {
            $order->update([
                'paid_amount' => $order->paid_amount + $amount,
                'due_amount' => $order->grand_total - ($order->paid_amount + $amount),
            ]);

            if ($order->due_amount <= 0) {
                $order->update(['payment_status' => 'paid']);
            }

            return $order;
        });
    }
}
