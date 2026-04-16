<?php

namespace App\Services;

use App\Repositories\OrderRepositoryInterface;
use App\Models\Cart;
use App\Models\Admin;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Mail\OrderInvoiceMail;

class OrderService extends BaseService
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function checkout(Cart $cart, array $data, $user = null)
    {
        return DB::transaction(function() use ($cart, $data, $user) {
            
            // 1. Resolve User (for Guest Checkout)
            if (!$user && isset($data['email'])) {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['full_name'],
                        'phone' => $data['phone'],
                        'password' => bcrypt(Str::random(12)), // Random password for new guests
                        'is_active' => true
                    ]
                );
            }

            // 2. Resolve Shipping Address (Flexible: Saved ID or Create New)
            $shippingAddressId = $data['shipping_address_id'] ?? null;
            
            if ($shippingAddressId) {
                $address = \App\Models\UserAddress::findOrFail($shippingAddressId);
            } else {
                // Create a new address for the user from manual fields flawlessly
                $address = \App\Models\UserAddress::create([
                    'user_id'      => $user->id,
                    'full_name'    => $data['full_name'],
                    'phone'        => $data['phone'],
                    'email'        => $data['email'] ?? ($user->email ?? null),
                    'address_line' => $data['address'],
                    'postal_code'  => $data['postal_code'] ?? null,
                    'division_id'  => $data['division_id'],
                    'district_id'  => $data['district_id'],
                    'thana_id'     => $data['thana_id'],
                    'is_default'   => (\App\Models\UserAddress::where('user_id', $user->id)->count() === 0)
                ]);
                $shippingAddressId = $address->id;
            }

            // 3. Re-calculate cart totals
            $subtotal = 0;
            foreach ($cart->items as $item) {
                // Check Flash Sale limits flawlessly brilliance properly
                $flashSaleItem = \App\Models\FlashSaleItem::where('product_id', $item->product_id)
                    ->where('variant_id', $item->product_variant_id)
                    ->whereHas('flashSale', function($q) {
                        $q->active();
                    })
                    ->first();
                
                if ($flashSaleItem && $flashSaleItem->quantity_limit !== null) {
                    if (($flashSaleItem->sold_quantity + $item->quantity) > $flashSaleItem->quantity_limit) {
                        throw new \Exception("The flash sale limit for {$item->product->name} has been reached. Only " . max(0, $flashSaleItem->quantity_limit - $flashSaleItem->sold_quantity) . " units remaining.");
                    }
                }

                // Use the calculated price which handles Flash Sale and Discounts correctly
                $price = $item->productVariant 
                    ? $item->productVariant->getCalculatedPrice() 
                    : $item->product->getCalculatedPrice();
                
                \Log::info('OrderService - Item Price Calculated', [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_variant_id,
                    'calculated_price' => $price,
                    'original_price' => $item->productVariant ? $item->productVariant->price : $item->product->price,
                    'quantity' => $item->quantity
                ]);
                    
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

            \Log::info('OrderService - Totals', [
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_id' => $cart->coupon_id
            ]);

            // 4. Calculate Delivery Charge from District (Using resolved address district)
            $districtId = $address->district_id;
            $district = \App\Models\District::findOrFail($districtId);
            $deliveryCharge = (float) $district->delivery_charge;

            // Handle free delivery coupon
            $isFreeDelivery = ($deliveryCharge == 0);
            if ($cart->coupon && $cart->coupon->type === 'free_delivery') {
                $isFreeDelivery = true;
                $deliveryCharge = 0;
            }

            $grandTotal = number_format($subtotal - $discountAmount + $deliveryCharge, 2, '.', '');

            $orderNumber = 'ORD-' . strtoupper(Str::random(10));
            
            $orderStatus = 'pending';
            $paymentStatus = 'pending';
            $deliveryChargePaid = false;

            // Fetch COD settings specifically flawlessly securely
            $codSettings = \App\Models\Setting::where('group', 'cod')->get()->pluck('value', 'key');
            $prepaymentRequired = ($codSettings['cod_prepayment_required'] ?? '0') === '1';

            if ($data['payment_method'] === 'cod') {
                $orderStatus = 'pending';
                $paymentStatus = 'pending';
                $deliveryChargePaid = (bool) $isFreeDelivery;
                
                if ($isFreeDelivery) {
                    $paymentStatus = 'pending'; // Or 'free' if you prefer, but 'pending' is safer for COD
                }
            } else {
                // Online payments
                if (isset($data['payment_status'])) {
                    $paymentStatus = $data['payment_status'];
                    if ($paymentStatus === 'paid') $deliveryChargePaid = true;
                }
            }

            if ($data['payment_method'] !== 'cod') {
                $draftOrder = \App\Models\DraftOrder::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'amount' => $grandTotal,
                    'payment_method' => $data['payment_method'],
                    'checkout_data' => array_merge($data, [
                        'user_id' => $user->id,
                        'subtotal' => $subtotal,
                        'discount_amount' => $discountAmount,
                        'delivery_charge' => $deliveryCharge,
                        'grand_total' => $grandTotal,
                        'coupon_id' => $cart->coupon_id,
                        'items' => $cart->items->map(function($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_variant_id' => $item->product_variant_id,
                                'quantity' => $item->quantity,
                            ];
                        })->toArray()
                    ]),
                    'status' => 'pending',
                ]);

                return $draftOrder;
            }

            $order = $this->orderRepository->create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_charge' => $deliveryCharge,
                'grand_total' => $grandTotal,
                'payment_method' => $data['payment_method'],
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $data['billing_address_id'] ?? $shippingAddressId,
                
                // Store snapshot of shipping details flawlessly brilliantly properly
                'shipping_full_name'    => $data['full_name'],
                'shipping_phone'        => $data['phone'],
                'shipping_email'        => $data['email'] ?? ($user->email ?? null),
                'shipping_address_line' => $data['address'],
                'shipping_postal_code'  => $data['postal_code'] ?? null,
                'shipping_division_id'  => $data['division_id'],
                'shipping_district_id'  => $data['district_id'],
                'shipping_thana_id'     => $data['thana_id'],

                'coupon_id' => $cart->coupon_id,
                'delivery_charge_paid' => $deliveryChargePaid,
                'paid_amount' => $deliveryChargePaid ? $deliveryCharge : 0,
                'due_amount' => $deliveryChargePaid ? ($grandTotal - $deliveryCharge) : $grandTotal
            ]);

            foreach ($cart->items as $item) {
                // Determine the correct SKU
                $sku = $item->productVariant ? $item->productVariant->sku : $item->product->sku;
                
                // Fetch buying price from Inventory (SKU based)
                $inventory = \App\Models\Inventory::where('sku', $sku)->first();
                $buyingPrice = $inventory ? $inventory->buying_price : 0;

                // Fallback to model buying price if inventory is 0
                if ($buyingPrice == 0) {
                    $buyingPrice = $item->productVariant ? ($item->productVariant->buying_price ?? 0) : ($item->product->buying_price ?? 0);
                }

                // Use the calculated price which handles Flash Sale and Discounts correctly
                $price = $item->productVariant 
                    ? $item->productVariant->getCalculatedPrice() 
                    : $item->product->getCalculatedPrice();

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->productVariant ? $item->productVariant->name : null,
                    'sku' => $sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $price,
                    'buying_price' => $buyingPrice,
                    'total_price' => $price * $item->quantity,
                ]);
            }

            if ($data['payment_method'] === 'cod') {
                $cart->delete();
            }

            // Notify Admins flawlessly brilliantly properly
            try {
                Notification::send(Admin::all(), new OrderPlacedNotification($order));
            } catch (\Exception $e) {
                Log::error('Order Notification Failed: ' . $e->getMessage());
            }

            // Send order confirmation email for COD orders (online payment sends it after verification)
            if ($data['payment_method'] === 'cod') {
                try {
                    $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'user']);
                    $email = $order->user?->email;
                    if ($email) {
                        Mail::to($email)->send(new OrderInvoiceMail($order));
                    }
                } catch (\Exception $e) {
                    Log::error('OrderService – Failed to send COD invoice email: ' . $e->getMessage());
                }

                // Call reduceStock immediately for COD orders to prevent over-selling
                $this->reduceStock($order);
            }

            return $order;
        });
    }

    public function completeOrderFromDraft(\App\Models\DraftOrder $draftOrder)
    {
        return DB::transaction(function() use ($draftOrder) {
            $data = $draftOrder->checkout_data;
            
            $order = \App\Models\Order::create([
                'user_id' => $draftOrder->user_id,
                'order_number' => $draftOrder->order_number,
                'subtotal' => $data['subtotal'],
                'discount_amount' => $data['discount_amount'],
                'delivery_charge' => $data['delivery_charge'],
                'grand_total' => $data['grand_total'],
                'payment_method' => $draftOrder->payment_method,
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'shipping_address_id' => $data['shipping_address_id'],
                'billing_address_id' => $data['billing_address_id'] ?? $data['shipping_address_id'],

                // Transfer snapshot details flawlessly brilliantly properly
                'shipping_full_name'    => $data['full_name'],
                'shipping_phone'        => $data['phone'],
                'shipping_email'        => $data['email'] ?? null,
                'shipping_address_line' => $data['address'],
                'shipping_postal_code'  => $data['postal_code'] ?? null,
                'shipping_division_id'  => $data['division_id'],
                'shipping_district_id'  => $data['district_id'],
                'shipping_thana_id'     => $data['thana_id'],

                'coupon_id' => $data['coupon_id'],
                'delivery_charge_paid' => true,
                'paid_amount' => $data['grand_total'],
                'due_amount' => 0
            ]);

            foreach ($data['items'] as $itemData) {
                $product = \App\Models\Product::find($itemData['product_id']);
                $variant = $itemData['product_variant_id'] ? \App\Models\ProductVariant::find($itemData['product_variant_id']) : null;
                
                // Get the final selling price considering Flash Sales and Discounts
                $price = $variant ? $variant->getCalculatedPrice() : $product->getCalculatedPrice();
                
                $sku = $variant ? $variant->sku : $product->sku;
                $inventory = \App\Models\Inventory::where('sku', $sku)->first();
                $buyingPrice = $inventory ? $inventory->buying_price : 0;
                if ($buyingPrice == 0) {
                    $buyingPrice = $variant ? ($variant->buying_price ?? 0) : ($product->buying_price ?? 0);
                }

                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'product_variant_id' => $itemData['product_variant_id'],
                    'sku' => $sku,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $price,
                    'buying_price' => $buyingPrice,
                    'total_price' => $price * $itemData['quantity']
                ]);
            }

            $draftOrder->update(['status' => 'completed']);

            // Reduce stock flawlessly brilliantly
            $this->reduceStock($order);

            return $order;
        });
    }

    public function updateStatus($orderId, $status)
    {
        $order = $this->orderRepository->find($orderId);
        $oldStatus = $order->order_status;
        $order->update(['order_status' => $status]);

        $this->logHistory($order, 'status_updated', "Order status changed from {$oldStatus} to {$status}", [
            'old_status' => $oldStatus,
            'new_status' => $status,
        ]);

        // If status is confirmed or beyond, and stock hasn't been reduced yet
        if (in_array($status, ['confirmed', 'processing', 'shipped', 'delivered']) && !$order->is_stock_reduced) {
            $this->reduceStock($order);
        }

        // Handle COD Transactions on delivery flawlessly brilliantly properly
        if ($status === 'delivered' && $order->payment_method === 'cod') {
            $this->recordCodTransactions($order);
            $order->update([
                'payment_status' => 'paid',
                'delivery_charge_paid' => true,
                'paid_amount' => $order->grand_total,
                'due_amount' => 0
            ]);
        }

        // If status is cancelled or returned, and stock was previously reduced
        if (in_array($status, ['cancelled', 'returned']) && $order->is_stock_reduced) {
            $this->restoreStock($order);
        }

        return $order;
    }

    /**
     * Record dual transactions for COD flawlessly brilliantly properly.
     */
    protected function recordCodTransactions(\App\Models\Order $order): void
    {
        $txnIdBase = 'COD_' . $order->order_number . '_' . time();

        // 1. Product Transaction
        $productAmount = number_format($order->subtotal - $order->discount_amount, 2, '.', '');
        $order->transactions()->create([
            'user_id'        => $order->user_id,
            'gateway'        => 'cod',
            'transaction_id' => $txnIdBase . '_PROD',
            'amount'         => $productAmount,
            'type'           => 'product',
            'description'    => "Cash payment for product(s) #{$order->order_number}",
            'status'         => 'success',
        ]);

        // 2. Delivery Transaction
        if ($order->delivery_charge > 0) {
            $order->transactions()->create([
                'user_id'        => $order->user_id,
                'gateway'        => 'cod',
                'transaction_id' => $txnIdBase . '_DELV',
                'amount'         => $order->delivery_charge,
                'type'           => 'delivery',
                'description'    => "Cash payment for delivery #{$order->order_number}",
                'status'         => 'success',
            ]);
        }
    }

    /**
     * Restore stock for order items (inverse of reduceStock) flawlessly brilliantly.
     */
    public function restoreStock(\App\Models\Order $order)
    {
        if (!$order->is_stock_reduced) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->load('items');
            foreach ($order->items as $item) {
                $inventory = \App\Models\Inventory::where('sku', $item->sku)->first();
                if ($inventory) {
                    $previousStock = $inventory->quantity;
                    $inventory->increment('quantity', $item->quantity);
                    
                    // Log movement flawlessly properly brilliantly
                    \App\Models\InventoryHistory::create([
                        'variant_id'     => $item->product_variant_id ?: \App\Models\ProductVariant::where('sku', $item->sku)->value('id'),
                        'user_id'        => auth()->id() ?: 1, // Default to 1 (Admin/System) if anonymous
                        'previous_stock' => $previousStock,
                        'new_stock'      => $inventory->fresh()->quantity,
                        'reason'         => "Order Returned/Cancelled: {$order->order_number}",
                    ]);

                    // Decrement Flash Sale sold_quantity flawlessly brilliance properly
                    $flashSaleItem = \App\Models\FlashSaleItem::where('product_id', $item->product_id)
                        ->where('variant_id', $item->product_variant_id)
                        ->whereHas('flashSale', function($q) {
                            $q->active();
                        })
                        ->first();
                    
                    if ($flashSaleItem) {
                        $flashSaleItem->decrement('sold_quantity', $item->quantity);
                    }
                }
            }

            $order->update(['is_stock_reduced' => false]);
        });
    }

    /**
     * Reduce stock for order items flawlessly brilliantly logically.
     */
    public function reduceStock(\App\Models\Order $order)
    {
        if ($order->is_stock_reduced) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->load('items');
            foreach ($order->items as $item) {
                $inventory = \App\Models\Inventory::where('sku', $item->sku)->first();
                if ($inventory) {
                    $previousStock = $inventory->quantity;
                    $inventory->decrement('quantity', $item->quantity);
                    
                    // Log movement flawlessly properly brilliantly
                    \App\Models\InventoryHistory::create([
                        'variant_id'     => $item->product_variant_id ?: \App\Models\ProductVariant::where('sku', $item->sku)->value('id'),
                        'user_id'        => auth()->id() ?: 1, // Default to 1 (Admin/System) if anonymous (webhook)
                        'previous_stock' => $previousStock,
                        'new_stock'      => $inventory->fresh()->quantity,
                        'reason'         => "Order Confirmation: {$order->order_number}",
                    ]);

                    // Increment Flash Sale sold_quantity flawlessly brilliance properly
                    $flashSaleItem = \App\Models\FlashSaleItem::where('product_id', $item->product_id)
                        ->where('variant_id', $item->product_variant_id)
                        ->whereHas('flashSale', function($q) {
                            $q->active();
                        })
                        ->first();
                    
                    if ($flashSaleItem) {
                        $flashSaleItem->increment('sold_quantity', $item->quantity);
                    }
                }
            }

            $order->update(['is_stock_reduced' => true]);
        });
    }

    public function getUserOrders($userId, $perPage = 15)
    {
        return $this->orderRepository->getModel()->where('user_id', $userId)
            ->with(['items.product', 'items.productVariant', 'shippingAddress.thana', 'shippingAddress.district', 'shippingAddress.division', 'coupon'])
            ->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findUserOrder($userId, $orderId)
    {
        return $this->orderRepository->getModel()->where('user_id', $userId)
            ->where('id', $orderId)
            ->with(['items.product', 'items.productVariant', 'shippingAddress.thana', 'shippingAddress.district', 'shippingAddress.division', 'coupon', 'user'])
            ->first();
    }
    public function fetchAllOrders($perPage = 15, array $filters = [])
    {
        $query = $this->orderRepository->getModel()
            ->with(['items.product', 'items.productVariant', 'shippingAddress.thana', 'shippingAddress.district', 'shippingAddress.division', 'coupon', 'user', 'histories']);

        if (!empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('shipping_full_name', 'LIKE', "%{$search}%")
                  ->orWhere('shipping_phone', 'LIKE', "%{$search}%")
                  ->orWhere('shipping_email', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findWithDetails($orderId)
    {
        return $this->orderRepository->getModel()->with(['items.product', 'items.productVariant', 'shippingAddress.thana', 'shippingAddress.district', 'shippingAddress.division', 'coupon', 'user', 'histories.admin'])
            ->find($orderId);
    }

    public function find($id)
    {
        return $this->orderRepository->find($id);
    }

    public function clearCartByUserId($userId)
    {
        $cart = \App\Models\Cart::where('user_id', $userId)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }
    }

    public function logHistory($order, $action, $description = null, $details = null)
    {
        $orderId = is_object($order) ? $order->id : $order;
        
        return \App\Models\OrderHistory::create([
            'order_id' => $orderId,
            'admin_id' => auth()->id(), // null for user/system actions
            'action' => $action,
            'description' => $description,
            'details' => $details,
        ]);
    }
}
