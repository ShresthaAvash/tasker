<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Reset Password</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Custom Styles for the Forgot Password Page --}}
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
        .forgot-password-container {
            width: 100%;
            max-width: 550px;
            padding: 20px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .forgot-password-box {
            animation: fadeIn 0.6s ease-out forwards;
            background-color: var(--card-bg-color);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            text-align: center;
        }
        .forgot-password-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .forgot-password-logo a { 
            color: inherit; 
            text-decoration: none; 
        }
        .forgot-password-box h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }
        .forgot-password-box p.lead {
            color: var(--text-muted-color);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        /* --- THIS IS THE FIX --- */
        .input-wrapper {
            position: relative;
        }
        .input-wrapper .form-control {
            height: 52px;
            padding-left: 45px;
            border-radius: 8px;
            background-color: var(--input-bg-color);
            border: 1px solid #e0e0e0;
            transition: all 0.2s ease-in-out;
            width: 100%;
            box-sizing: border-box;
        }
        .input-wrapper .form-control:focus {
            background-color: #fff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12, 111, 253, 0.15);
        }
        .input-wrapper .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            transition: color 0.2s ease-in-out;
        }
        .input-wrapper .form-control:focus + .form-icon { 
            color: var(--primary-color); 
        }
        /* --- END OF FIX --- */
        .reset-button {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: #ffffff !important;
            font-weight: 600;
            height: 52px;
            border-radius: 8px;
            transition: all 0.2s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-button:hover {
            background-color: var(--primary-hover-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(12, 111, 253, 0.2);
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="forgot-password-container">
        <div class="forgot-password-box">
            <h1 class="forgot-password-logo"><a href="/">Tasker</a></h1>
            <h2>Forgot Password?</h2>
            <p class="lead text-center mb-4">
                {{ __('No problem. Just let us know your email address and we will email you a password reset link.') }}
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="text-left">
                @csrf

                {{-- MODIFIED: Added .input-wrapper --}}
                <div class="form-group">
                    <div class="input-wrapper">
                        <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus placeholder="Email Address"/>
                        <i class="fas fa-envelope form-icon"></i>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary reset-button">
                        {{ __('Email Password Reset Link') }}
                    </button>
                </div>

                <div style="text-align: left; margin-top: 15px;">
                    <a href="{{ route('login') }}" 
                    style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">
                        Go Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>