@extends('partials.admin')

@section('title', 'Store Members - RizqMall')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Store Members</h2>
                    <p class="text-muted mb-0">Manage customers who joined as members of {{ $store->name }}</p>
                </div>
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Members</p>
                            <h3 class="mb-0">{{ $stats['total_members'] }}</h3>
                        </div>
                        <div class="p-3 rounded" style="background-color: rgba(139, 92, 246, 0.1);">
                            <i class="fas fa-users fa-lg" style="color: #8b5cf6;"></i>
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
                            <p class="text-muted mb-1 small">New This Month</p>
                            <h3 class="mb-0">{{ $stats['new_this_month'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-plus text-success fa-lg"></i>
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
                            <p class="text-muted mb-1 small">Via QR Scan</p>
                            <h3 class="mb-0">{{ $stats['qr_scans'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-qrcode text-info fa-lg"></i>
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
                            <p class="text-muted mb-1 small">Via Referral</p>
                            <h3 class="mb-0">{{ $stats['referrals'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-link text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.members.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Active Only</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Join Method</label>
                    <select name="join_method" class="form-select">
                        <option value="">All Methods</option>
                        <option value="qr_scan" {{ request('join_method') === 'qr_scan' ? 'selected' : '' }}>QR Scan</option>
                        <option value="referral" {{ request('join_method') === 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="store_page" {{ request('join_method') === 'store_page' ? 'selected' : '' }}>Store Page</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="{{ route('vendor.members.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Members Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">Members List</h5>
        </div>
        <div class="card-body p-0">
            @if ($members->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Join Method</th>
                            <th>Joined Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($member->customer && $member->customer->avatar)
                                    <img src="{{ $member->customer->avatar }}" alt="{{ $member->customer->name ?? 'Member' }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    @endif
                                    <span class="fw-semibold">{{ $member->customer->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td>{{ $member->customer->email ?? '-' }}</td>
                            <td>{{ $member->customer->phone ?? '-' }}</td>
                            <td>
                                @if($member->join_method === 'qr_scan')
                                <span class="badge bg-info">
                                    <i class="fas fa-qrcode me-1"></i>QR Scan
                                </span>
                                @elseif($member->join_method === 'referral' || $member->join_method === 'ref_code')
                                <span class="badge bg-success">
                                    <i class="fas fa-link me-1"></i>Referral
                                </span>
                                @elseif($member->join_method === 'store_page')
                                <span class="badge bg-primary">
                                    <i class="fas fa-store me-1"></i>Store Page
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-user-plus me-1"></i>{{ ucfirst($member->join_method ?? 'Direct') }}
                                </span>
                                @endif
                            </td>
                            <td>{{ $member->joined_at ? $member->joined_at->format('M d, Y H:i') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center p-3">
                {{ $members->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Members Found</h5>
                <p class="text-muted mb-3">
                    @if(request()->hasAny(['search', 'status', 'join_method', 'from_date', 'to_date']))
                    No members match your filter criteria.
                    @else
                    You don't have any members yet. Share your Member QR code to start recruiting customers!
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status', 'join_method', 'from_date', 'to_date']))
                <a href="{{ route('vendor.members.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-times me-2"></i>Clear Filters
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection