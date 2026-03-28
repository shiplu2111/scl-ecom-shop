<?php

namespace App\Services;

use App\Repositories\OrderRepositoryInterface;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function checkout(Cart $cart, array $data)
    {
        return DB::transaction(function() use ($cart, $data) {
            // Re-calculate cart totals

            $subtotal = 0;
            foreach ($cart->items as $item) {
                $price = $item->productVariant ? $item->productVariant->price : $item->product->price;
                $subtotal += ($price * $item->quantity);
            }

            $discountAmount = 0;
            if ($cart->coupon) {
                if ($cart->coupon->type === 'percentage') {
                    $discountAmount = $subtotal * ($cart->coupon->value / 100);
                } elseif ($cart->coupon->type === 'fixed') {
                    $discountAmount = $cart->coupon->value;
                }
                $cart->coupon->increment('used_count');
            }
            $discountAmount = min($subtotal, $discountAmount);

            // Map Address constraints securely

            $address = \App\Models\UserAddress::findOrFail($data['shipping_address_id']);
            $district = \App\Models\District::findOrFail($address->district_id);
            $deliveryCharge = (float) $district->delivery_charge;

            // Handle free delivery coupon
            $isFreeDelivery = ($deliveryCharge == 0);
            if ($cart->coupon && $cart->coupon->type === 'free_delivery') {
                $isFreeDelivery = true;
                $deliveryCharge = 0; // Explicitly set to 0 if coupon is free_delivery
            }

            $grandTotal = $subtotal - $discountAmount + $deliveryCharge;

            $orderNumber = 'ORD-' . strtoupper(Str::random(10));
            
            // Logic for COD Order Confirmation
            // If delivery_charge > 0 and no free delivery -> order is pending
            // If free delivery -> order is confirmed
            $orderStatus = 'pending';
            $paymentStatus = 'pending';
            $deliveryChargePaid = false;

            if ($data['payment_method'] === 'cod') {
                if ($isFreeDelivery) {
                    $orderStatus = 'confirmed';
                    $paymentStatus = 'free'; 
                    $deliveryChargePaid = true;
                }
            } else {
                // For online payment, everything starts as pending until payment callback
                $paymentStatus = 'pending';
            }

            $order = $this->orderRepository->create([
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_charge' => $deliveryCharge,
                'grand_total' => $grandTotal,
                'payment_method' => $data['payment_method'],
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'shipping_address_id' => $data['shipping_address_id'],
                'billing_address_id' => $data['billing_address_id'] ?? null,
                'coupon_id' => $cart->coupon_id,
                'delivery_charge_paid' => $deliveryChargePaid,
                'paid_amount' => 0,
                'due_amount' => $grandTotal
            ]);

            foreach ($cart->items as $item) {
                $price = $item->productVariant ? $item->productVariant->price : $item->product->price;
                $buyingPrice = $item->productVariant ? $item->productVariant->buying_price : $item->product->buying_price;
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $price,
                    'buying_price' => $buyingPrice,
                    'total_price' => $price * $item->quantity
                ]);
            }

            $cart->delete();

            return $order;
        });
    }

    public function updateStatus($orderId, $status)
    {
        $order = $this->orderRepository->find($orderId);
        $order->update(['order_status' => $status]);
        return $order;
    }

    public function getUserOrders($userId)
    {
        return $this->orderRepository->getModel()->where('user_id', $userId)
            ->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon'])
            ->orderBy('id', 'desc')->get();
    }

    public function findUserOrder($userId, $orderId)
    {
        return $this->orderRepository->getModel()->where('user_id', $userId)
            ->where('id', $orderId)
            ->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user'])
            ->first();
    }
    public function fetchAllOrders()
    {
        return $this->orderRepository->getModel()->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function findWithDetails($orderId)
    {
        return $this->orderRepository->getModel()->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user'])
            ->find($orderId);
    }

    public function find($id)
    {
        return $this->orderRepository->find($id);
    }
}
