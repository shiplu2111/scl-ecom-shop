<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
use App\Services\Courier\CourierManager;
use App\Repositories\CartRepositoryInterface;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Orders
 */
class OrderController extends BaseController
{
    protected OrderService $orderService;
    protected CartService $cartService;
    protected CartRepositoryInterface $cartRepository;
    protected CourierManager $courierManager;

    public function __construct(
        OrderService $orderService, 
        CartService $cartService, 
        CartRepositoryInterface $cartRepository,
        CourierManager $courierManager
    ) {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->cartRepository = $cartRepository;
        $this->courierManager = $courierManager;
    }

    public function checkout(CheckoutRequest $request)
    {
        $user = auth()->user();
        $cartSessionId = $request->header('X-Cart-Session');

        // Use CartService to resolve (and auto-merge guest cart if user is logged in)
        $cart = $this->cartService->resolveCartTokens($user?->id, $cartSessionId);
        $cart->load(['items.product.activeFlashSaleItem', 'items.productVariant.activeFlashSaleItem', 'coupon']);

        \Log::info('Checkout Attempt', [
            'user_id'    => $user?->id,
            'session_id' => $cartSessionId,
            'cart_id'    => $cart?->id,
            'item_count' => $cart?->items?->count(),
        ]);

        if (!$cart || $cart->items->isEmpty()) {
            return $this->errorResponse('Your cart is empty. Please add items before checking out.', 400);
        }

        try {
            $order = $this->orderService->checkout($cart, $request->validated(), $user);
            
            // DraftOrder doesn't have the same relationships as Order (items, shippingAddress, etc.)
            if ($order instanceof \App\Models\DraftOrder) {
                $order->load(['user']);
            } else {
                $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user']);
            }

            return $this->successResponse(new OrderResource($order), 'Checkout initiated.', 201);
        } catch (\Exception $e) {
            \Log::error('Checkout failed: ' . $e->getMessage());
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->getUserOrders(auth()->id(), $request->input('per_page', 15));
        return $this->successResponse(OrderResource::collection($orders), 'Orders fetched successfully.');
    }

    public function show($id)
    {
        $order = $this->orderService->findUserOrder(auth()->id(), $id);
        if (!$order) return $this->errorResponse('Order not found', 404);

        $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'histories', 'coupon', 'user']);

        return $this->successResponse(new OrderResource($order), 'Order fetched successfully.');
    }

    /**
     * Track order via courier API
     */
    public function track($id)
    {
        $order = $this->orderService->findUserOrder(auth()->id(), $id);
        
        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        if (!$order->consignment_id || !$order->courier_name) {
            return $this->errorResponse('Tracking information not available for this order yet.', 404);
        }

        try {
            $gateway = $this->courierManager->driver($order->courier_name);
            $tracking = $gateway->trackOrder($order->consignment_id);

            if ($tracking['success']) {
                return $this->successResponse($tracking, 'Tracking status retrieved.');
            }

            return $this->errorResponse($tracking['message'], 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Courier tracking is currently unavailable.', 500);
        }
    }
}
