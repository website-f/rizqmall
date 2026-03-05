@extends('partials.admin')

@section('title', 'Quote Request Details')

@section('content')
<div class="container-fluid px-4" style="max-width: 900px;">
    <a href="{{ route('vendor.bulk-quotes.index') }}" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-1"></i> Back to Quotes
    </a>

    @if(session('success'))
        <div class="alert alert-success border-0">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Quote Details -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <h4 class="fw-bold mb-0">Quote Request #{{ $quote->id }}</h4>
                <span class="badge bg-{{ $quote->status_badge }} fs-6">{{ $quote->status_label }}</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Buyer Information</h6>
                    <p class="mb-1"><strong>{{ $quote->user->name }}</strong></p>
                    <p class="mb-0 text-muted">{{ $quote->user->email }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Product</h6>
                    <p class="mb-1"><strong>{{ $quote->product->name }}</strong></p>
                    <p class="mb-0 text-muted">Regular price: RM {{ number_format($quote->product->regular_price, 2) }}</p>
                </div>
            </div>

            <hr>

            <div class="row g-4">
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Requested Quantity</h6>
                    <p class="fs-4 fw-bold mb-0">{{ number_format($quote->requested_quantity) }} units</p>
                </div>
                @if($quote->quoted_price)
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Quoted Price (per unit)</h6>
                    <p class="fs-4 fw-bold text-primary mb-0">RM {{ number_format($quote->quoted_price, 2) }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Quoted Total</h6>
                    <p class="fs-4 fw-bold text-success mb-0">RM {{ number_format($quote->quoted_total, 2) }}</p>
                </div>
                @endif
            </div>

            @if($quote->buyer_notes)
            <hr>
            <h6 class="text-muted mb-2">Buyer Notes</h6>
            <p class="mb-0">{{ $quote->buyer_notes }}</p>
            @endif

            @if($quote->vendor_notes)
            <hr>
            <h6 class="text-muted mb-2">Your Response Notes</h6>
            <p class="mb-0">{{ $quote->vendor_notes }}</p>
            @endif

            @if($quote->expires_at)
            <hr>
            <p class="text-muted mb-0">
                <i class="fas fa-clock me-1"></i>
                Quote expires: {{ $quote->expires_at->format('d M Y, h:i A') }}
                @if($quote->isExpired())
                    <span class="badge bg-danger ms-2">Expired</span>
                @endif
            </p>
            @endif
        </div>
    </div>

    <!-- Response Form (only for pending quotes) -->
    @if($quote->status === 'pending')
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body">
            <h5 class="fw-bold mb-4">
                <i class="fas fa-reply me-2"></i> Respond to Quote
            </h5>

            <form action="{{ route('vendor.bulk-quotes.respond', $quote->id) }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Quoted Price per Unit (RM) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quoted_price"
                               class="form-control form-control-lg" placeholder="0.00"
                               value="{{ old('quoted_price', $quote->product->bulk_price ?? $quote->product->regular_price) }}" required>
                        <small class="text-muted">
                            Total: RM <span id="calculatedTotal">0.00</span>
                            ({{ number_format($quote->requested_quantity) }} units)
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Quote Valid For (days) <span class="text-danger">*</span></label>
                        <input type="number" name="expires_in_days" class="form-control form-control-lg"
                               value="7" min="1" max="30" required>
                        <small class="text-muted">How many days the buyer has to accept</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Notes to Buyer</label>
                    <textarea name="vendor_notes" class="form-control" rows="3"
                              placeholder="Any terms, conditions, or delivery details...">{{ old('vendor_notes') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i> Send Quote
                    </button>
                    <form action="{{ route('vendor.bulk-quotes.respond', $quote->id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="quoted_price" value="0">
                        <input type="hidden" name="expires_in_days" value="1">
                    </form>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const priceInput = document.querySelector('input[name="quoted_price"]');
            const totalSpan = document.getElementById('calculatedTotal');
            const quantity = {{ $quote->requested_quantity }};

            function updateTotal() {
                const price = parseFloat(priceInput.value) || 0;
                totalSpan.textContent = (price * quantity).toFixed(2);
            }

            priceInput.addEventListener('input', updateTotal);
            updateTotal();
        });
    </script>
    @endif
</div>
@endsection
