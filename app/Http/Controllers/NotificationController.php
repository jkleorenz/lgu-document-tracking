<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of user's notifications
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->with('document')
            ->paginate(20);
        
        $unreadCount = Auth::user()->unreadNotificationsCount();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification)
    {
        // Check if notification belongs to current user
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this notification.');
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Get unread notifications count (AJAX)
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotificationsCount(),
        ]);
    }

    /**
     * Get recent notifications (AJAX)
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->with('document')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Auth::user()->unreadNotificationsCount(),
        ]);
    }
}

