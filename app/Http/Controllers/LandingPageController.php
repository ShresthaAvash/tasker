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
        return view('welcome');
    }

    /**
     * Show the application's pricing page.
     */
    public function pricing()
    {
        $subscriptions = Plan::all();
        return view('pricing', compact('subscriptions'));
    }
}