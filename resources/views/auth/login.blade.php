@extends('partials.app')

@section('title', 'Login - RizqMall')

@section('content')
<div class="container-small py-9">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <!-- Logo -->
            <div class="text-center mb-5">
                <a href="{{ route('rizqmall.home') }}">
                    <img src="{{ asset('assets/rizqmall.jpeg') }}" alt="RizqMall" width="120" class="mb-3">
                </a>
                <h3 class="text-body-emphasis">Welcome Back!</h3>
                <p class="text-body-secondary">Sign in to continue to RizqMall</p>
            </div>

            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    
                    <!-- SSO Login (Recommended) -->
                    <div class="mb-4">
                        <a href="{{ config('services.subscription.base_url') }}/dashboard" 
                           class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                            <span class="fas fa-sign-in-alt me-2"></span>
                            Login via Subscription Account
                        </a>
                        <p class="text-center mt-2 mb-0 fs-9 text-body-tertiary">
                            Login through your subscription account (Recommended)
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="position-relative my-4">
                        <hr class="bg-body-secondary">
                        <div class="position-absolute top-50 start-50 translate-middle bg-body px-3">
                            <span class="text-body-tertiary fs-9">OR</span>
                        </div>
                    </div>

                    <!-- Direct Login Form -->
                    <h6 class="text-body-emphasis mb-3">Quick Login</h6>
                    <p class="text-body-secondary fs-9 mb-4">
                        If you've previously logged in to RizqMall, you can use quick login.
                    </p>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <span class="fas fa-exclamation-circle me-2"></span>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <span class="fas fa-check-circle me-2"></span>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('auth.login') }}" method="POST">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="name@example.com"
                                       required 
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <span class="fas fa-eye"></span>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="remember" 
                                   name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-phoenix-secondary w-100">
                            <span class="fas fa-sign-in-alt me-2"></span>
                            Sign In
                        </button>
                    </form>

                    <!-- Forgot Password -->
                    <div class="text-center mt-4">
                        <a href="{{ config('services.subscription.base_url') }}/password/reset" 
                           class="text-decoration-none fs-9">
                            Forgot your password?
                        </a>
                    </div>
                </div>
            </div>

            <!-- Register Link -->
            <div class="card mt-3 border-0 bg-body-emphasis">
                <div class="card-body p-4 text-center">
                    <p class="mb-2 text-body-secondary">Don't have an account?</p>
                    <a href="{{ config('services.subscription.base_url') }}/register" 
                       class="btn btn-link text-decoration-none">
                        <span class="fas fa-user-plus me-2"></span>
                        Create New Account
                    </a>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-4">
                <p class="fs-9 text-body-tertiary mb-2">
                    <span class="fas fa-info-circle me-1"></span>
                    Having trouble signing in?
                </p>
                <a href="mailto:support@rizqmall.com" class="text-decoration-none fs-9">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Auto-focus email field
    document.getElementById('email')?.focus();
</script>
@endpush