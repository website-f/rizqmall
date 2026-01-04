@extends('partials.admin')

@section('title', 'My Stores')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Stores</h2>
            <p class="text-muted">Manage your stores and quota</p>
        </div>
        @if($canAddStore)
            <a href="{{ route('store.select-category') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Store
            </a>
        @else
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#purchaseQuotaModal">
                <i class="fas fa-shopping-cart me-2"></i>Purchase Additional Store Slot
            </button>
        @endif
    </div>

    <!-- Quota Information -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="mb-1">Store Quota: {{ $currentStoreCount }} / {{ $storesQuota }}</h5>
                <p class="mb-1">
                    <strong>Base Quota:</strong> {{ $baseQuota }} store(s) (from RizqMall subscription)<br>
                    @if($additionalSlots > 0)
                        <strong>Additional Slots:</strong> {{ $additionalSlots }} store(s) (purchased)
                    @endif
                </p>
                <p class="mb-0">
                    @if($canAddStore)
                        You can create {{ $storesQuota - $currentStoreCount }} more store(s).
                    @else
                        You've reached your store limit. Purchase additional slots to create more stores.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Active Purchases -->
    @if($activePurchases->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Active Additional Store Slots</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Purchase Date</th>
                                <th>Slots</th>
                                <th>Amount</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activePurchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->payment_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-primary">{{ $purchase->store_slots_purchased }} slot(s)</span></td>
                                    <td>RM {{ number_format($purchase->amount, 2) }}</td>
                                    <td>{{ $purchase->expires_at ? $purchase->expires_at->format('d M Y') : 'N/A' }}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Stores List -->
    <div class="row g-4">
        @forelse($stores as $store)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    @if($store->banner_url)
                        <img src="{{ $store->banner_url }}" class="card-img-top" alt="{{ $store->name }}" style="height: 150px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fas fa-store fa-3x text-muted"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            @if($store->logo_url)
                                <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-0">{{ $store->name }}</h5>
                                <small class="text-muted">{{ $store->category->name ?? 'N/A' }}</small>
                            </div>
                        </div>

                        <p class="card-text text-muted small mb-3">{{ Str::limit($store->description, 100) }}</p>

                        <div class="d-flex gap-2">
                            <a href="{{ route('vendor.dashboard') }}" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('vendor.store.edit') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-box me-1"></i>{{ $store->products()->count() }} Products
                            <span class="ms-3">
                                <i class="fas fa-shopping-bag me-1"></i>{{ $store->orders()->count() }} Orders
                            </span>
                        </small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="fas fa-store fa-3x mb-3"></i>
                    <h4>No stores yet</h4>
                    <p>Create your first store to start selling on RizqMall</p>
                    <a href="{{ route('store.select-category') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Your First Store
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Purchase Additional Quota Modal -->
<div class="modal fade" id="purchaseQuotaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('vendor.store-purchase.create') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Additional Store Slot(s)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Each additional store slot costs <strong>RM20/month</strong> (same as RizqMall subscription)
                    </div>

                    <div class="mb-3">
                        <p><strong>Current Quota:</strong> {{ $storesQuota }} store(s) ({{ $baseQuota }} base + {{ $additionalSlots }} purchased)</p>
                        <p><strong>Used:</strong> {{ $currentStoreCount }} store(s)</p>
                        <p><strong>Available:</strong> {{ $storesQuota - $currentStoreCount }} store(s)</p>
                    </div>

                    <div class="mb-3">
                        <label for="slots" class="form-label">Number of Additional Slots to Purchase</label>
                        <select name="slots" id="slots" class="form-select" required onchange="updateTotal()">
                            <option value="1">1 slot - RM20.00/month</option>
                            <option value="2">2 slots - RM40.00/month</option>
                            <option value="3">3 slots - RM60.00/month</option>
                            <option value="4">4 slots - RM80.00/month</option>
                            <option value="5">5 slots - RM100.00/month</option>
                        </select>
                        <small class="text-muted">Base price per slot: RM20/month</small>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Payment Breakdown</h6>
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td>Subtotal (<span id="slotsCount">1</span> slot × RM20):</td>
                                        <td class="text-end"><strong>RM <span id="subtotal">20.00</span></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (8%):</td>
                                        <td class="text-end">RM <span id="taxAmount">1.60</span></td>
                                    </tr>
                                    <tr>
                                        <td>FPX Charge:</td>
                                        <td class="text-end">RM 1.00</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Total Payment:</strong></td>
                                        <td class="text-end"><strong>RM <span id="totalAmount">22.60</span></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <small>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Additional store slots are billed monthly. You will be redirected to ToyyibPay to complete the payment.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTotal() {
    const slots = parseInt(document.getElementById('slots').value);
    const pricePerSlot = 20; // RM20 per slot

    // Calculate subtotal
    const subtotal = slots * pricePerSlot;

    // Calculate tax (8%)
    const tax = subtotal * 0.08;

    // FPX charge is fixed at RM1.00
    const fpx = 1.00;

    // Calculate total
    const total = subtotal + tax + fpx;

    // Update display
    document.getElementById('slotsCount').textContent = slots;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = tax.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}
</script>
@endsection
