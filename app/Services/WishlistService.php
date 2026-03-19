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

    public function toggleWishlist(int $userId, int $productId)
    {
        $exists = $this->wishlistRepository->model->where('user_id', $userId)
                                                  ->where('product_id', $productId)
                                                  ->first();

        if ($exists) {
            $exists->delete();
            return ['status' => 'removed'];
        }

        $this->wishlistRepository->create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        
        return ['status' => 'added'];
    }
}
