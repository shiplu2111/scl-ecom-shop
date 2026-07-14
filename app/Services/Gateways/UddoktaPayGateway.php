<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\PaymentCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderInvoiceMail;

/**
 * UddoktaPay Payment Gateway (V2)
 *
 * Docs:
 *   - Create Charge  : {base_url}/api/checkout-v2
 *   - Verify Payment : {base_url}/api/verify-payment
 *   - Webhook        : Validate via RT-UDDOKTAPAY-API-KEY header
 *
 * Header for all requests: RT-UDDOKTAPAY-API-KEY
 */
class UddoktaPayGateway implements PaymentGatewayInterface
{
    protected function getCredentials(): PaymentCredential
    {
        $cred = PaymentCredential::where('name', 'UddoktaPay')->where('is_active', true)->first();
        if (!$cred) {
            throw new \Exception('UddoktaPay is not configured or not active. Please configure it in the admin panel.');
        }
        return $cred;
    }

    protected function getBaseUrl(PaymentCredential $credentials): string
    {
        // Prefer exactly what admin saved in the database
        if (!empty($credentials->base_url)) {
            $base = rtrim((string) $credentials->base_url, '/');
        } elseif (strtolower((string) $credentials->environment) === 'sandbox') {
            $base = 'https://sandbox.uddoktapay.com/api';
        } else {
            $base = 'https://uddoktapay.com/api';
        }

        // Ensure .../api suffix if admin saved host only
        if (!str_ends_with($base, '/api')) {
            $base .= '/api';
        }

        return $base;
    }

    protected function resolveApiKey(PaymentCredential $credentials): string
    {
        return $credentials->getResolvedSecretKey();
    }

    // -------------------------------------------------------------------------
    // Create Charge  →  POST {base_url}/api/checkout-v2
    // Docs: https://uddoktapay.readme.io/reference/create-charge-api-guideline
    // -------------------------------------------------------------------------
    public function initiatePayment($order): array
    {
        $credentials = $this->getCredentials();
        $baseUrl     = $this->getBaseUrl($credentials);
        $apiKey      = $this->resolveApiKey($credentials);

        // Determine amount: full total for online orders, delivery charge for COD prepayment
        $amount = $order instanceof Order ? $order->grand_total : $order->amount;
        $orderId = $order->id;
        $orderNumber = $order->order_number;

        if ($order instanceof Order) {
            $codSettings = \App\Models\Setting::where('group', 'cod')->pluck('value', 'key');
            if ($order->payment_method === 'cod' && ($codSettings['cod_prepayment_required'] ?? '0') === '1') {
                $amount = $order->delivery_charge;
            }
        }

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $backendUrl  = rtrim((string) config('app.url'), '/');

        if ($frontendUrl === '' || str_contains($frontendUrl, 'localhost') || str_contains($frontendUrl, '127.0.0.1')) {
            throw new \Exception(
                'Invalid APP_FRONTEND_URL. Set your live shop URL in .env (APP_FRONTEND_URL), then run: php artisan config:clear'
            );
        }

        if ($backendUrl === '' || str_contains($backendUrl, 'localhost') || str_contains($backendUrl, '127.0.0.1')) {
            throw new \Exception(
                'Invalid APP_URL. Set your live API URL in .env (APP_URL), then run: php artisan config:clear'
            );
        }

        $fullName = $order instanceof Order
            ? ($order->user->name ?? $order->shipping_full_name ?? 'Customer')
            : ($order->checkout_data['full_name'] ?? 'Customer');
        $email = $order instanceof Order
            ? ($order->user->email ?? $order->shipping_email ?? null)
            : ($order->checkout_data['email'] ?? null);

        $payload = [
            'full_name'    => $fullName ?: 'Customer',
            'email'        => $email ?: 'customer@example.com',
            'amount'       => number_format((float) $amount, 2, '.', ''),
            'metadata'     => [
                'user_id'      => (string) ($order->user_id ?? 1),
                'order_id'     => (string) $orderId,
                'order_number' => (string) $orderNumber,
                'is_draft'     => $order instanceof Order ? '0' : '1',
            ],
            'redirect_url' => $frontendUrl . '/payment/success',
            'cancel_url'   => $frontendUrl . '/payment/cancel',
            'webhook_url'  => $backendUrl . '/API/V1/payment/uddoktapay/webhook',
            'return_type'  => 'GET',
        ];

        Log::info('UddoktaPay - Initiation Details', [
            'order_id' => $orderId,
            'amount_calculated' => $amount,
            'base_url' => $baseUrl,
            'frontend_url' => $frontendUrl,
            'redirect_url' => $payload['redirect_url'],
            'cancel_url' => $payload['cancel_url'],
        ]);

        try {
            $endpoint = "{$baseUrl}/checkout-v2";
            $response = Http::withHeaders([
                'RT-UDDOKTAPAY-API-KEY' => $apiKey,
                'Accept'                => 'application/json',
                'Content-Type'          => 'application/json',
            ])->post($endpoint, $payload);

            $result = $response->json();

            if (!$result) {
                $rawBody = $response->body();
                Log::error('UddoktaPay – Initiation failed (Non-JSON response)', [
                    'order'  => $order->id,
                    'status' => $response->status(),
                    'raw'    => $rawBody,
                    'endpoint' => $endpoint,
                ]);
                return ['status' => false, 'message' => "UddoktaPay Error: Server returned non-JSON response (HTTP {$response->status()}). Check gateway base URL / API key."];
            }

            Log::info('UddoktaPay – Checkout API response', ['status' => $response->status(), 'body' => $result, 'endpoint' => $endpoint]);

            // Success: API returns { status: true, payment_url: "..." }
            if ($response->successful() && !empty($result['payment_url'])) {
                try {
                    $txPayload = [
                        'user_id'        => $order->user_id ?? null,
                        'gateway'        => 'uddoktapay',
                        'transaction_id' => $result['invoice_id'] ?? ('UP_' . time()),
                        'amount'         => $amount,
                        'type'           => 'master',
                        'description'    => "Pending payment for #{$orderNumber}",
                        'status'         => 'pending',
                    ];

                    if ($order instanceof Order) {
                        $txPayload['order_id'] = $order->id;
                        $order->transactions()->create($txPayload);
                    } else {
                        $txPayload['order_id'] = null;
                        $txPayload['draft_order_id'] = $order->id;
                        \App\Models\Transaction::create($txPayload);
                    }
                } catch (\Throwable $e) {
                    // Do not block redirect if pending transaction row fails
                    Log::error('UddoktaPay – Failed to store pending transaction', [
                        'order' => $orderId,
                        'error' => $e->getMessage(),
                    ]);
                }

                return [
                    'status'       => true,
                    'redirect_url' => $result['payment_url'],
                    'invoice_id'   => $result['invoice_id'] ?? null,
                ];
            }

            $errorMessage = $result['message'] ?? $result['error'] ?? "HTTP {$response->status()}";
            Log::error('UddoktaPay – Initiation unsuccessful', ['order' => $order->id, 'response' => $result]);

            return ['status' => false, 'message' => "UddoktaPay Initiation Error: {$errorMessage}"];

        } catch (\Exception $e) {
            Log::error('UddoktaPay – Initiation exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'Gateway connection error: ' . $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Verify Payment  →  POST {base_url}/api/verify-payment
    // Body: { invoice_id: "..." }
    // Docs: https://uddoktapay.readme.io/reference/verify-payment-api-guideline
    // Called from: /payment/uddoktapay/verify?transaction_id={invoice_id}
    // -------------------------------------------------------------------------
    public function verifyPayment(string $invoiceId): array
    {
        $credentials = $this->getCredentials();
        $baseUrl     = $this->getBaseUrl($credentials);
        $apiKey      = $this->resolveApiKey($credentials);

        Log::info('UddoktaPay – Verifying payment', ['invoice_id' => $invoiceId]);

        try {
            $endpoint = "{$baseUrl}/verify-payment";
            $response = Http::withHeaders([
                'RT-UDDOKTAPAY-API-KEY' => $apiKey,
                'Accept'                => 'application/json',
                'Content-Type'          => 'application/json',
            ])->post($endpoint, [
                'invoice_id' => (string) $invoiceId,
            ]);

            $result = $response->json();

            Log::info('UddoktaPay – Verify API response', ['status' => $response->status(), 'body' => $result]);

            // Success response: { status: "COMPLETED", transaction_id: "...", ... }
            if ($response->successful() && isset($result['status']) && in_array($result['status'], ['COMPLETED', 'SUCCESS'])) {
                $transaction = Transaction::where('transaction_id', $invoiceId)->first();

                if ($transaction && $transaction->status !== 'success') {
                    $transaction->update([
                        'status'         => 'success',
                        'transaction_id' => $result['transaction_id'] ?? $invoiceId,
                    ]);

                    $order = $transaction->order;
                    
                    // If this was a draft order, convert it to a real order now
                    if (!$order && $transaction->draft_order_id) {
                        $orderService = app(\App\Services\OrderService::class);
                        $order = $orderService->completeOrderFromDraft($transaction->draftOrder);
                        
                        // Link the new order back to the transaction
                        if ($order) {
                            $transaction->update(['order_id' => $order->id]);
                        }
                    }

                    if ($order) {
                        $order->update(['payment_status' => 'paid', 'order_status' => 'processing']);
                        $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'user']);
                        
                        // Create Split Transactions flawlessly brilliantly properly
                        $this->recordOrderTransactions($order, $result['transaction_id'] ?? $invoiceId);

                        // Send confirmation invoice to customer
                        $this->sendInvoiceEmail($order);

                        // Clear cart after successful payment
                        app(\App\Services\OrderService::class)->clearCartByUserId($order->user_id);
                    }

                    return ['status' => true, 'order' => $order, 'transaction' => $result];
                }

                // Already processed – return the order anyway
                $transaction = $transaction ?? Transaction::where('transaction_id', $invoiceId)->first();
                return ['status' => true, 'order' => $transaction?->order, 'transaction' => $result];
            }

            Log::warning('UddoktaPay – Verification failed or incomplete', ['invoice_id' => $invoiceId, 'response' => $result]);
            return ['status' => false, 'message' => $result['message'] ?? "Payment not completed (status: {$result['status']})"];

        } catch (\Exception $e) {
            Log::error('UddoktaPay – Verify exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'Verification error: ' . $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Validate & Handle Webhook
    // UddoktaPay sends the API key in the RT-UDDOKTAPAY-API-KEY header.
    // The webhook payload is the same as the verify-payment success response.
    // Docs: https://uddoktapay.readme.io/reference/validate-webhook
    // -------------------------------------------------------------------------
    public function handleWebhook(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $this->getCredentials();
        $apiKey      = $credentials->secret_key;

        // Step 1: Validate API key from header (per official docs)
        $headerKey = $request->header('RT-UDDOKTAPAY-API-KEY');
        if ($headerKey !== $apiKey) {
            Log::warning('UddoktaPay – Webhook unauthorized', ['received_key' => $headerKey]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data      = $request->all();
        $invoiceId = $data['invoice_id'] ?? null;
        $status    = $data['status'] ?? null;

        Log::info('UddoktaPay – Webhook received', ['invoice_id' => $invoiceId, 'status' => $status]);

        if (!$invoiceId || !in_array($status, ['COMPLETED', 'SUCCESS'])) {
            return response()->json(['message' => 'Invalid webhook data'], 400);
        }

        $transaction = Transaction::where('transaction_id', $invoiceId)->first();

        if (!$transaction) {
            Log::warning('UddoktaPay – Webhook: transaction not found', ['invoice_id' => $invoiceId]);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status !== 'success') {
            $transaction->update([
                'status'         => 'success',
                'transaction_id' => $data['transaction_id'] ?? $invoiceId,
            ]);

            $order = $transaction->order;
            
            // If this was a draft order, convert it to a real order now
            if (!$order && $transaction->draft_order_id) {
                $orderService = app(\App\Services\OrderService::class);
                $order = $orderService->completeOrderFromDraft($transaction->draftOrder);
            }

            if ($order) {
                $order->update(['payment_status' => 'paid', 'order_status' => 'processing']);
                $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'user']);

                // Create Split Transactions flawlessly brilliantly properly
                $this->recordOrderTransactions($order, $data['transaction_id'] ?? $invoiceId);

                // Send confirmation invoice to customer
                $this->sendInvoiceEmail($order);

                // Clear cart after successful payment
                app(\App\Services\OrderService::class)->clearCartByUserId($order->user_id);
            }

            Log::info('UddoktaPay – Webhook: order updated', ['order_id' => $order?->id]);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }

    // -------------------------------------------------------------------------
    // Send Order Invoice Email
    // -------------------------------------------------------------------------
    protected function sendInvoiceEmail(Order $order): void
    {
        try {
            $recipientEmail = $order->user?->email;
            if ($recipientEmail) {
                Mail::to($recipientEmail)->send(new OrderInvoiceMail($order));
                Log::info('UddoktaPay – Invoice email sent', ['order' => $order->id, 'email' => $recipientEmail]);
            }
        } catch (\Exception $e) {
            Log::error('UddoktaPay – Invoice email failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record dual transactions (Product + Delivery) flawlessly brilliantly properly.
     */
    protected function recordOrderTransactions($order, string $transactionId): void
    {
        if (!$order) return;

        // Avoid duplicates by checking transaction_id suffix flawlessly properly brilliantly
        if (Transaction::where('transaction_id', $transactionId . '_PROD')->exists()) {
            return;
        }

        // 1. Product Transaction (Subtotal - Discount)
        $productAmount = number_format($order->subtotal - $order->discount_amount, 2, '.', '');
        $order->transactions()->create([
            'user_id'        => $order->user_id,
            'gateway'        => 'uddoktapay',
            'transaction_id' => $transactionId . '_PROD',
            'amount'         => $productAmount,
            'type'           => 'product',
            'description'    => "Product payment for order #{$order->order_number}",
            'status'         => 'success',
        ]);

        // 2. Delivery Transaction
        if ($order->delivery_charge > 0) {
            $order->transactions()->create([
                'user_id'        => $order->user_id,
                'gateway'        => 'uddoktapay',
                'transaction_id' => $transactionId . '_DELV',
                'amount'         => $order->delivery_charge,
                'type'           => 'delivery',
                'description'    => "Delivery charge for order #{$order->order_number}",
                'status'         => 'success',
            ]);
        }
    }

    /**
     * Refund a payment.
     * 
     * @param string $transactionId
     * @param float $amount
     * @return array
     */
    public function refundPayment(string $transactionId, float $amount): array
    {
        $credentials = $this->getCredentials();
        $baseUrl     = $this->getBaseUrl($credentials);
        $apiKey      = (str_starts_with($credentials->secret_key, 'eyJpdiI6')) 
              ? decrypt($credentials->secret_key) 
              : $credentials->secret_key;

        Log::info('UddoktaPay – Refunding payment', ['invoice_id' => $transactionId, 'amount' => $amount]);

        try {
            $response = Http::withHeaders([
                'RT-UDDOKTAPAY-API-KEY' => $apiKey,
                'Accept'                => 'application/json',
                'Content-Type'          => 'application/json',
            ])->post("{$baseUrl}/refund-payment", [
                'invoice_id' => $transactionId,
                'amount'     => (string) $amount,
            ]);

            $result = $response->json();

            if ($response->successful() && ($result['status'] ?? false)) {
                return ['status' => true, 'message' => $result['message'] ?? 'Refund processed successfully'];
            }

            return ['status' => false, 'message' => $result['message'] ?? 'Refund failed'];

        } catch (\Exception $e) {
            Log::error('UddoktaPay – Refund exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'Refund error: ' . $e->getMessage()];
        }
    }
}
