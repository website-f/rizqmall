@extends('partials.admin')

@section('title', 'Bulk Quote Requests')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Bulk Quote Requests</h2>
            <p class="text-muted">Manage quote requests from buyers</p>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

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

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            @if($quotes->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No quote requests yet</h5>
                    <p class="text-muted">Quote requests from buyers will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Buyer</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Quoted Price</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotes as $quote)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $quote->user->name }}</div>
                                        <small class="text-muted">{{ $quote->user->email }}</small>
                                    </td>
                                    <td>{{ Str::limit($quote->product->name, 30) }}</td>
                                    <td>{{ number_format($quote->requested_quantity) }}</td>
                                    <td>{{ $quote->quoted_price ? 'RM ' . number_format($quote->quoted_price, 2) : '-' }}</td>
                                    <td>{{ $quote->quoted_total ? 'RM ' . number_format($quote->quoted_total, 2) : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $quote->status_badge }}">{{ $quote->status_label }}</span>
                                    </td>
                                    <td>{{ $quote->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('vendor.bulk-quotes.show', $quote->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
