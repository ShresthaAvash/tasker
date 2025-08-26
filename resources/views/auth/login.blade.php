<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Custom Styles for the Login Page --}}
    <style>
        :root {
            --primary-color: #0c6ffd;
            --primary-hover-color: #0a58ca;
            --body-bg-color: #f0f2f5;
            --card-bg-color: #ffffff;
            --input-bg-color: #f7f7f7;
            --text-color: #333;
            --text-muted-color: #6c757d;
        }

        body {
            background-color: var(--body-bg-color);
            font-family: 'Figtree', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 550px; /* Wider container */
            padding: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-box {
            animation: fadeIn 0.6s ease-out forwards;
            background-color: var(--card-bg-color);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            text-align: center;
        }

        .login-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            letter-spacing: -1px;
        }

        .login-logo a {
            color: inherit;
            text-decoration: none;
        }

        .login-box h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .login-box p.lead {
            color: var(--text-muted-color);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group .form-control {
            height: 52px; /* Set explicit height */
            padding-left: 45px;
            border-radius: 8px;
            background-color: var(--input-bg-color);
            border: 1px solid #e0e0e0;
            transition: all 0.2s ease-in-out;
            width: 100%; /* ✅ Make inputs full width */
            box-sizing: border-box; /* ✅ Prevent overflow */
        }

        .form-group .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12, 111, 253, 0.15);
        }
        
        .form-group .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            transition: color 0.2s ease-in-out;
        }

        .form-group .form-control:focus + .form-icon {
            color: var(--primary-color);
        }

        .login-button {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #ffffff !important;
            font-weight: 600;
            height: 52px; /* Match input height */
            border-radius: 8px;
            transition: all 0.2s;
            width: 100%; /* ✅ Full width button */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-button:hover {
            background-color: var(--primary-hover-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(12, 111, 253, 0.2);
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%; /* ✅ Stretch across box */
        }

        .form-check-label {
            color: var(--text-muted-color);
        }

        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="login-container">
        <div class="login-box">
            <h1 class="login-logo"><a href="/">Tasker</a></h1>
            
            <h2>Welcome Back!</h2>
            <p class="lead">Log in to continue to your dashboard.</p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if (session('error'))
                <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="text-left">
                @csrf
                
                @if (request()->has('plan'))
                    <input type="hidden" name="plan_id" value="{{ request()->query('plan') }}">
                @endif

                <!-- Email Address -->
                <div class="form-group">
                    <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Email Address" />
                    <i class="fas fa-envelope form-icon"></i>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="form-group">
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" placeholder="Password"/>
                    <i class="fas fa-lock form-icon"></i>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me + Forgot Password -->
                <div class="d-flex mb-4">
                    <div class="form-check">
                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                        <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
                    </div>
                     @if (Route::has('password.request'))
                        <a class="text-sm" style="color: var(--primary-color); text-decoration: none;" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <!-- Log In Button -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary login-button">
                        {{ __('Log In') }}
                    </button>
                </div>
            
                <!-- Register Link -->
                <div class="text-center mt-4 text-muted">
                    Don't have an account? 
                    <a href="{{ route('pricing') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                        View Pricing Plans
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
