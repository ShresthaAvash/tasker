<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // --- THIS IS THE DEFINITIVE FIX ---
        // Instead of only allowing the Organization owner ('O'), we now allow ANY user
        // whose type indicates they belong to the organization (Owner 'O', or Staff 'A'/'T').
        // This will grant access to the page, and the menu config will handle showing the correct links.

        if (Auth::check() && in_array(Auth::user()->type, ['O', 'A', 'T'])) {
            return $next($request);
        }

        // If the user is not an authorized type (like a Superadmin or Client), deny access.
        abort(403, 'Unauthorized action.');
    }
}