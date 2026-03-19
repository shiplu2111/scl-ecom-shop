<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Courier\CourierManager;
use Illuminate\Http\Request;

/**
 * @group Admin
 */
class CourierController extends Controller
{
    protected CourierManager $courierManager;

    public function __construct(CourierManager $courierManager)
    {
        $this->courierManager = $courierManager;
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

                return response()->json([
                    'message' => 'Order successfully dispatched to courier.',
                    'tracking_info' => $response
                ]);
            }

            return response()->json(['message' => 'Failed to dispatch order.'], 400);

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
}
