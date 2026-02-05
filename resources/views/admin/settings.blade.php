@extends('partials.admin')

@section('title', 'Platform Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="mb-1">Platform Pricing Settings</h3>
                    <p class="text-muted mb-0">Manage tax and shipping fees for the entire marketplace.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>Please fix the highlighted fields.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Tax Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tax Rate (Percent)</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        name="tax_rate_percent"
                                        class="form-control @error('tax_rate_percent') is-invalid @enderror"
                                        value="{{ old('tax_rate_percent', number_format($taxRatePercent, 2, '.', '')) }}"
                                        required>
                                    <span class="input-group-text">%</span>
                                    @error('tax_rate_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Example: 6 = 6% SST.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Shipping Fees (RM)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Standard Delivery</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="shipping_standard"
                                    class="form-control @error('shipping_standard') is-invalid @enderror"
                                    value="{{ old('shipping_standard', number_format($shippingStandard, 2, '.', '')) }}"
                                    required>
                                @error('shipping_standard')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Express Delivery</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="shipping_express"
                                    class="form-control @error('shipping_express') is-invalid @enderror"
                                    value="{{ old('shipping_express', number_format($shippingExpress, 2, '.', '')) }}"
                                    required>
                                @error('shipping_express')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Self Pickup</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="shipping_pickup"
                                    class="form-control @error('shipping_pickup') is-invalid @enderror"
                                    value="{{ old('shipping_pickup', number_format($shippingPickup, 2, '.', '')) }}"
                                    required>
                                @error('shipping_pickup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Fees apply to all stores unless a store uses pickup-only service items.
                        </small>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
