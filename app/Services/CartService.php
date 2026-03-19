<?php

namespace App\Services;

use App\Repositories\CartRepositoryInterface;
use App\Models\CartItem;
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
            $cart = $this->cartRepository->model->firstOrCreate(['user_id' => $userId]);
            if ($sessionId) {
                // Potential logic to merge session cart here if required globally mapped transparently natively
            }
            return $cart;
        }

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
        }

        return $this->cartRepository->model->firstOrCreate(['session_id' => $sessionId]);
    }

    public function addItem($cart, array $data)
    {
        $item = $cart->items()->where('product_id', $data['product_id'])
                             ->where('product_variant_id', $data['product_variant_id'] ?? null)
                             ->first();
        if ($item) {
            $item->increment('quantity', $data['quantity'] ?? 1);
            return $item;
        }

        return $cart->items()->create([
            'product_id' => $data['product_id'],
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'quantity' => $data['quantity'] ?? 1
        ]);
    }

    public function removeItem($cartId, $itemId)
    {
        return CartItem::where('cart_id', $cartId)->where('id', $itemId)->delete();
    }
}
