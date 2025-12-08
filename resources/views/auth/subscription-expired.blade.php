@extends('partials.app')

@section('title', 'Subscription Expired - RizqMall')

@section('content')
<div class="container-small py-9">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div class="avatar avatar-5xl">
                            <div class="avatar-name rounded-circle bg-soft-warning">
                                <span class="fs-2 text-warning" data-feather="clock" style="width: 64px; height: 64px;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <h2 class="mb-3 text-body-emphasis">Subscription Expired</h2>
                    
                    <!-- Message -->
                    @if(session('error'))
                        <div class="alert alert-soft-warning mb-4" role="alert">
                            {{ session('error') }}
                        </div>
                    @else
                        <p class="text-body-secondary mb-4 fs-8">
                            Your vendor subscription has expired and you no longer have access to vendor features.
                        </p>
                    @endif

                    <div class="bg-body-tertiary rounded-3 p-4 mb-4">
                        <h6 class="mb-3 text-body-emphasis">What you can do:</h6>
                        <div class="text-start">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <span class="fas fa-check-circle text-success me-2"></span>
                                    Renew your subscription to regain vendor access
                                </li>
                                <li class="mb-2">
                                    <span class="fas fa-check-circle text-success me-2"></span>
                                    Continue shopping as a customer
                                </li>
                                <li class="mb-2">
                                    <span class="fas fa-check-circle text-success me-2"></span>
                                    Contact support for assistance
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 mb-4">
                        <a href="{{ config('services.subscription.base_url') }}/dashboard" 
                           class="btn btn-primary btn-lg">
                            <span class="fas fa-sync-alt me-2"></span>
                            Renew Subscription
                        </a>
                        <a href="{{ route('rizqmall.home') }}" class="btn btn-phoenix-secondary">
                            <span class="fas fa-shopping-cart me-2"></span>
                            Continue Shopping
                        </a>
                        <a href="{{ route('auth.logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                           class="btn btn-link">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>

                    <!-- Help Section -->
                    <div class="bg-body-highlight rounded-3 p-3">
                        <div class="d-flex align-items-start text-start">
                            <span class="fas fa-question-circle text-primary me-3 mt-1"></span>
                            <div>
                                <h6 class="mb-2 text-body-emphasis">Need Help?</h6>
                                <p class="mb-2 fs-9 text-body-secondary">
                                    If you believe this is an error or need assistance with your subscription, please contact our support team.
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
