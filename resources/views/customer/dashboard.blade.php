@extends('partials.app')

@section('title', 'My Dashboard - RizqMall')

@section('content')
    <div class="container py-5">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                    <div class="card-body p-4">
                        <h2 class="mb-1">Welcome back, {{ $user->name }}!</h2>
                        <p class="mb-0 opacity-75">Manage your orders, profile, and more</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Orders</p>
                                <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-bag text-primary fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Pending Orders</p>
                                <h3 class="mb-0">{{ $stats['pending_orders'] }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Completed</p>
                                <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Spent</p>
                                <h3 class="mb-0">RM {{ number_format($stats['total_spent'], 2) }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-dollar-sign text-info fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-primary">View
                                All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($recentOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Store</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentOrders as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('customer.orders.show', $order) }}"
                                                        class="text-decoration-none fw-semibold">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $order->store->name }}</td>
                                                <td>{{ $order->items->count() }} items</td>
                                                <td class="fw-semibold">RM {{ number_format($order->total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $order->status_color ?? 'secondary' }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No orders yet</h5>
                                <p class="text-muted mb-3">Start shopping to see your orders here</p>
                                <a href="{{ route('rizqmall.home') }}" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('customer.profile') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>Edit Profile
                            </a>
                            <a href="{{ route('customer.addresses') }}" class="btn btn-outline-primary">
                                <i class="fas fa-map-marker-alt me-2"></i>Manage Addresses
                            </a>
                            <a href="{{ route('customer.wishlist') }}" class="btn btn-outline-primary">
                                <i class="fas fa-heart me-2"></i>View Wishlist
                            </a>
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-cart me-2"></i>View Cart
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $user->email }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <strong>{{ $user->phone ?? 'Not provided' }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Member Since</small>
                            <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
