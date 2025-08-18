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
        
        // Get all notifications, paginated
        $notifications = $user->notifications()->paginate(15);

        // Mark unread notifications as read
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }
}