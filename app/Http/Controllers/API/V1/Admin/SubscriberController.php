<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Models\Subscriber;
use App\Models\Campaign;
use App\Mail\NewsletterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

/**
 * @group Admin
 * @subgroup Newsletter Management
 */
class SubscriberController extends BaseController
{
    /**
     * List all subscribers.
     */
    public function index()
    {
        $subscribers = Subscriber::latest()->get();
        return $this->successResponse($subscribers, 'Subscribers retrieved successfully');
    }

    /**
     * Delete a subscriber.
     */
    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();
        return $this->successResponse(null, 'Subscriber deleted successfully');
    }

    /**
     * Send newsletter email to all or selected subscribers.
     */
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'subscriber_ids' => 'nullable|array',
            'subscriber_ids.*' => 'exists:subscribers,id',
            'send_to_all' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        $query = Subscriber::where('is_active', true);

        if (!$request->send_to_all && $request->has('subscriber_ids')) {
            $query->whereIn('id', $request->subscriber_ids);
        }

        $subscribers = $query->get();

        if ($subscribers->isEmpty()) {
            return $this->errorResponse('No active subscribers found to send email', 404);
        }

        // Create campaign record
        $campaign = Campaign::create([
            'subject' => $request->subject,
            'content' => $request->content,
            'sent_at' => now(),
        ]);

        foreach ($subscribers as $subscriber) {
            $unsubscribeUrl = URL::signedRoute('newsletter.unsubscribe', ['email' => $subscriber->email]);
            
            // Queue email
            Mail::to($subscriber->email)->queue(new NewsletterMail($request->subject, $request->content, $unsubscribeUrl));

            // Track campaign delivery
            $campaign->subscribers()->attach($subscriber->id, [
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        // Log broadcast activity
        activity()
            ->performedOn($campaign)
            ->causedBy(auth()->user())
            ->withProperties(['subscriber_count' => $subscribers->count()])
            ->log('Newsletter campaign "' . $campaign->subject . '" broadcasted to ' . $subscribers->count() . ' subscribers');

        return $this->successResponse([
            'campaign_id' => $campaign->id,
            'subscriber_count' => $subscribers->count()
        ], 'Newsletter emails queued and campaign tracked successfully');
    }
}
