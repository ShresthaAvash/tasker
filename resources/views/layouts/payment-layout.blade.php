<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Secure Checkout') - {{ config('app.name', 'Tasker') }}</title>

    <!-- Scripts & Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f4f6f9; /* Light grey background */
        }
        .checkout-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
    </style>

    @yield('page-styles')
</head>
<body class="antialiased">
    <div class="checkout-container container">
        <main class="col-md-8 col-lg-6 mx-auto">
            @yield('content')
        </main>
    </div>

    @yield('js')
</body>
</html>