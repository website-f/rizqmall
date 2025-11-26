@extends('partials.app')

@section('title', 'Customer Registration - RizqMall')

@section('content')
    <div class="container-small py-9">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <!-- Logo -->
                <div class="text-center mb-5">
                    <a href="{{ route('rizqmall.home') }}">
                        <img src="{{ asset('assets/rizqmall.jpeg') }}" alt="RizqMall" width="120" class="mb-3">
                    </a>
                    <h3 class="text-body-emphasis">Create Your Account</h3>
                    <p class="text-body-secondary">Join RizqMall and start shopping today!</p>
                </div>

                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <span class="fas fa-exclamation-circle me-2"></span>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <span class="fas fa-check-circle me-2"></span>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('customer.register') }}" method="POST">
                            @csrf

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}"
                                        placeholder="Enter your full name" required autofocus>
                                </div>
                                @error('name')
                                    <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="fas fa-envelope"></span>
                                    </span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}"
                                        placeholder="name@example.com" required>
                                </div>
                                @error('email')
                                    <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="fas fa-phone"></span>
                                    </span>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}" placeholder="+60123456789"
                                        required>
                                </div>
                                @error('phone')
                                    <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Create a strong password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <span class="fas fa-eye"></span>
                                    </button>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                                @error('password')
                                    <div class="text-danger fs-9 mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </span>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Re-enter your password" required>
                                </div>
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label fs-9" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                                    and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <span class="fas fa-user-plus me-2"></span>
                                Create Account
                            </button>

                            <!-- Divider -->
                            <div class="position-relative my-4">
                                <hr class="bg-body-secondary">
                                <div class="position-absolute top-50 start-50 translate-middle bg-body px-3">
                                    <span class="text-body-tertiary fs-9">OR</span>
                                </div>
                            </div>

                            <!-- Vendor Registration Link -->
                            <div class="text-center">
                                <p class="text-body-secondary fs-9 mb-2">Want to sell on RizqMall?</p>
                                <a href="{{ config('services.sandbox.url', 'http://localhost:8000') }}/register"
                                    class="btn btn-outline-primary w-100">
                                    <span class="fas fa-store me-2"></span>
                                    Register as Vendor
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Login Link -->
                <div class="card mt-3 border-0 bg-body-emphasis">
                    <div class="card-body p-4 text-center">
                        <p class="mb-2 text-body-secondary">Already have an account?</p>
                        <a href="{{ route('login') }}" class="btn btn-link text-decoration-none">
                            <span class="fas fa-sign-in-alt me-2"></span>
                            Sign In
                        </a>
                    </div>
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

        // Password strength indicator
        const passwordInput = document.getElementById('password');
        passwordInput?.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            // You can add visual feedback here
        });
    </script>
@endpush
