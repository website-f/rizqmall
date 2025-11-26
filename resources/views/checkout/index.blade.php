@extends('partials.app')

@section('title', 'Checkout - RizqMall')

@section('content')
    <div class="container py-5">
        <h2 class="mb-4">Checkout</h2>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
            @csrf
            <div class="row g-4">
                <!-- Checkout Form -->
                <div class="col-lg-8">
                    <!-- Delivery Address -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">1. Delivery Address</h5>
                                @auth
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#addressModal">
                                        <i class="fas fa-plus me-2"></i>Add New
                                    </button>
                                @endauth
                            </div>
                        </div>
                        <div class="card-body">
                            @auth
                                @if (isset($addresses) && $addresses->count() > 0)
                                    <div class="row g-3">
                                        @foreach ($addresses as $address)
                                            <div class="col-md-6">
                                                <div
                                                    class="form-check address-card p-3 border rounded {{ $address->is_default ? 'border-primary' : '' }}">
                                                    <input class="form-check-input" type="radio" name="address_id"
                                                        id="address{{ $address->id }}" value="{{ $address->id }}"
                                                        {{ $address->is_default ? 'checked' : '' }} required>
                                                    <label class="form-check-label w-100" for="address{{ $address->id }}">
                                                        @if ($address->is_default)
                                                            <span class="badge bg-primary mb-2">Default</span>
                                                        @endif
                                                        <strong class="d-block">{{ $address->recipient_name }}</strong>
                                                        <span class="d-block text-muted small">{{ $address->phone }}</span>
                                                        <span class="d-block text-muted small mt-1">
                                                            {{ $address->address_line1 }},
                                                            {{ $address->city }}, {{ $address->state }}
                                                            {{ $address->postal_code }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Please add a delivery address to continue.
                                        <button type="button" class="btn btn-sm btn-warning ms-2" data-bs-toggle="modal"
                                            data-bs-target="#addressModal">
                                            Add Address
                                        </button>
                                    </div>
                                @endif
                            @else
                                <!-- Guest Checkout Address Form -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="shipping_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="shipping_name" name="shipping_name"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone"
                                            required>
                                    </div>
                                    <div class="col-12">
                                        <label for="shipping_address" class="form-label">Address *</label>
                                        <input type="text" class="form-control" id="shipping_address" name="shipping_address"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="shipping_city" class="form-label">City *</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city"
                                            required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="shipping_state" class="form-label">State *</label>
                                        <select class="form-select" id="shipping_state" name="shipping_state" required>
                                            <option value="">Select State</option>
                                            <option value="Johor">Johor</option>
                                            <option value="Kedah">Kedah</option>
                                            <option value="Kelantan">Kelantan</option>
                                            <option value="Kuala Lumpur">Kuala Lumpur</option>
                                            <option value="Melaka">Melaka</option>
                                            <option value="Negeri Sembilan">Negeri Sembilan</option>
                                            <option value="Pahang">Pahang</option>
                                            <option value="Penang">Penang</option>
                                            <option value="Perak">Perak</option>
                                            <option value="Selangor">Selangor</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="shipping_postal_code" class="form-label">Postal Code *</label>
                                        <input type="text" class="form-control" id="shipping_postal_code"
                                            name="shipping_postal_code" required>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>

                    <!-- Shipping Method -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">2. Shipping Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="standard"
                                    value="standard" checked>
                                <label class="form-check-label w-100" for="standard">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Standard Delivery</strong>
                                            <p class="text-muted small mb-0">3-5 business days</p>
                                        </div>
                                        <strong>RM 5.00</strong>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="express"
                                    value="express">
                                <label class="form-check-label w-100" for="express">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Express Delivery</strong>
                                            <p class="text-muted small mb-0">1-2 business days</p>
                                        </div>
                                        <strong>RM 15.00</strong>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="pickup"
                                    value="pickup">
                                <label class="form-check-label w-100" for="pickup">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Self Pickup</strong>
                                            <p class="text-muted small mb-0">Pick up from store</p>
                                        </div>
                                        <strong class="text-success">FREE</strong>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">3. Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="online_banking"
                                    value="online_banking" checked>
                                <label class="form-check-label w-100" for="online_banking">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-university fa-2x text-primary me-3"></i>
                                        <div>
                                            <strong>Online Banking (FPX)</strong>
                                            <p class="text-muted small mb-0">Pay securely with your bank account</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="credit_card"
                                    value="credit_card">
                                <label class="form-check-label w-100" for="credit_card">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                        <div>
                                            <strong>Credit/Debit Card</strong>
                                            <p class="text-muted small mb-0">Visa, Mastercard, Amex</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="ewallet"
                                    value="ewallet">
                                <label class="form-check-label w-100" for="ewallet">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-wallet fa-2x text-primary me-3"></i>
                                        <div>
                                            <strong>E-Wallet</strong>
                                            <p class="text-muted small mb-0">Touch 'n Go, GrabPay, Boost</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod"
                                    value="cod">
                                <label class="form-check-label w-100" for="cod">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <p class="text-muted small mb-0">Pay when you receive</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Order Notes (Optional)</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions for your order?"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <!-- Cart Items -->
                            <div class="mb-3">
                                <h6 class="mb-3">Items ({{ $cart->items->count() ?? 0 }})</h6>
                                <div class="order-items" style="max-height: 200px; overflow-y: auto;">
                                    @if (isset($cart))
                                        @foreach ($cart->items as $item)
                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <img src="{{ $item->product->image_url ?? asset('placeholder.png') }}"
                                                        alt="{{ $item->product->name ?? 'Product' }}"
                                                        class="rounded me-2"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div class="small">
                                                        <div>{{ Str::limit($item->product->name ?? 'Product', 30) }}</div>
                                                        <div class="text-muted">Qty: {{ $item->quantity }}</div>
                                                    </div>
                                                </div>
                                                <span class="small fw-semibold">RM
                                                    {{ number_format($item->price * $item->quantity, 2) }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <!-- Price Breakdown -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span id="subtotal">RM
                                    {{ number_format($cart->items->sum(function ($item) {return $item->price * $item->quantity;}) ?? 0,2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span id="shipping-fee">RM 5.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount</span>
                                <span id="discount">-RM 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5">
                                <span>Total</span>
                                <span class="text-primary" id="total">RM
                                    {{ number_format(($cart->items->sum(function ($item) {return $item->price * $item->quantity;}) ??0) +5,2) }}</span>
                            </div>

                            <!-- Terms -->
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label small" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                                </label>
                            </div>

                            <!-- Place Order Button -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                                <i class="fas fa-lock me-2"></i>Place Order
                            </button>

                            <!-- Security Badges -->
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure SSL Encrypted Payment
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Update shipping fee based on selected method
        document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const shippingFee = document.getElementById('shipping-fee');
                const total = document.getElementById('total');
                const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('RM ',
                    '').replace(',', ''));

                let fee = 0;
                if (this.value === 'standard') fee = 5.00;
                if (this.value === 'express') fee = 15.00;
                if (this.value === 'pickup') fee = 0.00;

                shippingFee.textContent = fee === 0 ? 'FREE' : 'RM ' + fee.toFixed(2);
                total.textContent = 'RM ' + (subtotal + fee).toFixed(2);
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .address-card:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .form-check-input:checked+.form-check-label {
            font-weight: 500;
        }
    </style>
@endpush
