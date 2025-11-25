<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Display a listing of user's notifications
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            $notifications = $user->notifications()
                ->with('document')
                ->paginate(20);
            
            $unreadCount = $user->unreadNotificationsCount();
            
            return view('notifications.index', compact('notifications', 'unreadCount'));
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error loading notifications index: ' . $e->getMessage());
            
            // Return view with empty data - create empty paginator
            $emptyNotifications = \Illuminate\Pagination\LengthAwarePaginator::make(
                collect([]),
                0,
                20,
                1
            );
            return view('notifications.index', [
                'notifications' => $emptyNotifications,
                'unreadCount' => 0,
            ]);
        }
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
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'count' => 0,
                ], 200);
            }
            
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'count' => 0,
                ], 200);
            }
            
            $count = 0;
            try {
                // Check if notifications table exists
                if (Schema::hasTable('notifications')) {
                    $count = $user->unreadNotificationsCount();
                } else {
                    Log::warning('Notifications table does not exist');
                    $count = 0;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error in unreadNotificationsCount for user ' . $user->id . ': ' . $e->getMessage());
                $count = 0;
            } catch (\Exception $e) {
                Log::error('Error in unreadNotificationsCount for user ' . $user->id . ': ' . $e->getMessage());
                $count = 0;
            }
            
            return response()->json([
                'count' => (int)$count,
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching unread notifications count: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            // Return safe default value with proper status code
            return response()->json([
                'count' => 0,
            ], 200);
        }
    }

    /**
     * Get recent notifications (AJAX)
     */
    public function recent()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0,
                ]);
            }
            
            $notifications = $user->notifications()
                ->with('document')
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotificationsCount(),
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching recent notifications: ' . $e->getMessage());
            
            // Return safe default values
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }
    }
}

