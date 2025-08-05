<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    /**
     * Where to redirect users after login.
    *
    * @return string
    */
    
    protected function redirectTo()
{
    if (auth()->user()->type === 'S') {
        return route('superadmin.dashboard');
    } elseif (auth()->user()->type === 'O') {
        return route('organization.dashboard');
    }
    return '/'; // fallback if type is not defined
}


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
}
