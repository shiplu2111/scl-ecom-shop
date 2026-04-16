<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\WishlistRequest;
use App\Services\WishlistService;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Wishlist
 */
class WishlistController extends BaseController
{
    protected WishlistService $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $sessionId = $request->header('X-Cart-Session');

        $query = \App\Models\Wishlist::with(['product', 'product.images', 'product.variants']);

        if ($userId) {
            $query->where('user_id', $userId);
        } else if ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return $this->successResponse([], 'Wishlist empty (no session or user)');
        }

        $wishlists = $query->get();
        return $this->successResponse($wishlists, 'Wishlist fetched successfully');
    }

    public function toggle(WishlistRequest $request)
    {
        $userId = auth()->id();
        $sessionId = $request->header('X-Cart-Session');

        if (!$userId && !$sessionId) {
            return $this->errorResponse('Authentication or Session required', 400);
        }

        $result = $this->wishlistService->toggleWishlist(
            $userId, 
            $request->product_id, 
            $sessionId,
            $request->product_variant_id
        );
        
        return $this->successResponse($result, 'Wishlist toggled successfully');
    }
}
