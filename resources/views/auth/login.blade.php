<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Custom Styles for the Login Page --}}
        <style>
            :root {
                --primary-color: #3c8dbc; /* AdminLTE light blue */
                --primary-hover-color: #367fa9;
                --body-bg-color: #f4f6f9; /* Light grey background */
            }

            body {
                background-color: var(--body-bg-color) !important;
                font-family: 'Figtree', sans-serif;
            }
            
            .login-container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1rem;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .login-box {
                animation: fadeIn 0.7s ease-out forwards;
                width: 100%;
                max-width: 28rem;
                margin-top: 1.5rem;
                padding: 2.5rem;
                background-color: white;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }

            .login-button {
                background-color: var(--primary-color) !important;
                transition: background-color 0.3s;
                width: 100%;
                justify-content: center;
            }
            .login-button:hover {
                background-color: var(--primary-hover-color) !important;
            }

            .login-box a { color: var(--primary-color); }
        </style>
    </head>
        <body class="font-sans text-gray-900 antialiased">
        <div class="login-container">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="login-box">
                {{-- Session Status for things like password resets --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- --- THIS IS THE FIX: Display the error message --- --}}
                @if (session('error'))
                    <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded-md">
                        {{ session('error') }}
                    </div>
                @endif
                {{-- --- END OF FIX --- --}}

<form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="block mt-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <!-- Log In Button -->
                    <div class="mt-4">
                        <x-primary-button class="login-button">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                
                    {{-- --- THIS IS THE FIX --- --}}
                    <!-- Forgot Password & Register Links -->
                    <div class="text-center mt-4">
                        @if (Route::has('password.request'))
                            <p class="mb-2">
                                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            </p>
                        @endif

                        <p>
                            <a href="{{ route('register') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
                                {{ __("Don't have an account? Register") }}
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>