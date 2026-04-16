<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Courier\CourierManager;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Courier
 */
class CourierController extends Controller
{
    protected CourierManager $courierManager;
    protected OrderService $orderService;

    public function __construct(CourierManager $courierManager, OrderService $orderService)
    {
        $this->courierManager = $courierManager;
        $this->orderService = $orderService;
    }

    /**
     * Dispatch an order to a courier.
     */
    public function dispatchOrder(Request $request, Order $order)
    {
        $request->validate([
            'courier' => 'required|string|in:steadfast,pathao',
        ]);

        try {
            $gateway = $this->courierManager->driver($request->courier);
            $response = $gateway->createDelivery($order);

            if ($response['success']) {
                $order->update([
                    'courier_name' => $request->courier,
                    'consignment_id' => $response['consignment_id'],
                    'tracking_number' => $response['tracking_code'],
                    'order_status' => 'shipped',
                ]);

                $this->orderService->logHistory($order, 'dispatched', "Order dispatched to {$request->courier}", [
                    'courier' => $request->courier,
                    'consignment_id' => $response['consignment_id'],
                    'tracking_code' => $response['tracking_code'],
                ]);

                return response()->json([
                    'message' => 'Order successfully dispatched to courier.',
                    'tracking_info' => $response
                ]);
            }

            return response()->json(['message' => $response['message'] ?? 'Failed to dispatch order.'], 400);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Track an existing dispatched order.
     */
    public function trackOrder(Order $order)
    {
        if (!$order->courier_name || !$order->consignment_id) {
            return response()->json(['message' => 'This order has not been dispatched yet.'], 400);
        }

        try {
            $gateway = $this->courierManager->driver($order->courier_name);
            $response = $gateway->trackOrder($order->consignment_id);

            return response()->json([
                'tracking_status' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get steadfast courier balance.
     */
    public function getBalance()
    {
        try {
            $gateway = $this->courierManager->driver('steadfast');
            $response = $gateway->getBalance();

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create return request for an order.
     */
    public function createReturn(Order $order, Request $request)
    {
        if (!$order->courier_name || !$order->consignment_id) {
            return response()->json(['message' => 'This order has not been dispatched yet.'], 400);
        }

        try {
            $gateway = $this->courierManager->driver($order->courier_name);
            $response = $gateway->createReturnRequest($order->consignment_id, $request->reason);

            if ($response['success']) {
                $this->orderService->logHistory($order, 'return_requested', "Return request created: " . ($request->reason ?? 'No reason provided'));
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
