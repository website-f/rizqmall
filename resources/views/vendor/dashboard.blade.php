@extends('partials.admin')

@php
    $productTerm = $store->product_term ?? 'Product';
    $productTermPlural = $store->product_term_plural ?? 'Products';
    $productTermLower = strtolower($productTerm);
    $productTermPluralLower = strtolower($productTermPlural);
@endphp

@section('title', 'Vendor Dashboard - RizqMall')

@section('content')
<style>
    /* ========== Modern Dashboard Styles ========== */
    .dashboard-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 16px;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 28px 32px;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: 10%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .welcome-banner h2 {
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: 4px;
    }
    .welcome-banner p {
        opacity: 0.85;
        font-size: 0.95rem;
    }
    .welcome-banner .btn-light {
        border-radius: 12px;
        font-weight: 600;
        padding: 10px 20px;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .welcome-banner .btn-light:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        color: #6b7280;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 4px;
    }
    .stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.2;
    }
    .stat-card .stat-sub {
        font-size: 0.75rem;
        margin-top: 2px;
    }

    /* Content Cards */
    .content-card {
        background: white;
        border-radius: 16px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .content-card .card-header {
        background: white;
        border-bottom: 1px solid #f3f4f6;
        padding: 16px 20px;
    }
    .content-card .card-header h5 {
        font-weight: 700;
        font-size: 1rem;
        color: #1f2937;
        margin: 0;
    }
    .content-card .card-body {
        padding: 0;
    }

    /* Table Styles */
    .modern-table {
        margin-bottom: 0;
        font-size: 0.875rem;
    }
    .modern-table thead th {
        background: #f9fafb;
        border: none;
        padding: 12px 16px;
        font-weight: 600;
        color: #6b7280;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .modern-table tbody td {
        padding: 12px 16px;
        border-color: #f3f4f6;
        vertical-align: middle;
    }
    .modern-table tbody tr:hover {
        background: #f9fafb;
    }
    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge Styles */
    .badge-modern {
        padding: 5px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.7rem;
        letter-spacing: 0.3px;
    }

    /* Quick Actions */
    .quick-action-card {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 16px;
        padding: 20px;
        border: 2px solid #bae6fd;
        margin-bottom: 24px;
    }
    .quick-action-card h5 {
        font-weight: 700;
        color: #0369a1;
        margin-bottom: 8px;
    }
    .quick-action-card p {
        color: #0c4a6e;
        font-size: 0.9rem;
    }

    /* Bulk Quote Alert */
    .quote-alert {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
        border: 2px solid #fde047;
        border-radius: 16px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .quote-alert-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #facc15;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #713f12;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .quote-alert-content h6 {
        font-weight: 700;
        color: #713f12;
        margin-bottom: 2px;
    }
    .quote-alert-content p {
        color: #854d0e;
        font-size: 0.85rem;
        margin: 0;
    }

    /* Product List Item */
    .product-list-item {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.2s ease;
    }
    .product-list-item:hover {
        background: #f9fafb;
    }
    .product-list-item:last-child {
        border-bottom: none;
    }
    .product-list-item img {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        object-fit: cover;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .product-list-item .product-info {
        flex: 1;
        min-width: 0;
    }
    .product-list-item .product-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: #1f2937;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .product-list-item .product-meta {
        font-size: 0.75rem;
        color: #9ca3af;
    }
    .product-list-item .product-price {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.875rem;
        text-align: right;
        flex-shrink: 0;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state i {
        font-size: 2.5rem;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .empty-state p {
        color: #9ca3af;
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 767.98px) {
        .dashboard-container {
            padding: 12px;
        }
        .welcome-banner {
            padding: 20px;
            border-radius: 16px;
        }
        .welcome-banner h2 {
            font-size: 1.25rem;
        }
        .welcome-banner .d-flex {
            flex-direction: column;
            gap: 12px;
        }
        .stat-card .stat-value {
            font-size: 1.25rem;
        }
        .stat-card {
            padding: 16px;
        }
        .quote-alert {
            flex-direction: column;
            text-align: center;
        }
        .modern-table {
            font-size: 0.8rem;
        }
        .modern-table thead th,
        .modern-table tbody td {
            padding: 10px 12px;
        }
    }

    @media (max-width: 575.98px) {
        .stat-card .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        .stat-card .stat-value {
            font-size: 1.1rem;
        }
    }

    @media (min-width: 1200px) {
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3" style="position: relative; z-index: 1;">
            <div>
                <h2>Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="mb-0">{{ $store->name }} &mdash; {{ $store->category->name ?? 'Store' }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('vendor.products.create') }}" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Add {{ $productTerm }}
                </a>
                <a href="{{ route('store.profile', $store->slug) }}" class="btn btn-light" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>View Store
                </a>
            </div>
        </div>
    </div>

    <!-- Bulk Quote Alert (if pending) -->
    @if (($stats['pending_quotes'] ?? 0) > 0)
    <div class="quote-alert">
        <div class="quote-alert-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="quote-alert-content flex-grow-1">
            <h6>{{ $stats['pending_quotes'] }} Pending Quote {{ $stats['pending_quotes'] > 1 ? 'Requests' : 'Request' }}</h6>
            <p>Buyers are waiting for your price response. Respond quickly to close the deal!</p>
        </div>
        <a href="{{ route('vendor.bulk-quotes.index') }}" class="btn btn-warning btn-sm fw-bold" style="border-radius: 10px; white-space: nowrap;">
            <i class="fas fa-reply me-1"></i> Respond Now
        </a>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">{{ $productTermPlural }}</div>
                        <div class="stat-value">{{ $stats['total_products'] }}</div>
                        <div class="stat-sub text-success"><i class="fas fa-circle" style="font-size: 6px; vertical-align: middle;"></i> {{ $stats['active_products'] }} active</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Orders</div>
                        <div class="stat-value">{{ $stats['total_orders'] }}</div>
                        <div class="stat-sub text-warning"><i class="fas fa-clock" style="font-size: 8px;"></i> {{ $stats['pending_orders'] }} pending</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Revenue</div>
                        <div class="stat-value">RM {{ number_format($stats['total_revenue'], 0) }}</div>
                        <div class="stat-sub text-muted">All time</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">This Month</div>
                        <div class="stat-value">RM {{ number_format($stats['monthly_revenue'], 0) }}</div>
                        <div class="stat-sub text-muted">{{ now()->format('M Y') }}</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Members</div>
                        <div class="stat-value">{{ $stats['total_members'] }}</div>
                        <div class="stat-sub text-success">+{{ $stats['new_members_this_month'] }} this month</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Bulk Quotes</div>
                        <div class="stat-value">{{ $stats['total_quotes'] ?? 0 }}</div>
                        <div class="stat-sub text-warning">{{ $stats['pending_quotes'] ?? 0 }} pending</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(236,72,153,0.1); color: #ec4899;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Get Started (only if no products) -->
    @if ($stats['total_products'] == 0)
    <div class="quick-action-card">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #0ea5e9; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; flex-shrink: 0;">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1">Get Started!</h5>
                <p class="mb-2">You haven't added any {{ $productTermPluralLower }} yet. Start by adding your first {{ $productTermLower }}.</p>
                <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm" style="border-radius: 10px;">
                    <i class="fas fa-plus me-2"></i>Add Your First {{ $productTerm }}
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-12 col-lg-8">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-shopping-bag me-2 text-primary"></i>Recent Orders</h5>
                    <a href="{{ route('vendor.orders.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.8rem;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if ($recentOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th class="d-none d-md-table-cell">Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="d-none d-sm-table-cell">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('vendor.orders.show', $order) }}" class="text-decoration-none fw-semibold text-primary">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 120px;">{{ $order->user->name }}</span>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $order->items->count() }}</td>
                                    <td class="fw-semibold">RM {{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge badge-modern bg-{{ $order->status_color }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        @if (($order->order_type ?? 'retail') !== 'retail')
                                        <span class="badge badge-modern bg-{{ $order->order_type === 'bulk' ? 'warning' : 'info' }} ms-1">
                                            {{ ucfirst($order->order_type) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $order->created_at->format('M d') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart d-block"></i>
                        <p>No orders yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-12 col-lg-4">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-fire me-2" style="color: #f59e0b;"></i>Top {{ $productTermPlural }}</h5>
                    <a href="{{ route('vendor.products.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.8rem;">
                        All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if ($topProducts->count() > 0)
                        @foreach ($topProducts as $product)
                        <div class="product-list-item">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                            <div class="product-info">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-meta">{{ $product->sold_count }} sold</div>
                            </div>
                            <div class="product-price">RM {{ number_format($product->regular_price, 2) }}</div>
                        </div>
                        @endforeach
                    @else
                    <div class="empty-state">
                        <i class="fas fa-box d-block"></i>
                        <p class="mb-3">No {{ $productTermPluralLower }} yet</p>
                        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm" style="border-radius: 10px;">
                            Add {{ $productTerm }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Store Members -->
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-users me-2" style="color: #8b5cf6;"></i>Store Members</h5>
                    <a href="{{ route('vendor.members.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-size: 0.8rem;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if ($recentMembers->count() > 0)
                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Join Method</th>
                                    <th class="d-none d-sm-table-cell">Joined</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentMembers as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($member->customer && $member->customer->avatar)
                                            <img src="{{ $member->customer->avatar }}" alt="" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: rgba(139,92,246,0.1);">
                                                <i class="fas fa-user" style="color: #8b5cf6; font-size: 0.7rem;"></i>
                                            </div>
                                            @endif
                                            <span class="fw-medium">{{ $member->customer->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted">{{ $member->customer->email ?? '-' }}</td>
                                    <td>
                                        @if($member->join_method === 'qr_scan')
                                        <span class="badge badge-modern bg-info"><i class="fas fa-qrcode me-1"></i>QR</span>
                                        @elseif($member->join_method === 'referral')
                                        <span class="badge badge-modern bg-success"><i class="fas fa-link me-1"></i>Referral</span>
                                        @else
                                        <span class="badge badge-modern bg-secondary">{{ ucfirst($member->join_method ?? 'Direct') }}</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $member->joined_at ? $member->joined_at->format('M d, Y') : '-' }}</td>
                                    <td>
                                        <span class="badge badge-modern bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($member->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="empty-state">
                        <i class="fas fa-users d-block"></i>
                        <p class="mb-1">No members yet</p>
                        <small class="text-muted">Share your Member QR code to start recruiting customers!</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
