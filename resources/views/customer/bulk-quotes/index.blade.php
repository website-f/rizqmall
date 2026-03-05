@extends('partials.app')

@section('title', 'My Quote Requests')

@section('content')
<div class="container py-4" style="max-width: 1000px;">
    <h2 class="fw-bold mb-1">My Quote Requests</h2>
    <p class="text-muted mb-4">Track your bulk order quote requests</p>

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

    @if($quotes->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 16px;">
            <div class="card-body">
                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No quote requests yet</h5>
                <p class="text-muted">Browse products with bulk ordering and request a quote.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i> Browse Products
                </a>
            </div>
        </div>
    @else
        @foreach($quotes as $quote)
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $quote->product->name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-store me-1"></i> {{ $quote->store->name }}
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i> {{ $quote->created_at->format('d M Y') }}
                        </p>
                    </div>
                    <span class="badge bg-{{ $quote->status_badge }} fs-6">{{ $quote->status_label }}</span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Quantity</small>
                        <strong>{{ number_format($quote->requested_quantity) }} units</strong>
                    </div>
                    @if($quote->quoted_price)
                    <div class="col-md-3">
                        <small class="text-muted d-block">Price per Unit</small>
                        <strong class="text-primary">RM {{ number_format($quote->quoted_price, 2) }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Total</small>
                        <strong class="text-success">RM {{ number_format($quote->quoted_total, 2) }}</strong>
                    </div>
                    @endif
                    @if($quote->expires_at)
                    <div class="col-md-3">
                        <small class="text-muted d-block">Expires</small>
                        <strong class="{{ $quote->isExpired() ? 'text-danger' : '' }}">
                            {{ $quote->expires_at->format('d M Y') }}
                        </strong>
                    </div>
                    @endif
                </div>

                @if($quote->buyer_notes)
                <p class="text-muted small mb-2">
                    <i class="fas fa-sticky-note me-1"></i> {{ $quote->buyer_notes }}
                </p>
                @endif

                @if($quote->vendor_notes)
                <p class="text-muted small mb-2">
                    <i class="fas fa-reply me-1"></i> Vendor: {{ $quote->vendor_notes }}
                </p>
                @endif

                @if($quote->canBeAccepted())
                <div class="d-flex gap-2 mt-3">
                    <form action="{{ route('customer.bulk-quotes.accept', $quote->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Accept this quote and create an order for RM {{ number_format($quote->quoted_total, 2) }}?')">
                            <i class="fas fa-check me-1"></i> Accept & Create Order
                        </button>
                    </form>
                    <form action="{{ route('customer.bulk-quotes.buyer-reject', $quote->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Are you sure you want to reject this quote?')">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <div class="mt-3">
            {{ $quotes->links() }}
        </div>
    @endif
</div>
@endsection
