<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch admin notifications (paginated)
     */
    public function index(Request $request)
    {
        $admin = $request->user('admin');
        
        $notifications = $admin->notifications()->latest()->paginate(15);
        
        return response()->json([
            'status' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $admin->unreadNotifications()->count()
            ]
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user('admin')->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user('admin')->unreadNotifications->markAsRead();
        
        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
}
