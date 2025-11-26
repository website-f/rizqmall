@extends('partials.app')

@section('title', 'Order Details - RizqMall')

@section('content')
    <div class="container py-5">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-2">Order #{{ $order->order_number }}</h4>
                        <p class="text-muted mb-0">
                            Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-{{ $order->status_color ?? 'secondary' }} px-3 py-2 fs-6">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($item->product)
                                                        <img src="{{ $item->product->image_url }}"
                                                            alt="{{ $item->product->name }}" class="rounded me-3"
                                                            style="width: 60px; height: 60px; object-fit: cover;">
                                                        <div>
                                                            <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                            @if ($item->variant)
                                                                <small class="text-muted">{{ $item->variant->name }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div>
                                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                            <small class="text-muted">Product no longer available</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>RM {{ number_format($item->price, 2) }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td class="fw-semibold">RM
                                                {{ number_format($item->price * $item->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Order Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6>Order Delivered</h6>
                                    @if ($order->delivered_at)
                                        <p class="text-muted small mb-0">{{ $order->delivered_at->format('M d, Y h:i A') }}
                                        </p>
                                    @else
                                        <p class="text-muted small mb-0">Pending</p>
                                    @endif
                                </div>
                            </div>

                            <div
                                class="timeline-item {{ in_array($order->status, ['out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6>Out for Delivery</h6>
                                    <p class="text-muted small mb-0">
                                        @if ($order->tracking_number)
                                            Tracking: {{ $order->tracking_number }}
                                        @else
                                            Awaiting dispatch
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div
                                class="timeline-item {{ in_array($order->status, ['processing', 'ready_for_pickup', 'out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6>Processing</h6>
                                    <p class="text-muted small mb-0">Your order is being prepared</p>
                                </div>
                            </div>

                            <div class="timeline-item completed">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6>Order Confirmed</h6>
                                    <p class="text-muted small mb-0">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary & Actions -->
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>RM {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>RM {{ number_format($order->shipping_fee ?? 0, 2) }}</span>
                        </div>
                        @if ($order->discount > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Discount</span>
                                <span>-RM {{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        @if ($order->tax > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax</span>
                                <span>RM {{ number_format($order->tax, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between fw-bold h5">
                            <span>Total</span>
                            <span class="text-primary">RM {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Store Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Store Information</h5>
                    </div>
                    <div class="card-body">
                        <h6>{{ $order->store->name }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>{{ $order->store->address }}
                        </p>
                        @if ($order->store->phone)
                            <p class="text-muted small mb-2">
                                <i class="fas fa-phone me-2"></i>{{ $order->store->phone }}
                            </p>
                        @endif
                        <a href="{{ route('store.profile', $order->store->slug) }}"
                            class="btn btn-outline-primary btn-sm w-100 mt-2">
                            <i class="fas fa-store me-2"></i>Visit Store
                        </a>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $order->shipping_name }}</strong></p>
                        <p class="mb-1">{{ $order->shipping_phone }}</p>
                        <p class="mb-0 text-muted small">
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}
                        </p>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Method</span>
                            <span class="fw-semibold">{{ ucfirst($order->payment_method ?? 'COD') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Payment Status</span>
                            <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Print Invoice
                            </button>

                            @if ($order->status == 'delivered')
                                <button class="btn btn-outline-success">
                                    <i class="fas fa-star me-2"></i>Rate & Review
                                </button>
                            @endif

                            @if (in_array($order->status, ['pending', 'confirmed']))
                                <button class="btn btn-outline-danger" data-bs-toggle="modal"
                                    data-bs-target="#cancelOrderModal">
                                    <i class="fas fa-times me-2"></i>Cancel Order
                                </button>
                            @endif

                            <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1">
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
@endsection

@push('styles')
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 8px;
            bottom: -22px;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #e0e0e0;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e0e0e0;
        }

        .timeline-item.completed .timeline-marker {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }

        .timeline-item.completed::before {
            background: #28a745;
        }

        @media print {

            .btn,
            .card-header,
            .timeline,
            nav,
            footer {
                display: none !important;
            }
        }
    </style>
@endpush
