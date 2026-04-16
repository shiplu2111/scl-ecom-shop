<?php

namespace App\Services;

use App\Repositories\WishlistRepositoryInterface;

class WishlistService extends BaseService
{
    protected WishlistRepositoryInterface $wishlistRepository;

    public function __construct(WishlistRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function toggleWishlist(?int $userId, int $productId, ?string $sessionId = null, ?int $productVariantId = null)
    {
        $query = $this->wishlistRepository->getModel()->where('product_id', $productId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $exists = $query->first();

        if ($exists) {
            $exists->delete();
            return ['status' => 'removed'];
        }

        $this->wishlistRepository->create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $productId,
            'product_variant_id' => $productVariantId
        ]);
        
        return ['status' => 'added'];
    }
}
