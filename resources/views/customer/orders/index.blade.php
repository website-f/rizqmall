@extends('partials.app')

@section('title', 'My Orders - RizqMall')

@section('content')
    <div class="container py-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">My Orders</h2>
                <p class="text-muted mb-0">Track and manage all your orders</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('customer.orders.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by order number..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing
                            </option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders List -->
        @if ($orders->count() > 0)
            @foreach ($orders as $order)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Order Number</small>
                                <strong>{{ $order->order_number }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Store</small>
                                <strong>{{ $order->store->name }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Date</small>
                                <strong>{{ $order->created_at->format('M d, Y') }}</strong>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Total</small>
                                <strong class="text-primary">RM {{ number_format($order->total, 2) }}</strong>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="badge bg-{{ $order->status_color ?? 'secondary' }} px-3 py-2">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-3">Order Items ({{ $order->items->count() }})</h6>
                                @foreach ($order->items->take(3) as $item)
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($item->product)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"
                                                class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <div class="fw-medium">{{ $item->product->name }}</div>
                                                <small class="text-muted">Qty: {{ $item->quantity }} × RM
                                                    {{ number_format($item->price, 2) }}</small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                @if ($order->items->count() > 3)
                                    <small class="text-muted">+ {{ $order->items->count() - 3 }} more items</small>
                                @endif
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('customer.orders.show', $order) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                    @if ($order->status === 'delivered')
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-star me-2"></i>Rate Order
                                        </button>
                                    @endif
                                    @if (in_array($order->status, ['pending', 'confirmed']))
                                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#cancelModal{{ $order->id }}">
                                            <i class="fas fa-times me-2"></i>Cancel Order
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancel Order Modal -->
                <div class="modal fade" id="cancelModal{{ $order->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancel Order</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('customer.orders.cancel', $order) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p>Are you sure you want to cancel this order?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Reason for cancellation</label>
                                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No orders found</h5>
                    <p class="text-muted mb-3">You haven't placed any orders yet</p>
                    <a href="{{ route('rizqmall.home') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
