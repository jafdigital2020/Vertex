<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        try {
            $user = Auth::user() ?? Auth::guard('global')->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $notification = DatabaseNotification::where('id', $request->input('id'))
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return response()->json(['message' => 'Notification not found'], 404);
            }

            if ($notification->read_at === null) {
                $notification->markAsRead();
            }

            $unreadCount = $user->unreadNotifications->count();

            return response()->json([
                'message' => 'Marked as read',
                'unreadCount' => $unreadCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user() ?? Auth::guard('global')->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $user->unreadNotifications->each->markAsRead();

            return response()->json([
                'message' => 'All notifications marked as read',
                'unreadCount' => 0
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }

}
