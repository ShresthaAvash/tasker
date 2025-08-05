<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsOrganization
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->type === 'O') {
            return $next($request);
        }

        return redirect('/login')->withErrors('Unauthorized access.');
    }
}
