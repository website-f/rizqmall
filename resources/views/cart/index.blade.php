@extends('partials.app')

@section('title', 'Shopping Cart - RizqMall')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4">Shopping Cart</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (isset($cart) && $cart->items->count() > 0)
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Cart Items ({{ $cart->items->count() }})</h5>
                                <form action="{{ route('cart.clear') }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to clear your cart?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-2"></i>Clear Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @foreach ($cart->items as $item)
                                <div class="cart-item p-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            @if ($item->product)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                                    class="img-fluid rounded"
                                                    style="width: 100%; height: 100px; object-fit: cover;">
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            @if ($item->product)
                                                <h6 class="mb-1">
                                                    <a href="{{ route('product.show', $item->product->slug) }}"
                                                        class="text-decoration-none text-dark">
                                                        {{ $item->product->name }}
                                                    </a>
                                                </h6>
                                                <p class="text-muted small mb-1">
                                                    <i class="fas fa-store me-1"></i>{{ $item->product->store->name }}
                                                </p>
                                                @if ($item->variant)
                                                    <p class="text-muted small mb-0">{{ $item->variant->name }}</p>
                                                @endif

                                                <!-- Stock Status -->
                                                @if ($item->product->stock_quantity <= 0)
                                                    <span class="badge bg-danger mt-1">Out of Stock</span>
                                                @elseif($item->product->stock_quantity < $item->quantity)
                                                    <span class="badge bg-warning mt-1">Only
                                                        {{ $item->product->stock_quantity }} available</span>
                                                @endif
                                            @else
                                                <h6 class="mb-1">Product Unavailable</h6>
                                                <p class="text-muted small">This product is no longer available</p>
                                            @endif
                                        </div>
                                        <div class="col-md-2">
                                            <p class="mb-0 fw-semibold">RM {{ number_format($item->price, 2) }}</p>
                                        </div>
                                        <div class="col-md-2">
                                            <form action="{{ route('cart.update', $item) }}" method="POST"
                                                class="quantity-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        onclick="decrementQuantity(this)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" class="form-control text-center" name="quantity"
                                                        value="{{ $item->quantity }}" min="1"
                                                        max="{{ $item->product->stock_quantity ?? 99 }}"
                                                        onchange="this.form.submit()">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        onclick="incrementQuantity(this)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <p class="mb-2 fw-bold text-primary">RM
                                                {{ number_format($item->price * $item->quantity, 2) }}</p>
                                            <form action="{{ route('cart.remove', $item) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="mt-3">
                        <a href="{{ route('rizqmall.home') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal ({{ $cart->items->count() }} items)</span>
                                <span>RM
                                    {{ number_format($cart->items->sum(function ($item) {return $item->price * $item->quantity;}),2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span class="text-success">Calculated at checkout</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5 mb-0">
                                <span>Total</span>
                                <span class="text-primary">RM
                                    {{ number_format($cart->items->sum(function ($item) {return $item->price * $item->quantity;}),2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Coupon Code -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">Have a coupon code?</h6>
                            <form action="{{ route('cart.apply-coupon') }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="text" class="form-control" name="coupon_code" placeholder="Enter code">
                                    <button class="btn btn-outline-primary" type="submit">Apply</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="d-grid gap-2">
                        @auth
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                            </a>
                            <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#guestCheckoutModal">
                                Continue as Guest
                            </button>
                        @endauth
                    </div>

                    <!-- Trust Badges -->
                    <div class="card border-0 bg-light mt-3">
                        <div class="card-body text-center">
                            <div class="row g-3">
                                <div class="col-4">
                                    <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Secure Payment</p>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Fast Delivery</p>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                                    <p class="small mb-0">Easy Returns</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Your cart is empty</h5>
                    <p class="text-muted mb-4">Add items to your cart to continue shopping</p>
                    <a href="{{ route('rizqmall.home') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        @endif

        <!-- Recently Viewed Products -->
        @if (isset($recentlyViewed) && $recentlyViewed->count() > 0)
            <div class="mt-5">
                <h4 class="mb-4">Recently Viewed</h4>
                <div class="row g-3">
                    @foreach ($recentlyViewed->take(4) as $product)
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100">
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}"
                                    style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">{{ Str::limit($product->name, 40) }}</h6>
                                    <p class="text-primary fw-bold mb-2">RM {{ number_format($product->price, 2) }}</p>
                                    <a href="{{ route('product.show', $product->slug) }}"
                                        class="btn btn-outline-primary btn-sm w-100">
                                        View Product
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Guest Checkout Modal -->
    <div class="modal fade" id="guestCheckoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Guest Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('auth.guest-checkout') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="guest_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="guest_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="guest_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="guest_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="guest_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="guest_phone" name="phone" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function incrementQuantity(btn) {
            const input = btn.parentElement.querySelector('input[name="quantity"]');
            const max = parseInt(input.getAttribute('max'));
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
                input.form.submit();
            }
        }

        function decrementQuantity(btn) {
            const input = btn.parentElement.querySelector('input[name="quantity"]');
            const current = parseInt(input.value);
            if (current > 1) {
                input.value = current - 1;
                input.form.submit();
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .cart-item:hover {
            background-color: #f8f9fa;
        }

        .quantity-form input[type="number"]::-webkit-inner-spin-button,
        .quantity-form input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
@endpush
