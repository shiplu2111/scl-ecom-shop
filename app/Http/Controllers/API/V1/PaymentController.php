<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Order;
use App\Services\Gateways\PaymentManager;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Payments
 */
class PaymentController extends BaseController
{
    /**
     * Initiate a payment for a confirmed order.
     * POST /payment/initiate
     */
    public function initiate(Request $request)
    {
        // Normalize gateway to lowercase to prevent case-sensitivity validation errors impeccably
        if ($request->has('gateway')) {
            $request->merge(['gateway' => strtolower($request->gateway)]);
        }

        $request->validate([
            'order_id' => 'required|integer',
            'gateway'  => 'required|in:stripe,uddoktapay',
        ]);

        // Try real order first, then draft order
        $order = Order::where('user_id', auth()->id())->find($request->order_id);
        
        if (!$order) {
            $order = \App\Models\DraftOrder::where('user_id', auth()->id())->find($request->order_id);
        }

        if (!$order) {
            return $this->errorResponse('Order not found.', 404);
        }

        if ($order->payment_status === 'paid') {
            return $this->errorResponse('This order has already been paid.', 400);
        }

        try {
            $gateway         = PaymentManager::resolve($request->gateway);
            $initiationResult = $gateway->initiatePayment($order);

            if (!$initiationResult['status']) {
                throw new \Exception($initiationResult['message'] ?? 'Payment initiation failed.');
            }

            return $this->successResponse($initiationResult, 'Payment initiated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Payment initiation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify a payment after return from gateway.
     * GET/POST /payment/{gateway}/verify?transaction_id={invoice_id}
     */
    public function verify(Request $request, $gateway)
    {
        $gateway = strtolower($gateway); // Normalize for case-insensitive matching smoothly
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        try {
            $paymentService = PaymentManager::resolve($gateway);
            $result         = $paymentService->verifyPayment($request->transaction_id);

            return $this->successResponse($result, 'Payment verified successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Payment verification failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle incoming webhook from UddoktaPay.
     * POST /payment/{gateway}/webhook
     */
    public function webhook(Request $request, $gateway)
    {
        $gateway = strtolower($gateway); // Normalize for case-insensitive matching reliably
        try {
            $paymentService = PaymentManager::resolve($gateway);
            return $paymentService->handleWebhook($request);
        } catch (\Exception $e) {
            \Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 200); // Always return 200 for webhooks
        }
    }
}
