<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsStaff
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->type === 'T') {
            return $next($request);
        }

        return redirect('/login')->withErrors('Unauthorized access.');
    }
}
