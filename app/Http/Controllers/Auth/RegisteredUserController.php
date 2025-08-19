<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan; // <-- It's good practice to import the model
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Notification; // <-- ADD THIS LINE
use App\Notifications\SubscriptionRequested;    // <-- ADD THIS LINE

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // This is correct, it passes the plan id to the view.
        return view('auth.register', ['plan_id' => $request->query('plan')]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id' => ['required', 'exists:plans,id'], // <-- THIS IS THE FIX
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'O',
            'status' => 'R', // Set status to 'Requested'
        ]);

        event(new Registered($user));

        // --- THIS IS THE FIX ---
        // Log the user in so they can proceed to the checkout page.
        Auth::login($user);

        // --- THIS IS THE NEW NOTIFICATION LOGIC ---
        $superAdmins = User::where('type', 'S')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SubscriptionRequested($user));
        }
        // --- END OF NEW LOGIC ---

        // Redirect to the subscription checkout page
        return redirect()->route('subscription.checkout', ['plan' => $request->plan_id]);
    }
}