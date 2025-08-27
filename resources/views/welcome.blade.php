<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tasker - Your Practice Management Solution</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts & Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Custom Styles for the new landing page design --}}
    <style>
        :root {
            --primary-blue: #0d6efd;
            --light-blue: #e3f2fd;
            --dark-blue: #0a58ca;
            --text-dark: #212529;
            --text-light: #6c757d;
            --section-bg: #f8f9fa;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Figtree', sans-serif; }
        .section { padding: 80px 0; }
        .section-title { font-weight: 700; font-size: 2.8rem; color: var(--text-dark); }
        .section-subtitle { font-size: 1.15rem; color: var(--text-light); max-width: 600px; margin: 0 auto 50px auto; }

        /* Navbar */
        .navbar-custom { background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 1rem 0; }
        .navbar-brand { font-weight: 700; }
        .navbar .nav-link { color: var(--text-dark); font-weight: 500; transition: color 0.2s; }
        .navbar .nav-link:hover, .navbar .nav-link.active { color: var(--primary-blue); }
        .navbar .btn-login { border: 2px solid var(--primary-blue); font-weight: 600; padding: 0.5rem 1.5rem; border-radius: 50px; }
        .navbar .btn-login:hover { background-color: var(--primary-blue); color: #fff; }

        /* Hero Section */
        .hero-section { background-color: var(--primary-blue); color: #fff; padding: 120px 0; }
        .hero-section h1 { font-size: 3.8rem; font-weight: 700; line-height: 1.2; }
        .hero-section .lead { font-size: 1.25rem; opacity: 0.9; }
        .hero-section .highlight { color: #ffdd57; }
        .hero-section .feature-list li { margin-bottom: 1rem; }
        .hero-section .btn { padding: 12px 30px; font-size: 1.1rem; font-weight: 600; border-radius: 8px; }
        .hero-image-wrapper { background: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }

        /* Feature & Step Cards */
        .feature-card, .step-card-wrapper { background: #fff; border: 1px solid #e3e6f0; border-radius: 16px; padding: 30px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .feature-card:hover, .step-card-wrapper:hover { transform: translateY(-8px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .feature-card .icon { font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 20px; }
        .step-card { text-align: center; }
        .step-number { background-color: var(--primary-blue); color: #fff; width: 40px; height: 40px; line-height: 40px; border-radius: 50%; margin: 0 auto 20px auto; font-weight: 700; }
        
        /* Pricing Section Styling */
        .pricing-section {
            padding: 80px 0;
            background-color: #f8f9fa; /* Light grey background */
        }
        .pricing-header {
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 50px;
        }
        .pricing-header h2 {
            font-weight: 700;
            font-size: 2.8rem;
            color: #1a202c;
        }
        .pricing-header p {
            font-size: 1.15rem;
            color: #6c757d;
        }
        .pricing-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px; /* Space between cards */
        }
        .pricing-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 40px 30px; /* Vertical and horizontal padding */
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center content horizontally */
            flex: 0 0 340px; /* Fixed width for cards */
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .pricing-card.highlight {
            background: linear-gradient(180deg, #4f80f8 0%, #3b66f5 100%);
            border: none;
            color: #fff;
            transform: scale(1.05); /* Makes the highlighted card slightly larger */
        }
        .pricing-card.highlight:hover {
            transform: scale(1.08); /* Slightly larger hover effect */
        }
        .pricing-card.highlight .plan-name,
        .pricing-card.highlight .price,
        .pricing-card.highlight .price-period,
        .pricing-card.highlight .plan-description {
            color: #fff;
        }
        .pricing-card.highlight .feature-list li { color: #e0ecff; }
        .pricing-card.highlight .feature-list .fa-check-circle { color: #fff; }
        .pricing-card.highlight .btn-purchase { background: #fff; color: #2563eb; }
        .most-popular-badge {
            position: absolute;
            top: -14px; /* Position badge above the card */
            left: 50%;
            transform: translateX(-50%);
            background: #ffffff;
            color: #3b82f6;
            padding: 5px 15px;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 14px; /* Pill shape */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .plan-name { font-weight: 600; font-size: 1.1rem; margin-bottom: 10px; color: #1a202c; }
        .price { font-size: 3rem; font-weight: 700; color: #1a202c; }
        .price-period { font-size: 1rem; color: #6c757d; font-weight: 500; margin-bottom: 20px; }
        .plan-description { font-size: 0.95rem; color: #4a5568; margin-bottom: 25px; text-align: center; min-height: 40px; }
        .feature-list { list-style: none; padding: 0; margin: 15px 0 30px 0; text-align: left; width: 100%; flex-grow: 1; }
        .feature-list li { margin-bottom: 14px; display: flex; align-items: center; font-size: 0.95rem; color: #4a5568;}
        .feature-list .fa-check-circle { color: #3b82f6; margin-right: 12px; font-size: 1.1rem; }
        .btn-purchase {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            background: #3b82f6;
            color: #fff;
            border: 1px solid #3b82f6;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-purchase.btn-outline {
            background: transparent;
            color: #3b82f6;
        }
        .btn-purchase:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Contact & Footer */
        .contact-section { background-color: var(--section-bg); }
        .contact-info-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .footer { background: linear-gradient(45deg, #0d6efd, #0a58ca); color: #fff; padding-top: 60px; }
        .footer a { color: #fff; text-decoration: none; }
        .footer .social-icon { width: 40px; height: 40px; background: rgba(255,255,255,0.1); display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; transition: background-color 0.3s; }
        .footer .social-icon:hover { background: rgba(255,255,255,0.2); }
        .footer-bottom { background-color: rgba(0,0,0,0.1); padding: 15px 0; margin-top: 40px; }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#navbarNav">

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">TASKER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#why-tasker">Why Tasker</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Subscription</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        @auth
                            <a class="btn btn-primary" href="{{ route('dashboard') }}">Dashboard</a>
                        @else
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        {{-- Hero Section --}}
        <section class="hero-section" id="home">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1>Organize Your Tasks <br><span class="highlight">Anywhere, Anytime</span></h1>
                        <p class="lead my-4">
                            Streamline your workflow with Tasker's powerful task management platform. Boost productivity and never miss a deadline again.
                        </p>
                        <ul class="list-unstyled feature-list lead">
                            <li><i class="fas fa-check-circle me-2"></i> Create and organize tasks effortlessly</li>
                            <li><i class="fas fa-check-circle me-2"></i> Collaborate with your team in real-time</li>
                            <li><i class="fas fa-check-circle me-2"></i> Track progress with powerful analytics</li>
                        </ul>
                        <a href="#pricing" class="btn btn-light me-2">Get Started Now</a>
                    </div>
                    <div class="col-lg-6 text-center d-none d-lg-block">
                        <div class="hero-image-wrapper">
                            <img src="https://i.imgur.com/yO8pP5k.png" class="img-fluid rounded" alt="Tasker Dashboard Preview">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- About Section --}}
        <section class="section text-center" id="about">
            <div class="container">
                <h2 class="section-title">About <span class="text-primary">Tasker</span></h2>
                <p class="section-subtitle">Tasker is a comprehensive task management platform designed to help individuals and teams organize, prioritize, and complete their work more efficiently. With powerful features and an intuitive interface, managing tasks has never been easier.</p>
                <div class="row">
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-bolt icon"></i><h3>Lightning Fast</h3><p>Experience blazing-fast performance with our optimized task management engine.</p></div></div>
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-shield-alt icon"></i><h3>Secure & Private</h3><p>Your data is protected with enterprise-grade security and privacy controls.</p></div></div>
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-users icon"></i><h3>Team Collaboration</h3><p>Work seamlessly with your team using real-time collaboration features.</p></div></div>
                </div>
            </div>
        </section>
        
        {{-- How It Works Section --}}
        <section class="section text-center bg-light" id="how-it-works">
            <div class="container">
                <h2 class="section-title">How <span class="text-primary">Tasker</span> Works</h2>
                <p class="section-subtitle">A simple, four-step process to get you from chaos to organized clarity.</p>
                <div class="row mt-5">
                    <div class="col-md-3 mb-4"><div class="step-card-wrapper h-100"><div class="step-card"><div class="step-number">1</div><i class="fas fa-user-plus fa-3x mb-3"></i><h3>Sign Up & Setup</h3><p>Create your account in seconds and customize your workspace to match your workflow.</p></div></div></div>
                    <div class="col-md-3 mb-4"><div class="step-card-wrapper h-100"><div class="step-card"><div class="step-number">2</div><i class="fas fa-tasks fa-3x mb-3"></i><h3>Create & Organize</h3><p>Add tasks, set priorities, and organize them into projects with our intuitive interface.</p></div></div></div>
                    <div class="col-md-3 mb-4"><div class="step-card-wrapper h-100"><div class="step-card"><div class="step-number">3</div><i class="fas fa-users-cog fa-3x mb-3"></i><h3>Collaborate & Share</h3><p>Invite team members, assign tasks, and collaborate in real-time on shared projects.</p></div></div></div>
                    <div class="col-md-3 mb-4"><div class="step-card-wrapper h-100"><div class="step-card"><div class="step-number">4</div><i class="fas fa-chart-pie fa-3x mb-3"></i><h3>Track & Analyze</h3><p>Monitor progress with detailed analytics and insights to optimize your productivity.</p></div></div></div>
                </div>
            </div>
        </section>

        {{-- Why Choose Tasker Section --}}
        <section class="section" id="why-tasker">
            <div class="container">
                <h2 class="section-title text-center">Why Choose <span class="text-primary">Tasker</span>?</h2>
                <p class="section-subtitle">Join thousands of professionals and teams who have transformed their productivity with Tasker. Here’s what makes us the preferred choice for task management.</p>
                 <div class="row">
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-lock icon"></i><h3>Enterprise Security</h3><p>Bank-grade encryption and security protocols to keep your data safe and secure.</p></div></div>
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-clock icon"></i><h3>Save 3+ Hours Daily</h3><p>Streamlined workflows and smart automation help you reclaim valuable time.</p></div></div>
                    <div class="col-md-4 mb-4"><div class="feature-card h-100"><i class="fas fa-globe icon"></i><h3>Works Everywhere</h3><p>Native web app for iOS, Android, Windows, Mac, and web. Always in sync.</p></div></div>
                </div>
            </div>
        </section>

        {{-- Pricing Section --}}
        <section class="pricing-section" id="pricing">
            <div class="container">
                <div class="pricing-header text-center">
                    <h2>Choose Your Perfect Plan</h2>
                    <p>Scale your productivity with Tasker. From personal task management to enterprise-grade solutions.</p>
                </div>
        
                @if(session('info'))
                    <div class="alert alert-info mb-4 col-md-8 mx-auto">
                        {{ session('info') }}
                    </div>
                @endif
        
                <div class="pricing-container">
                    @forelse($plans as $plan)
                        @php
                            $isHighlighted = $loop->iteration == 2;
                        @endphp
                        <div class="pricing-card {{ $isHighlighted ? 'highlight' : '' }}">
                            @if($isHighlighted)
                                <div class="most-popular-badge">Most Popular</div>
                            @endif
        
                            <h5 class="plan-name">{{ $plan->name }}</h5>
                            <h2 class="price">
                                £{{ number_format($plan->type == 'annually' && $plan->price > 0 ? $plan->price / 12 : $plan->price, 2) }}
                            </h2>
                            <p class="price-period">
                                Per {{ $plan->type == 'annually' ? 'year' : 'month' }} +VAT
                            </p>
        
                            <p class="plan-description">{{ $plan->description }}</p>
        
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Unlimited client & staff</li>
                                <li><i class="fas fa-check-circle"></i> Workflow & task automation</li>
                                <li><i class="fas fa-check-circle"></i> Advance task organization</li>
                                <li><i class="fas fa-check-circle"></i> Team tracking & reporting</li>
                                <li><i class="fas fa-check-circle"></i> Email & push notification</li>
                                <li><i class="fas fa-check-circle"></i> Mobile support</li>
                            </ul>
                           
                            @php
                                $buttonClass = $isHighlighted ? '' : 'btn-outline';
                            @endphp
        
                            @auth
                                @if(Auth::user()->subscribed('default') && Auth::user()->subscription('default')->stripe_price === $plan->stripe_price_id)
                                    <button type="button" class="btn-purchase mt-auto" disabled>Current Plan</button>
                                @else
                                     <a href="{{ route('subscription.checkout', ['plan' => $plan->id]) }}" class="btn-purchase mt-auto {{ $buttonClass }}">Get Started</a>
                                @endif
                            @else
                                <a href="{{ route('register', ['plan' => $plan->id]) }}" class="btn-purchase mt-auto {{ $buttonClass }}">Get Started</a>
                            @endauth
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                No subscription plans have been configured by the administrator yet. Please check back later.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Get in Touch Section --}}
        <section class="section contact-section" id="contact">
            <div class="container">
                <h2 class="section-title text-center">Get in <span class="text-primary">Touch</span></h2>
                <p class="section-subtitle">Have questions about Tasker? We're here to help. Reach out to our team and we'll get back to you as soon as possible.</p>
                {{-- MODIFIED: Adjusted column classes for better spacing --}}
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h4>Send us a message</h4>
                        <form action="#" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="First Name" required></div>
                                <div class="col-md-6 mb-3"><input type="text" class="form-control" placeholder="Last Name" required></div>
                            </div>
                            <div class="mb-3"><input type="email" class="form-control" placeholder="Email Address" required></div>
                            <div class="mb-3"><input type="text" class="form-control" placeholder="Company (optional)"></div>
                            <div class="mb-3"><textarea class="form-control" rows="5" placeholder="Tell us how we can help..."></textarea></div>
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </form>
                    </div>
                    <div class="col-lg-5 offset-lg-1">
                        <div class="contact-info-card mb-3">
                            <div class="d-flex align-items-center"><i class="fas fa-envelope fa-2x text-primary me-3"></i><div><strong>Email Us</strong><br><a href="mailto:hello@tasker.com">hello@tasker.com</a><br><small>Contact us via email.</small></div></div>
                        </div>
                        <div class="contact-info-card mb-3">
                             <div class="d-flex align-items-center"><i class="fas fa-phone fa-2x text-primary me-3"></i><div><strong>Call Us</strong><br>+977 9878763542<br><small>Monday to Friday, 9AM-6PM PST</small></div></div>
                        </div>
                         <div class="contact-info-card">
                             <div class="d-flex align-items-center"><i class="fas fa-map-marker-alt fa-2x text-primary me-3"></i><div><strong>Visit Us</strong><br>Pulchowk, Lalitpur, 44600<br><small>Monday to Friday, 9AM-6PM PST</small></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center text-lg-start mb-4 mb-lg-0">
                    <h4 class="fw-bold">TASKER</h4>
                    <p class="mb-0">Streamline your workflow. Organize tasks, track progress, and collaborate in one place.</p>
                </div>
                <div class="col-lg-5 text-center mb-4 mb-lg-0">
                    <a href="#pricing" class="mx-2">Pricing</a>
                    <a href="#how-it-works" class="mx-2">How It Works</a>
                    <a href="#about" class="mx-2">About Tasker</a>
                    <a href="{{ route('login') }}" class="mx-2">Login</a>
                </div>
                <div class="col-lg-3 text-center text-lg-end">
                    <p class="small mb-2">Powered by Gtech Vision</p>
                    <div>
                        <a href="#" class="social-icon mx-1"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon mx-1"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon mx-1"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container text-center">
                <small>© {{ date('Y') }} Tasker. All rights reserved.</small>
            </div>
        </div>
    </footer>

    {{-- JavaScript for Smooth Scrolling and Active Nav Link Highlighting --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // This enables the Bootstrap scrollspy feature
            const mainNav = document.body.querySelector('#navbarNav');
            if (mainNav) {
                new bootstrap.ScrollSpy(document.body, {
                    target: '#navbarNav',
                    offset: 74, // Adjust this value based on your navbar height
                });
            };

            // This handles the smooth scroll for all anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>