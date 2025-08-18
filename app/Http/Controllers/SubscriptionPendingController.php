<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionPendingController extends Controller
{
    /**
     * Show the subscription pending page.
     */
    public function index()
    {
        return view('auth.pending');
    }

    /**
     * --- THIS IS THE NEW METHOD ---
     * Show the subscription expired/ended page.
     */
    public function expired()
    {
        return view('subscription.expired');
    }
}