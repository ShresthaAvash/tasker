<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Only allow user with type 'S' (SuperAdmin)
        if (Auth::check() && Auth::user()->type === 'S') {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
