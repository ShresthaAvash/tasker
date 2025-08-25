<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response|RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        // --- THIS IS THE DEFINITIVE FIX ---
        if ($user->type === 'O' && !$user->subscribed('default')) {
            // If a plan was selected before logging in, go straight to checkout.
            if ($request->filled('plan_id')) {
                return redirect()->route('subscription.checkout', ['plan' => $request->plan_id]);
            }
            // Otherwise, show them the pricing page to choose a plan.
            return redirect()->route('pricing')->with('info', 'Please choose a subscription plan to continue.');
        }
        // --- END OF FIX ---

        if ($user->type === 'S') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->type === 'O') {
            return redirect()->route('organization.dashboard');
        }
        
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}