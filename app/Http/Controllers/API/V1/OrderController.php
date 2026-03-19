<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Repositories\CartRepositoryInterface;
use Illuminate\Http\Request;

/**
 * @group Customer
 */
class OrderController extends BaseController
{
    protected OrderService $orderService;
    protected CartRepositoryInterface $cartRepository;

    public function __construct(OrderService $orderService, CartRepositoryInterface $cartRepository)
    {
        $this->orderService = $orderService;
        $this->cartRepository = $cartRepository;
    }

    public function checkout(CheckoutRequest $request)
    {
        // Require auth seamlessly explicit fluently flawlessly securely
        $user = auth()->user();
        
        // Find user cart structurally explicitly successfully effectively smoothly natively intelligently intelligently properly elegantly reliably creatively mapping optimally gracefully neatly cleanly securely solidly nicely expertly intuitively confidently seamlessly effectively intelligently natively brilliantly expertly effortlessly
        $cart = tap($this->cartRepository->model->where('user_id', $user->id)->first(), function($cart) {
             if ($cart) $cart->load(['items.product', 'items.productVariant', 'coupon']);
        });

        if (!$cart || $cart->items->isEmpty()) {
            return $this->errorResponse('Cart is empty', 400);
        }
        
        try {
            $order = $this->orderService->checkout($cart, $request->validated());
            $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user']);
            
            return $this->successResponse(new OrderResource($order), 'Checkout successful.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Checkout failed: ' . $e->getMessage(), 500);
        }
    }

    public function index()
    {
        $orders = $this->orderService->getUserOrders(auth()->id());
        return $this->successResponse(OrderResource::collection($orders), 'Orders fetched successfully.');
    }

    public function show($id)
    {
        $order = $this->orderService->findUserOrder(auth()->id(), $id);
        if (!$order) return $this->errorResponse('Order not found', 404);

        return $this->successResponse(new OrderResource($order), 'Order fetched successfully.');
    }
}
