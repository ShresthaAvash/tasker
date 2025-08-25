@extends('layouts.site')

@section('page-styles')
<style>
    /* Hero Section */
    .hero-section {
        padding: 100px 0;
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    }
    .hero-section h1 {
        font-size: 3.8rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .hero-section .lead {
        font-size: 1.25rem;
        color: #495057;
    }
    .hero-section .btn {
        padding: 12px 30px;
        font-size: 1.1rem;
        font-weight: 500;
        border-radius: 8px;
    }

    /* Features Section */
    .features-section {
        padding: 80px 0;
    }
    .feature-icon {
        font-size: 2.5rem;
        color: #0d6efd;
        margin-bottom: 20px;
    }

    /* Testimonials Section */
    .testimonials-section {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    .testimonial-card {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .testimonial-card blockquote {
        font-style: italic;
        color: #495057;
        border-left: 3px solid #0d6efd;
        padding-left: 20px;
    }
    .testimonial-author {
        font-weight: 600;
    }

    /* CTA Section */
    .cta-section {
        padding: 80px 0;
        background-color: #343a40;
        color: #fff;
    }
    .cta-section h2 {
        font-weight: 700;
    }
</style>
@endsection

@section('content')
{{-- Hero Section --}}
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4">Tasker powers up your practice</h1>
                <p class="lead my-4">
                    With an easy-to-configure CRM, powerful workflow, integrations, and a user-friendly client
                    portal, Tasker helps you power up your accountancy or bookkeeping practice.
                </p>
                <a href="{{ route('pricing') }}" class="btn btn-primary me-2">View Pricing Plans</a>
                <a href="#" class="btn btn-outline-secondary">Play Demo</a>
            </div>
            {{-- You can place an image or illustration here --}}
            {{-- <div class="col-lg-6 text-center">
                <img src="/path/to/your/image.svg" class="img-fluid" alt="Tasker Dashboard">
            </div> --}}
        </div>
    </div>
</div>

{{-- Features Section --}}
<div class="features-section text-center" id="features">
    <div class="container">
        <h2 class="mb-5">Everything you need to grow your firm</h2>
        <div class="row">
            <div class="col-md-3">
                <i class="fas fa-sitemap feature-icon"></i>
                <h3>Streamlined Workflow</h3>
                <p class="text-muted">Automate your processes with powerful, easy-to-configure job and task templates.</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-users feature-icon"></i>
                <h3>Client Management</h3>
                <p class="text-muted">A complete CRM to manage client details, notes, documents, and contacts in one place.</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-shield-alt feature-icon"></i>
                <h3>Secure Client Portal</h3>
                <p class="text-muted">Collaborate with clients through a secure portal, improving communication and service.</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-clock feature-icon"></i>
                <h3>Time Tracking</h3>
                <p class="text-muted">Effortlessly track time against tasks and generate insightful reports for better billing.</p>
            </div>
        </div>
    </div>
</div>

{{-- Testimonials Section --}}
<div class="testimonials-section">
    <div class="container">
        <h2 class="text-center mb-5">Trusted by practices like yours</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="testimonial-card">
                    <blockquote class="blockquote">
                        <p>"Tasker has been a game-changer for our firm. We're more organized than ever, and our clients love the transparency of the portal."</p>
                    </blockquote>
                    <footer class="mt-3">
                        <span class="testimonial-author">Sarah J.</span>, <cite title="Source Title">Lead Accountant, Innovate Accounting</cite>
                    </footer>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="testimonial-card">
                    <blockquote class="blockquote">
                        <p>"The workflow automation saves us at least 10 hours a week. It’s incredibly powerful yet surprisingly simple to set up."</p>
                    </blockquote>
                    <footer class="mt-3">
                        <span class="testimonial-author">Mark T.</span>, <cite title="Source Title">Owner, Local Biz Bookkeeping</cite>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Final Call to Action Section --}}
<div class="cta-section text-center">
    <div class="container">
        <h2 class="mb-4">Ready to power up your practice?</h2>
        <p class="lead mb-4">Start your 14-day free trial today. No credit card required.</p>
        <a href="{{ route('pricing') }}" class="btn btn-primary btn-lg">Choose Your Plan</a>
    </div>
</div>
@endsection