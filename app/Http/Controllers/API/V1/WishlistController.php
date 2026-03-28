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

    public function index()
    {
        $wishlists = auth()->user()->wishlists()->with('product')->get();
        return $this->successResponse($wishlists, 'Wishlist fetched securely successfully');
    }

    public function toggle(WishlistRequest $request)
    {
        $result = $this->wishlistService->toggleWishlist(auth()->id(), $request->product_id);
        
        return $this->successResponse($result, 'Wishlist toggled organically');
    }
}
