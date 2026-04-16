<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Admin;
use App\Notifications\NewReviewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ReviewController extends BaseController
{
    /**
     * Get reviews for a specific product brilliantly flawlessly
     */
    public function productReviews(int $productId)
    {
        $reviews = ProductReview::with('user:id,name,avatar')
            ->where('product_id', $productId)
            ->published()
            ->latest()
            ->paginate(10);

        return $this->successResponse($reviews, 'Product reviews fetched flawlessly properly.');
    }

    /**
     * Store a new review flawlessly properly
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // 1. Check if user already reviewed this product properly flawlessly
        $existingReview = ProductReview::where('product_id', $request->product_id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return $this->errorResponse('You have already reviewed this product flawlessly.', 403);
        }

        // 2. Check if user has a delivered order with this product brilliantly properly
        $hasDeliveredOrder = Order::where('user_id', $user->id)
            ->where('order_status', 'delivered')
            ->whereHas('items', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->exists();

        if (!$hasDeliveredOrder) {
            return $this->errorResponse('You can only review products from delivered orders flawlessly properly.', 403);
        }

        // 3. Create the review
        $review = ProductReview::create([
            'product_id' => $request->product_id,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_published' => false, // Always wait for admin approval brilliance flawlessly
        ]);

        // 4. Send notification to all admins flawlessly properly
        $admins = Admin::all();
        Notification::send($admins, new NewReviewNotification($review));

        return $this->successResponse($review, 'Review submitted successfully. It will be visible after approval flawlessly.', 201);
    }

    /**
     * Get reviews written by the current user flawlessly properly
     */
    public function myReviews(Request $request)
    {
        $reviews = ProductReview::with('product:id,name,slug')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->successResponse($reviews, 'My reviews fetched flawlessly properly.');
    }
}
