@extends('partials.app')

@section('title', 'Authentication Error - RizqMall')

@section('content')
<div class="container-small py-9">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div class="avatar avatar-5xl">
                            <div class="avatar-name rounded-circle bg-soft-danger">
                                <span class="fs-2 text-danger" data-feather="alert-triangle" style="width: 64px; height: 64px;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <h2 class="mb-3 text-body-emphasis">Authentication Failed</h2>
                    
                    <!-- Message -->
                    @if(session('error'))
                        <div class="alert alert-soft-danger mb-4" role="alert">
                            {{ session('error') }}
                        </div>
                    @else
                        <p class="text-body-secondary mb-4 fs-8">
                            We were unable to authenticate your account. This could be due to:
                        </p>

                        <div class="text-start mb-4">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <span class="fas fa-times-circle text-danger me-2"></span>
                                    Your session has expired
                                </li>
                                <li class="mb-2">
                                    <span class="fas fa-times-circle text-danger me-2"></span>
                                    Invalid authentication credentials
                                </li>
                                <li class="mb-2">
                                    <span class="fas fa-times-circle text-danger me-2"></span>
                                    Connection issue with the subscription system
                                </li>
                                <li class="mb-2">
                                    <span class="fas fa-times-circle text-danger me-2"></span>
                                    Your account may not be properly configured
                                </li>
                            </ul>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mb-4">
                        <a href="{{ config('services.subscription.base_url') }}/dashboard" 
                           class="btn btn-primary btn-lg">
                            <span class="fas fa-redo me-2"></span>
                            Try Again via Subscription System
                        </a>
                        <a href="{{ route('auth.login') }}" class="btn btn-phoenix-secondary">
                            <span class="fas fa-sign-in-alt me-2"></span>
                            Direct Login
                        </a>
                        <a href="{{ route('rizqmall.home') }}" class="btn btn-link">
                            Back to Home
                        </a>
                    </div>

                    <!-- Help Section -->
                    <div class="bg-body-highlight rounded-3 p-3">
                        <div class="d-flex align-items-start text-start">
                            <span class="fas fa-question-circle text-primary me-3 mt-1"></span>
                            <div>
                                <h6 class="mb-2 text-body-emphasis">Need Help?</h6>
                                <p class="mb-2 fs-9 text-body-secondary">
                                    If you continue to experience issues, please contact our support team.
                                </p>
                                <a href="mailto:support@rizqmall.com" class="text-decoration-none fs-9">
                                    <span class="fas fa-envelope me-1"></span>
                                    support@rizqmall.com
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection