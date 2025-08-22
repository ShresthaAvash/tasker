<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display the user's notifications.
     */
    public function index()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()->paginate(15);

        // Mark only the unread notifications that are currently being viewed as read
        $user->unreadNotifications()->whereIn('id', $notifications->pluck('id'))->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read via AJAX.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a specific notification as unread.
     */
    public function markAsUnread(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsUnread();

        $unreadCount = Auth::user()->unreadNotifications()->count();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'unreadCount' => $unreadCount]);
        }

        return redirect()->back()->with('success', 'Notification marked as unread.');
    }
}