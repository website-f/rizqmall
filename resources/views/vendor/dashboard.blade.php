@extends('partials.admin')

@section('title', 'Vendor Dashboard - RizqMall')

@section('content')
    <div class="container-fluid py-4">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">Welcome back, {{ auth()->user()->name }}!</h2>
                                <p class="mb-0 opacity-75">{{ $store->name }}</p>
                            </div>
                            <div>
                                <a href="{{ route('store.profile', $store->slug) }}" class="btn btn-light" target="_blank">
                                    <i class="fas fa-external-link-alt me-2"></i>View Store
                                </a>
                            </div>
                        </div>
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
                                <p class="text-muted mb-1 small">Total Products</p>
                                <h3 class="mb-0">{{ $stats['total_products'] }}</h3>
                                <small class="text-success">{{ $stats['active_products'] }} active</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-box text-primary fa-lg"></i>
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
                                <p class="text-muted mb-1 small">Total Orders</p>
                                <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                                <small class="text-warning">{{ $stats['pending_orders'] }} pending</small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-cart text-success fa-lg"></i>
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
                                <p class="text-muted mb-1 small">Total Revenue</p>
                                <h3 class="mb-0">RM {{ number_format($stats['total_revenue'], 2) }}</h3>
                                <small class="text-muted">All time</small>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-dollar-sign text-info fa-lg"></i>
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
                                <p class="text-muted mb-1 small">This Month</p>
                                <h3 class="mb-0">RM {{ number_format($stats['monthly_revenue'], 2) }}</h3>
                                <small class="text-muted">{{ now()->format('F Y') }}</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-chart-line text-warning fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if ($stats['total_products'] == 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-1">Get Started!</h5>
                                <p class="mb-2">You haven't added any products yet. Start by adding your first product to
                                    your store.</p>
                                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-2"></i>Add Your First Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Recent Orders -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary">View
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
                                            <th>Customer</th>
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
                                                    <a href="{{ route('vendor.orders.show', $order) }}"
                                                        class="text-decoration-none">
                                                        {{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $order->user->name }}</td>
                                                <td>{{ $order->items->count() }}</td>
                                                <td>RM {{ number_format($order->total, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $order->status_color }}">
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
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No orders yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Top Selling Products</h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($topProducts->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach ($topProducts as $product)
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ Str::limit($product->name, 30) }}</h6>
                                                <small class="text-muted">{{ $product->sold_count }} sold</small>
                                            </div>
                                            <div class="text-end">
                                                <strong>RM {{ number_format($product->regular_price, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No products yet</p>
                                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">
                                    Add Product
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
