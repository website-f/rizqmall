@extends('partials.admin')

@section('title', 'Analytics - RizqMall')

@section('content')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Analytics</h2>
                        <p class="text-muted mb-0">Track your store's performance and insights</p>
                    </div>
                    <div>
                        <select class="form-select" id="dateRange">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Revenue</p>
                                <h3 class="mb-0">RM {{ number_format($analytics['total_revenue'] ?? 0, 2) }}</h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $analytics['revenue_growth'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-dollar-sign text-success fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Total Orders</p>
                                <h3 class="mb-0">{{ $analytics['total_orders'] ?? 0 }}</h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $analytics['orders_growth'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-shopping-cart text-primary fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Avg Order Value</p>
                                <h3 class="mb-0">RM {{ number_format($analytics['avg_order_value'] ?? 0, 2) }}</h3>
                                <small class="text-info">
                                    <i class="fas fa-minus"></i> {{ $analytics['aov_change'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-chart-line text-info fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 small">Conversion Rate</p>
                                <h3 class="mb-0">{{ $analytics['conversion_rate'] ?? 0 }}%</h3>
                                <small class="text-success">
                                    <i class="fas fa-arrow-up"></i> {{ $analytics['conversion_growth'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-percentage text-warning fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Revenue Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Revenue Overview</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders by Status -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Orders by Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Analytics -->
        <div class="row g-4 mb-4">
            <!-- Top Products -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Top Selling Products</h5>
                    </div>
                    <div class="card-body p-0">
                        @if (isset($topProducts) && $topProducts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Sales</th>
                                            <th>Revenue</th>
                                            <th>Trend</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topProducts as $product)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                            class="rounded me-2"
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                        <div>
                                                            <div class="fw-medium">{{ Str::limit($product->name, 30) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $product->total_sales ?? 0 }} units</td>
                                                <td class="fw-semibold">RM
                                                    {{ number_format($product->total_revenue ?? 0, 2) }}</td>
                                                <td>
                                                    <span class="text-success">
                                                        <i class="fas fa-arrow-up"></i> {{ $product->growth ?? 0 }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No sales data available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Traffic Sources -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Customer Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">New Customers</span>
                                <span class="fw-semibold">{{ $analytics['new_customers'] ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                    style="width: {{ $analytics['new_customers_percent'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Returning Customers</span>
                                <span class="fw-semibold">{{ $analytics['returning_customers'] ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $analytics['returning_customers_percent'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Visitors</span>
                                <span class="fw-semibold">{{ $analytics['total_visitors'] ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Performance Metrics</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-primary">{{ $analytics['page_views'] ?? 0 }}</h4>
                                    <small class="text-muted">Page Views</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-success">{{ $analytics['avg_session'] ?? '0m' }}</h4>
                                    <small class="text-muted">Avg Session</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h4 class="mb-0 text-warning">{{ $analytics['bounce_rate'] ?? 0 }}%</h4>
                                    <small class="text-muted">Bounce Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @if (isset($recentActivity) && count($recentActivity) > 0)
                                @foreach ($recentActivity as $activity)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div
                                                class="timeline-icon bg-{{ $activity['color'] ?? 'primary' }} bg-opacity-10 p-2 rounded-circle me-3">
                                                <i
                                                    class="fas fa-{{ $activity['icon'] ?? 'circle' }} text-{{ $activity['color'] ?? 'primary' }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <h6 class="mb-1">{{ $activity['title'] ?? 'Activity' }}</h6>
                                                    <small
                                                        class="text-muted">{{ $activity['time'] ?? 'Just now' }}</small>
                                                </div>
                                                <p class="text-muted mb-0 small">{{ $activity['description'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No recent activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($analytics['revenue_labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
                    datasets: [{
                        label: 'Revenue (RM)',
                        data: {!! json_encode($analytics['revenue_data'] ?? [1200, 1900, 3000, 5000, 4200, 3800]) !!},
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'RM ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Processing', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: {!! json_encode($analytics['status_data'] ?? [65, 20, 10, 5]) !!},
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Date Range Change Handler
        document.getElementById('dateRange').addEventListener('change', function() {
            // Reload page with new date range
            window.location.href = '?days=' + this.value;
        });
    </script>
@endpush
