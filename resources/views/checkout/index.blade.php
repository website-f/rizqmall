@extends('partials.app')

@section('title', 'Checkout - RizqMall')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>

    @php
        $taxRateValue = $taxRate ?? \App\Models\Setting::getFloat('tax_rate', config('rizqmall.tax_rate', 0.06));
        $shippingStandardValue = $shippingStandard ?? \App\Models\Setting::getFloat('shipping.standard', config('rizqmall.shipping.standard', 5.00));
        $shippingExpressValue = $shippingExpress ?? \App\Models\Setting::getFloat('shipping.express', config('rizqmall.shipping.express', 15.00));
        $shippingPickupValue = $shippingPickup ?? \App\Models\Setting::getFloat('shipping.pickup', config('rizqmall.shipping.pickup', 0.00));
        $taxRatePercent = rtrim(rtrim(number_format($taxRateValue * 100, 2), '0'), '.');
        $walletBalanceValue = $walletBalance ?? null;
        $walletTotalCents = isset($total) ? (int) round($total * 100) : null;
        $walletShortfall = null;
        $walletInsufficient = false;
        if (!is_null($walletBalanceValue) && !is_null($walletTotalCents) && $walletBalanceValue < $walletTotalCents) {
            $walletInsufficient = true;
            $walletShortfall = $walletTotalCents - $walletBalanceValue;
        }
    @endphp

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
                <!-- Delivery / Service Address -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                1. {{ ($hasPhysicalItems ?? false) ? 'Delivery Address' : 'Service Location (Optional)' }}
                            </h5>
                            @auth
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#addressModal">
                                <i class="fas fa-plus me-2"></i>Add New
                            </button>
                            @endauth
                        </div>
                    </div>
                    <div class="card-body">
                        @if (!($hasPhysicalItems ?? false))
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                If this service is performed at your location, add the address below.
                            </div>
                        @endif
                        @auth
                        @if (isset($addresses) && $addresses->count() > 0)
                        <div class="row g-3">
                            @foreach ($addresses as $address)
                            <div class="col-md-6">
                                <div
                                    class="form-check address-card p-3 border rounded {{ $address->is_default ? 'border-primary' : '' }}">
                                    <input class="form-check-input" type="radio" name="address_id"
                                        id="address{{ $address->id }}" value="{{ $address->id }}"
                                        {{ $address->is_default ? 'checked' : '' }} {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                                    <label class="form-check-label w-100" for="address{{ $address->id }}">
                                        @if ($address->is_default)
                                        <span class="badge bg-primary mb-2">Default</span>
                                        @endif
                                        @if ($address->label)
                                        <span class="badge bg-secondary mb-2">{{ $address->label }}</span>
                                        @endif
                                        <strong class="d-block">{{ $address->full_name }}</strong>
                                        <span class="d-block text-muted small">{{ $address->phone }}</span>
                                        <span class="d-block text-muted small mt-1">
                                            {{ $address->address_line_1 }}@if($address->address_line_2), {{ $address->address_line_2 }}@endif,
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
                            {{ ($hasPhysicalItems ?? false) ? 'Please add a delivery address to continue.' : 'Add a service location if needed.' }}
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
                                    {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone"
                                    {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                            </div>
                            <div class="col-12">
                                <label for="shipping_address" class="form-label">Address *</label>
                                <input type="text" class="form-control" id="shipping_address" name="shipping_address"
                                    {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label for="shipping_city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city"
                                    {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label for="shipping_state" class="form-label">State *</label>
                                <select class="form-select" id="shipping_state" name="shipping_state" {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
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
                                    name="shipping_postal_code" {{ ($hasPhysicalItems ?? false) ? 'required' : '' }}>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>

                <!-- Shipping Method -->
                @if ($hasPhysicalItems ?? false)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">2. Shipping Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="standard"
                                    value="standard" data-fee="{{ $shippingStandardValue }}" checked>
                                <label class="form-check-label w-100" for="standard">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Standard Delivery</strong>
                                            <p class="text-muted small mb-0">3-5 business days</p>
                                        </div>
                                        <strong>RM {{ number_format($shippingStandardValue, 2) }}</strong>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3 p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="express"
                                    value="express" data-fee="{{ $shippingExpressValue }}">
                                <label class="form-check-label w-100" for="express">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Express Delivery</strong>
                                            <p class="text-muted small mb-0">1-2 business days</p>
                                        </div>
                                        <strong>RM {{ number_format($shippingExpressValue, 2) }}</strong>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check p-3 border rounded">
                                <input class="form-check-input" type="radio" name="shipping_method" id="pickup"
                                    value="pickup" data-fee="{{ $shippingPickupValue }}">
                                <label class="form-check-label w-100" for="pickup">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>Self Pickup</strong>
                                            <p class="text-muted small mb-0">Pick up from store</p>
                                        </div>
                                        <strong class="text-success">
                                            {{ $shippingPickupValue > 0 ? 'RM ' . number_format($shippingPickupValue, 2) : 'FREE' }}
                                        </strong>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="shipping_method" value="pickup">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">2. Booking Type</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                No shipping required for service bookings.
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Delivery/Pickup/Booking Schedule -->
                @if ($requiresSchedule ?? false)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                3. {{ ($hasPhysicalItems ?? false) ? 'Delivery/Pickup Schedule' : 'Booking Schedule' }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ ($hasPhysicalItems ?? false) ? 'Select your preferred date and time for delivery or pickup' : 'Select your preferred date and time for your booking' }}
                            </p>
                            @if (($hasMarketplaceItems ?? false) && (($maxLeadTimeDays ?? 0) > 0 || ($latestPreorderDate ?? null)))
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-clock me-2"></i>
                                    @if (($maxLeadTimeDays ?? 0) > 0)
                                        Lead time up to {{ $maxLeadTimeDays }} day(s).
                                    @endif
                                    @if ($latestPreorderDate ?? null)
                                        Preorder releases on {{ \Carbon\Carbon::parse($latestPreorderDate)->format('M d, Y') }}.
                                    @endif
                                </div>
                            @endif
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="preferred_date" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="preferred_date" name="preferred_date"
                                        min="{{ $minScheduleDate->format('Y-m-d') }}"
                                        max="{{ $maxScheduleDate->format('Y-m-d') }}"
                                        required>
                                    <small class="text-muted">Available dates: {{ $minScheduleDate->format('M d, Y') }} to {{ $maxScheduleDate->format('M d, Y') }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="preferred_time" class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                    <select class="form-select" id="preferred_time" name="preferred_time" required>
                                        <option value="">Select Time Slot</option>
                                        <option value="09:00-12:00">Morning (9:00 AM - 12:00 PM)</option>
                                        <option value="12:00-15:00">Afternoon (12:00 PM - 3:00 PM)</option>
                                        <option value="15:00-18:00">Evening (3:00 PM - 6:00 PM)</option>
                                        <option value="18:00-21:00">Night (6:00 PM - 9:00 PM)</option>
                                    </select>
                                    <small class="text-muted">Choose a time slot that works for you</small>
                                </div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0" id="scheduleNote">
                                <i class="fas fa-truck me-2"></i>
                                <span id="scheduleNoteText">
                                    {{ ($hasPhysicalItems ?? false) ? 'Your order will be delivered on your selected date and time.' : 'Your booking will be scheduled for the selected date and time.' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment Method -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">4. Payment Method</h5>
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
                                        <strong>Rizq Wallet</strong>
                                        <p class="text-muted small mb-1">Pay using your Sandbox e-wallet balance</p>
                                        @if (!is_null($walletBalance))
                                            <p class="small mb-0 text-success">
                                                Balance: RM {{ number_format($walletBalance / 100, 2) }}
                                            </p>
                                            @if ($walletInsufficient)
                                                <p class="small mb-0 text-danger">
                                                    Insufficient balance. Need RM {{ number_format(($walletShortfall ?? 0) / 100, 2) }} more.
                                                </p>
                                            @endif
                                        @elseif (!empty($walletError))
                                            <p class="small mb-0 text-warning">
                                                Wallet unavailable. Try again later.
                                            </p>
                                        @else
                                            <p class="small mb-0 text-muted">
                                                No wallet linked yet. <a class="text-decoration-none"
                                                    href="{{ rtrim(config('services.sandbox.url'), '/') }}/wallet" target="_blank">Top up in Sandbox</a>
                                            </p>
                                        @endif
                                        @if (is_null($walletBalanceValue) || $walletInsufficient)
                                            <a class="btn btn-sm btn-outline-primary mt-2"
                                                href="{{ rtrim(config('services.sandbox.url'), '/') }}/wallet"
                                                target="_blank" rel="noopener">
                                                <i class="fas fa-plus-circle me-1"></i>Top up wallet
                                            </a>
                                        @else
                                            <a class="btn btn-sm btn-outline-secondary mt-2"
                                                href="{{ rtrim(config('services.sandbox.url'), '/') }}/wallet"
                                                target="_blank" rel="noopener">
                                                <i class="fas fa-wallet me-1"></i>Manage wallet
                                            </a>
                                        @endif
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
                        @if ($hasBulkItems ?? false)
                        <div class="alert alert-warning py-2 mb-3">
                            <i class="fas fa-boxes me-1"></i> <strong>Bulk Order</strong> — Bulk pricing applied to qualifying items
                        </div>
                        @endif

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
                                                @if (($item->product->type ?? null) === 'service')
                                                    <div class="text-muted">Booking fee at checkout</div>
                                                @endif
                                                @if (($item->product->allow_bulk_order ?? false))
                                                    @if (($item->product->minimum_order_quantity ?? 0) > 1)
                                                        <div class="text-muted">Bulk min: {{ $item->product->minimum_order_quantity }}</div>
                                                    @endif
                                                    @if (($item->product->bulk_quantity_threshold ?? 0) > 0 && $item->product->bulk_price)
                                                        <div class="text-muted">Bulk price: RM {{ number_format($item->product->bulk_price, 2) }} ({{ $item->product->bulk_quantity_threshold }}+)</div>
                                                    @endif
                                                @endif
                                                @if (($item->product->is_preorder ?? false) && $item->product->preorder_release_date)
                                                    <div class="text-muted">Preorder release: {{ \Carbon\Carbon::parse($item->product->preorder_release_date)->format('M d, Y') }}</div>
                                                @endif
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
                            <span id="subtotal">RM {{ number_format($subtotal ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span id="shipping-fee">{{ ($shipping ?? 0) > 0 ? 'RM ' . number_format($shipping, 2) : 'FREE' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax ({{ $taxRatePercent }}%)</span>
                            <span id="tax-amount" data-tax-rate="{{ $taxRateValue }}">RM {{ number_format($tax ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span id="discount">-RM 0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold h5">
                            <span>Total</span>
                            <span class="text-primary" id="total">RM {{ number_format($total ?? 0, 2) }}</span>
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

{{-- Add Address Modal --}}
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('customer.addresses.store') }}" method="POST" id="addAddressForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marker-alt me-2"></i>Add New Delivery Address
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                   placeholder="+60123456789">
                        </div>
                        <div class="col-12">
                            <label for="address_line_1" class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" id="address_line_1" name="address_line_1" required
                                   placeholder="Street address, building name">
                        </div>
                        <div class="col-12">
                            <label for="address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="address_line_2" name="address_line_2"
                                   placeholder="Apartment, suite, unit, floor (optional)">
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6">
                            <label for="postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required
                                   placeholder="e.g., 50000">
                        </div>
                        <div class="col-md-6">
                            <label for="state" class="form-label">State *</label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select State</option>
                                <option value="Johor">Johor</option>
                                <option value="Kedah">Kedah</option>
                                <option value="Kelantan">Kelantan</option>
                                <option value="Melaka">Melaka</option>
                                <option value="Negeri Sembilan">Negeri Sembilan</option>
                                <option value="Pahang">Pahang</option>
                                <option value="Penang">Penang</option>
                                <option value="Perak">Perak</option>
                                <option value="Perlis">Perlis</option>
                                <option value="Sabah">Sabah</option>
                                <option value="Sarawak">Sarawak</option>
                                <option value="Selangor">Selangor</option>
                                <option value="Terengganu">Terengganu</option>
                                <option value="WP Kuala Lumpur">WP Kuala Lumpur</option>
                                <option value="WP Labuan">WP Labuan</option>
                                <option value="WP Putrajaya">WP Putrajaya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="Malaysia" readonly>
                        </div>
                        <div class="col-12">
                            <label for="label" class="form-label">Address Label</label>
                            <select class="form-select" id="label" name="label">
                                <option value="Home">Home</option>
                                <option value="Office">Office</option>
                                <option value="Other">Other</option>
                            </select>
                            <small class="text-muted">Label for easy identification</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                                <label class="form-check-label" for="is_default">
                                    Set as default address
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Update shipping fee and schedule note based on selected method
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    if (shippingRadios.length > 0) {
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const shippingFee = document.getElementById('shipping-fee');
                const total = document.getElementById('total');
                const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('RM ',
                    '').replace(/,/g, ''));
                const taxElement = document.getElementById('tax-amount');
                const taxRate = taxElement ? parseFloat(taxElement.dataset.taxRate || '0.06') : 0.06;
                const scheduleNote = document.getElementById('scheduleNote');
                const scheduleNoteText = document.getElementById('scheduleNoteText');
                const scheduleNoteIcon = scheduleNote ? scheduleNote.querySelector('i') : null;

                const updateScheduleNote = (noteClass, iconClass, text) => {
                    if (!scheduleNote || !scheduleNoteText) {
                        return;
                    }
                    scheduleNote.className = noteClass;
                    if (scheduleNoteIcon) {
                        scheduleNoteIcon.className = iconClass;
                    }
                    scheduleNoteText.textContent = text;
                };

                let fee = parseFloat(this.dataset.fee || '0');
                if (this.value === 'standard') {
                    updateScheduleNote('alert alert-info mt-3 mb-0', 'fas fa-truck me-2', 'Your order will be delivered on your selected date and time.');
                }
                if (this.value === 'express') {
                    updateScheduleNote('alert alert-warning mt-3 mb-0', 'fas fa-shipping-fast me-2', 'Express delivery! Your order will arrive on your selected date within the chosen time slot.');
                }
                if (this.value === 'pickup') {
                    updateScheduleNote('alert alert-success mt-3 mb-0', 'fas fa-store me-2', 'Self pickup selected. Please come to the store on your selected date during the chosen time slot.');
                }

                shippingFee.textContent = fee === 0 ? 'FREE' : 'RM ' + fee.toFixed(2);
                const tax = parseFloat((subtotal * taxRate).toFixed(2));
                if (taxElement) {
                    taxElement.textContent = 'RM ' + tax.toFixed(2);
                }
                total.textContent = 'RM ' + (subtotal + fee + tax).toFixed(2);
            });
        });
    } else {
        const scheduleNote = document.getElementById('scheduleNote');
        const scheduleNoteText = document.getElementById('scheduleNoteText');
        if (scheduleNote && scheduleNoteText) {
            const scheduleNoteIcon = scheduleNote.querySelector('i');
            scheduleNote.className = 'alert alert-info mt-3 mb-0';
            if (scheduleNoteIcon) {
                scheduleNoteIcon.className = 'fas fa-calendar-check me-2';
            }
            scheduleNoteText.textContent = 'Your booking will be scheduled for the selected date and time.';
        }
    }

    // Set minimum date for preferred date (tomorrow)
    document.addEventListener('DOMContentLoaded', function() {
        const preferredDate = document.getElementById('preferred_date');
        if (!preferredDate) {
            return;
        }
        if (preferredDate.min || preferredDate.max) {
            return;
        }
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        preferredDate.min = tomorrow.toISOString().split('T')[0];

        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 30);
        preferredDate.max = maxDate.toISOString().split('T')[0];
    });

    // Handle add address form submission
    document.getElementById('addAddressForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addressModal'));
                modal.hide();

                // Show success message
                showToast('Success', 'Address added successfully!', 'success');

                // Reload page to show new address
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error', data.message || 'Failed to add address', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
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
