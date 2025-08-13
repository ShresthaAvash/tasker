<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    /**
     * Show the application's landing page.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Show the application's pricing page.
     */
    public function pricing()
    {
        $subscriptions = Subscription::all();
        return view('pricing', compact('subscriptions'));
    }
}