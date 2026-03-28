<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Order Management
 */
class AdminOrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->fetchAllOrders();
        return $this->successResponse(OrderResource::collection($orders), 'Orders fetched successfully');
    }

    public function show($id)
    {
        $order = $this->orderService->findWithDetails($id);
        if (!$order) return $this->errorResponse('Order not found', 404);

        return $this->successResponse(new OrderResource($order), 'Order fetched successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
        ]);

        $order = $this->orderService->find($id);
        if (!$order) return $this->errorResponse('Order not found', 404);

        $order = $this->orderService->updateStatus($id, $request->status);
        $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user']);
        
        return $this->successResponse(new OrderResource($order), 'Order status updated successfully');
    }
}
