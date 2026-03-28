<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Subscriber;
use App\Mail\SubscriptionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

/**
 * @group Public
 * @subgroup Newsletter
 */
class SubscriberController extends BaseController
{
    /**
     * Subscribe to newsletter.
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $subscriber = Subscriber::updateOrCreate(
            ['email' => $request->email],
            ['is_active' => true]
        );

        // Send confirmation email
        Mail::to($subscriber->email)->queue(new SubscriptionConfirmation($subscriber));

        return $this->successResponse($subscriber, 'Subscribed successfully. Please check your email for confirmation!');
    }

    /**
     * Unsubscribe from newsletter.
     */
    public function unsubscribe(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired unsubscribe link.'], 403);
        }

        $subscriber = Subscriber::where('email', $request->email)->first();

        if ($subscriber) {
            $subscriber->update(['is_active' => false]);
            return response()->view('newsletter.unsubscribed', ['email' => $request->email]);
        }

        return response()->json(['message' => 'Subscriber not found.'], 404);
    }
}
