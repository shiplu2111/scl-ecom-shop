<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * @group User
 * @subgroup Dashboard
 */
class DashboardController extends Controller
{
    /**
     * Get customer dashboard statistics.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_orders'     => Order::where('user_id', $user->id)->count(),
            'wishlist_count'   => Wishlist::where('user_id', $user->id)->count(),
            'open_tickets'     => Ticket::where('user_id', $user->id)->whereIn('status', ['open', 'pending'])->count(),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Dashboard stats fetched successfully.',
            'data' => $stats
        ]);
    }

    /**
     * Get recent activity for the user.
     */
    public function activity(Request $request)
    {
        $user = $request->user();

        // Latest orders
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'grand_total', 'order_status', 'created_at']);

        // Latest tickets
        $recentTickets = Ticket::where('user_id', $user->id)
            ->latest('updated_at')
            ->take(5)
            ->get(['id', 'ticket_number', 'subject', 'status', 'priority', 'last_reply_at', 'updated_at']);

        return response()->json([
            'status' => true,
            'message' => 'Recent activity fetched successfully.',
            'data' => [
                'recent_orders'  => $recentOrders,
                'recent_tickets' => $recentTickets
            ]
        ]);
    }
}
