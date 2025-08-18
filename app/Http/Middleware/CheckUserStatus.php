<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentRoute = Route::currentRouteName();

            // Handle users with pending payment ('R' status)
            if ($user->status === 'R') {
                $allowedRoutes = ['subscription.checkout', 'subscription.store', 'logout'];
                if (!in_array($currentRoute, $allowedRoutes)) {
                    return redirect()->route('subscription.checkout', ['plan' => 1]);
                }
            }

            // --- THIS IS THE DEFINITIVE FIX ---
            // If the user is an Organization and their subscription has been CANCELED
            // (even if it's still in the grace period), redirect them.
            if ($user->type === 'O' && $user->subscribed('default') && $user->subscription('default')->canceled()) {
                $allowedRoutes = ['subscription.expired', 'logout']; // They can only see the expired page or log out
                if (!in_array($currentRoute, $allowedRoutes)) {
                    return redirect()->route('subscription.expired');
                }
            }
            // --- END OF FIX ---

            // Handle manually suspended accounts ('I' status)
            if ($user->status === 'I') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/login')->with('error', 'Your account has been suspended.');
            }
        }

        return $next($request);
    }
}