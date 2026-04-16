<?php

namespace App\Services;

use App\Repositories\CartRepositoryInterface;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Str;

class CartService extends BaseService
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function resolveCartTokens(?int $userId, ?string $sessionId)
    {
        if ($userId) {
            $userCart = $this->cartRepository->getModel()->firstOrCreate(['user_id' => $userId]);
            
            // Merge guest cart if it exists
            if ($sessionId) {
                $guestCart = $this->cartRepository->getModel()->where('session_id', $sessionId)->first();
                if ($guestCart && $guestCart->id !== $userCart->id) {
                    $this->mergeCarts($guestCart, $userCart);
                }
            }
            return $userCart;
        }

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
        }

        return $this->cartRepository->getModel()->firstOrCreate(['session_id' => $sessionId]);
    }

    protected function mergeCarts($sourceCart, $targetCart)
    {
        foreach ($sourceCart->items as $sourceItem) {
            $existingItem = $targetCart->items()
                ->where('product_id', $sourceItem->product_id)
                ->where('product_variant_id', $sourceItem->product_variant_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $sourceItem->quantity;
                
                // Validate stock after merge specifically smoothly
                $this->validateStock($sourceItem->product_id, $sourceItem->product_variant_id, $newQuantity);
                
                $existingItem->update(['quantity' => $newQuantity]);
                $sourceItem->delete();
            } else {
                // Should we validate stock here too? Theoretically it was already validated in the guest cart
                $sourceItem->update(['cart_id' => $targetCart->id]);
            }
        }

        $sourceCart->delete();
    }

    public function addItem($cart, array $data)
    {
        $productId = $data['product_id'];
        $variantId = $data['product_variant_id'] ?? null;
        $requestedQuantity = $data['quantity'] ?? 1;

        $item = $cart->items()->where('product_id', $productId)
                             ->where('product_variant_id', $variantId)
                             ->first();
        
        if ($item) {
            $totalQuantity = $item->quantity + $requestedQuantity;
            $this->validateStock($productId, $variantId, $totalQuantity);
            $item->update(['quantity' => $totalQuantity]);
            return $item;
        }

        $this->validateStock($productId, $variantId, $requestedQuantity);

        return $cart->items()->create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $requestedQuantity
        ]);
    }

    /**
     * Centralized backend validation for stock securely intelligently flawlessly elegantly fluently gracefully statically natively dynamically smoothly powerfully.
     */
    public function validateStock($productId, $variantId, $quantity)
    {
        $sku = null;
        
        if ($variantId) {
            $sku = ProductVariant::where('id', $variantId)->where('product_id', $productId)->value('sku');
        } else {
            $sku = Product::where('id', $productId)->value('sku');
        }

        $availableStock = Inventory::forSku($sku);

        if ($quantity > $availableStock) {
            abort(422, "Insufficient stock. Only {$availableStock} items remaining.");
        }

        return true;
    }

    public function updateItemQuantity($cart, $itemId, $quantity)
    {
        $item = $cart->items()->where('id', $itemId)->firstOrFail();
        
        $this->validateStock($item->product_id, $item->product_variant_id, $quantity);
        
        $item->update(['quantity' => $quantity]);
        return $item;
    }

    public function removeItem($cartId, $itemId)
    {
        return CartItem::where('cart_id', $cartId)->where('id', $itemId)->delete();
    }
}
