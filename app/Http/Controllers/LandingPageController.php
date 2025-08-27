<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    /**
     * Show the application's landing page.
     */
    public function index()
    {
        // MODIFIED: Fetch plans to display on the homepage
        $plans = Plan::all();
        return view('welcome', compact('plans'));
    }

    /**
     * Show the application's pricing page.
     */
    public function pricing()
    {
        // This was already correct, no changes needed here.
        $subscriptions = Plan::all();
        return view('pricing', compact('subscriptions'));
    }
}