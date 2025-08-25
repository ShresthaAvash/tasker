<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tasker - Your Practice Management Solution</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Custom Styles for the landing page --}}
    <style>
        .navbar { background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.04); padding: 1rem 0; }
        .navbar-brand { font-weight: bold; color: #343a40 !important; }
        .navbar .nav-link { color: #495057; font-weight: 500; }
        .navbar .nav-link:hover { color: #0d6efd; }
        .footer { background-color: #343a40; color: #f8f9fa; padding: 3rem 0; }
        .footer a { color: #f8f9fa; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>

    @yield('page-styles')
</head>
<body class="antialiased">
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">TASKER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pricing') }}">Pricing</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            {{-- --- THIS IS THE MODIFIED LINE --- --}}
                            <a class="nav-link text-primary fw-bold ms-2" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                    @else
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
            <p class="mb-0">© {{ date('Y') }} Tasker. All Rights Reserved.</p>
        </div>
    </footer>

    @yield('page-scripts')
</body>
</html>