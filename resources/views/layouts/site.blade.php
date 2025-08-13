<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tasker - Your Practice Management Solution</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    {{-- REMOVED Bootstrap CDN link --}}
    {{-- @vite will now handle ALL CSS and JS --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Custom Styles for the landing page --}}
    <style>
        .navbar { background-color: #343a40; }
        .navbar-brand { font-weight: bold; }
        .navbar-dark .navbar-nav .nav-link { color: rgba(255,255,255,.75); }
        .navbar-dark .navbar-nav .nav-link:hover { color: #fff; }
        .footer { background-color: #f8f9fa; padding: 2rem 0; }
    </style>

    @yield('page-styles')
</head>
<body class="antialiased">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">TASKER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        {{-- Show Dashboard, Profile and Logout for logged-in users --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    Logout
                                </a>
                            </form>
                        </li>
                    @else
                        {{-- Show Login and Sign Up for guests --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container text-center">
            <span class="text-muted">© {{ date('Y') }} Tasker. All Rights Reserved.</span>
        </div>
    </footer>

    @yield('page-scripts')
</body>
</html>