<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\AddToCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Cart
 */
class CartController extends BaseController
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    protected function resolveCart(Request $request)
    {
        $userId = auth()->id();
        $sessionId = $request->header('X-Cart-Session');
        
        return $this->cartService->resolveCartTokens($userId, $sessionId);
    }

    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->load(['items.product', 'items.productVariant', 'coupon']);
        
        return $this->successResponse(new \App\Http\Resources\CartResource($cart), 'Cart fetched successfully');
    }

    public function add(AddToCartRequest $request)
    {
        $cart = $this->resolveCart($request);
        $this->cartService->addItem($cart, $request->validated());

        $cart->load(['items.product', 'items.productVariant', 'coupon']);

        return $this->successResponse(new \App\Http\Resources\CartResource($cart), 'Item added to cart', 201);
    }

    public function remove(Request $request, $itemId)
    {
        $cart = $this->resolveCart($request);
        $this->cartService->removeItem($cart->id, $itemId);

        $cart->load(['items.product', 'items.productVariant', 'coupon']);
        
        return $this->successResponse(new \App\Http\Resources\CartResource($cart), 'Item removed specifically successfully');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $cart = $this->resolveCart($request);
        $cart->load('items.product', 'items.productVariant');
        
        $tempResource = new \App\Http\Resources\CartResource($cart);
        // Force the resource to map to array to manually fetch the clean generic subtotal smoothly easily smartly explicitly
        $requestTemp = request();
        $subtotal = $tempResource->toArray($requestTemp)['subtotal'];

        /** @var \App\Services\CouponService $couponService */
        $couponService = app(\App\Services\CouponService::class);
        $coupon = $couponService->getCouponByCode($request->code);

        $validation = $couponService->validateForCart($coupon, $subtotal);
        if (!$validation['valid']) {
            return $this->errorResponse($validation['message'], 400);
        }

        $cart->update(['coupon_id' => $coupon->id]);
        $cart->load('coupon');

        return $this->successResponse(new \App\Http\Resources\CartResource($cart), 'Coupon applied successfully');
    }

    public function removeCoupon(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->update(['coupon_id' => null]);
        
        $cart->load(['items.product', 'items.productVariant']);
        return $this->successResponse(new \App\Http\Resources\CartResource($cart), 'Coupon removed securely successfully');
    }
}
