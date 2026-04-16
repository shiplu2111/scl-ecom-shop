<?php

namespace App\Http\Controllers\API\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CourierCredential;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SteadfastWebhookController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handle incoming Steadfast webhook notifications.
     *
     * Steadfast sends two types:
     *   - delivery_status: status update for a consignment
     *   - tracking_update: tracking message update only
     */
    public function handle(Request $request)
    {
        // ── 1. Verify Bearer Token (Now from DB) ──────────────────────────
        $credential = CourierCredential::where('name', 'steadfast')->first();
        $expectedToken = $credential?->webhook_token;

        if ($expectedToken) {
            $authHeader = $request->header('Authorization', '');
            $providedToken = str_replace('Bearer ', '', $authHeader);

            if ($providedToken !== $expectedToken) {
                Log::warning('Steadfast Webhook: Invalid auth token.', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized.',
                ], 403);
            }
        }

        // ── 2. Parse Payload ────────────────────────────────────────────────
        $payload          = $request->all();
        $notificationType = $payload['notification_type'] ?? null;
        $invoice          = $payload['invoice'] ?? null;
        $consignmentId    = $payload['consignment_id'] ?? null;

        Log::info('Steadfast Webhook Received', ['payload' => $payload]);

        // ── 3. Find the Order ───────────────────────────────────────────────
        if (!$invoice) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing invoice number.',
            ], 400);
        }

        $order = Order::where('order_number', $invoice)->first();

        if (!$order) {
            Log::error('Steadfast Webhook: Order not found.', ['invoice' => $invoice]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid consignment ID.',
            ], 404);
        }

        // ── 4. Handle by Notification Type ─────────────────────────────────
        if ($notificationType === 'delivery_status') {
            $this->handleDeliveryStatus($order, $payload);
        } elseif ($notificationType === 'tracking_update') {
            $this->handleTrackingUpdate($order, $payload);
        } else {
            Log::warning('Steadfast Webhook: Unknown notification type.', [
                'notification_type' => $notificationType,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Webhook received successfully.',
        ], 200);
    }

    /**
     * Handle "delivery_status" webhook: update order status and log history.
     */
    protected function handleDeliveryStatus(Order $order, array $payload): void
    {
        $steadfastStatus  = strtolower($payload['status'] ?? '');
        $trackingMessage  = $payload['tracking_message'] ?? null;
        $consignmentId    = $payload['consignment_id'] ?? null;
        $updatedAt        = $payload['updated_at'] ?? null;

        // Map Steadfast status → our system order_status
        $statusMap = [
            'delivered'         => 'delivered',
            'partial_delivered' => 'delivered',
            'cancelled'         => 'cancelled',
        ];

        $newStatus = $statusMap[$steadfastStatus] ?? null;

        // Update consignment_id on the order if not already set
        if ($consignmentId && !$order->consignment_id) {
            $order->update(['consignment_id' => $consignmentId]);
        }

        // Update order status only for actionable statuses
        if ($newStatus && $order->order_status !== $newStatus) {
            // updateStatus() handles: COD transactions, stock reduction/restoration, history log
            $this->orderService->updateStatus($order->id, $newStatus);
        }

        // Always log the webhook event in order history
        $this->orderService->logHistory(
            $order,
            'steadfast_webhook',
            "Steadfast delivery update: {$payload['status']}" . ($trackingMessage ? " — {$trackingMessage}" : ''),
            [
                'notification_type' => 'delivery_status',
                'steadfast_status'  => $payload['status'] ?? null,
                'consignment_id'    => $consignmentId,
                'tracking_message'  => $trackingMessage,
                'updated_at'        => $updatedAt,
                'cod_amount'        => $payload['cod_amount'] ?? null,
                'delivery_charge'   => $payload['delivery_charge'] ?? null,
            ]
        );
    }

    /**
     * Handle "tracking_update" webhook: log tracking message only.
     */
    protected function handleTrackingUpdate(Order $order, array $payload): void
    {
        $trackingMessage = $payload['tracking_message'] ?? null;
        $consignmentId   = $payload['consignment_id'] ?? null;
        $updatedAt       = $payload['updated_at'] ?? null;

        $this->orderService->logHistory(
            $order,
            'steadfast_tracking',
            $trackingMessage ?? 'Tracking update received from Steadfast.',
            [
                'notification_type' => 'tracking_update',
                'consignment_id'    => $consignmentId,
                'tracking_message'  => $trackingMessage,
                'updated_at'        => $updatedAt,
            ]
        );
    }
}
