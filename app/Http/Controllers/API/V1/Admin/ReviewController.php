<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\ProductReview;
use App\Events\ReviewUpdated;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    /**
     * List all reviews for management flawlessly properly
     */
    public function index(Request $request)
    {
        $reviews = ProductReview::with(['product:id,name,sku', 'user:id,name,email'])
            ->when($request->search, function ($query, $search) {
                $query->where('comment', 'LIKE', "%$search%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    });
            })
            ->latest()
            ->paginate($request->get('limit', 15));

        return $this->successResponse($reviews, 'All reviews fetched flawlessly properly.');
    }

    /**
     * Toggle the published status of a review brilliance flawlessly
     */
    public function toggleStatus(int $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->is_published = !$review->is_published;
        $review->save();

        // Broadcast the update for real-time storefront sync flawlessly properly
        event(new ReviewUpdated($review->product));

        return $this->successResponse($review, 'Review status updated flawlessly properly.');
    }

    /**
     * Delete a review flawlessly properly
     */
    public function destroy(int $id)
    {
        $product = $review->product;
        $review->delete();

        // Broadcast the update for real-time storefront sync flawlessly properly
        event(new ReviewUpdated($product));

        return $this->successResponse(null, 'Review deleted flawlessly properly.');
    }
}
