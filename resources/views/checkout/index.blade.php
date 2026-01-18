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

                <!-- Delivery/Pickup Schedule -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">3. Delivery/Pickup Schedule</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Select your preferred date and time for delivery or pickup
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="preferred_date" class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="preferred_date" name="preferred_date"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                    required>
                                <small class="text-muted">Available dates: Tomorrow to 30 days from now</small>
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
                            <span id="scheduleNoteText">Your order will be delivered on your selected date and time.</span>
                        </div>
                    </div>
                </div>

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
                        @php
                        $calculatedSubtotal = $cart->items->sum(function ($item) {
                        return floatval($item->price ?? 0) * intval($item->quantity ?? 1);
                        });
                        $shippingFee = 5.00;
                        $calculatedTotal = $calculatedSubtotal + $shippingFee;
                        @endphp
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal">RM {{ number_format($calculatedSubtotal, 2) }}</span>
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
                            <span class="text-primary" id="total">RM {{ number_format($calculatedTotal, 2) }}</span>
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
    document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const shippingFee = document.getElementById('shipping-fee');
            const total = document.getElementById('total');
            const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('RM ',
                '').replace(',', ''));
            const scheduleNote = document.getElementById('scheduleNote');
            const scheduleNoteText = document.getElementById('scheduleNoteText');

            let fee = 0;
            if (this.value === 'standard') {
                fee = 5.00;
                scheduleNote.className = 'alert alert-info mt-3 mb-0';
                scheduleNote.querySelector('i').className = 'fas fa-truck me-2';
                scheduleNoteText.textContent = 'Your order will be delivered on your selected date and time.';
            }
            if (this.value === 'express') {
                fee = 15.00;
                scheduleNote.className = 'alert alert-warning mt-3 mb-0';
                scheduleNote.querySelector('i').className = 'fas fa-shipping-fast me-2';
                scheduleNoteText.textContent = 'Express delivery! Your order will arrive on your selected date within the chosen time slot.';
            }
            if (this.value === 'pickup') {
                fee = 0.00;
                scheduleNote.className = 'alert alert-success mt-3 mb-0';
                scheduleNote.querySelector('i').className = 'fas fa-store me-2';
                scheduleNoteText.textContent = 'Self pickup selected. Please come to the store on your selected date during the chosen time slot.';
            }

            shippingFee.textContent = fee === 0 ? 'FREE' : 'RM ' + fee.toFixed(2);
            total.textContent = 'RM ' + (subtotal + fee).toFixed(2);
        });
    });

    // Set minimum date for preferred date (tomorrow)
    document.addEventListener('DOMContentLoaded', function() {
        const preferredDate = document.getElementById('preferred_date');
        if (preferredDate) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            preferredDate.min = tomorrow.toISOString().split('T')[0];

            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 30);
            preferredDate.max = maxDate.toISOString().split('T')[0];
        }
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