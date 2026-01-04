@extends('partials.admin')

@section('title', 'Edit ' . $product->name)

@section('content')
<style>
    .product-form-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .card-title {
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 2px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    /* Image Upload */
    .image-upload-zone {
        border: 3px dashed #d1d5db;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f9fafb;
        cursor: pointer;
    }

    .image-upload-zone:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
        margin-top: 20px;
    }

    .image-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 28px;
        height: 28px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 14px;
    }

    .image-primary-badge {
        position: absolute;
        bottom: 4px;
        left: 4px;
        background: #10b981;
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    /* Product Type Toggle */
    .product-type-toggle {
        display: inline-flex;
        background: #f3f4f6;
        border-radius: 10px;
        padding: 4px;
    }

    .product-type-toggle input[type="radio"] {
        display: none;
    }

    .product-type-toggle label {
        padding: 10px 24px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 0;
        font-weight: 600;
    }

    .product-type-toggle input:checked+label {
        background: white;
        color: #3b82f6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Variants */
    .variant-builder {
        background: #f9fafb;
        border-radius: 12px;
        padding: 24px;
    }

    .variant-type-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .variant-option-btn {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 6px;
        display: inline-block;
    }

    .variant-option-btn:hover {
        border-color: #3b82f6;
    }

    .variant-option-btn.selected {
        border-color: #3b82f6;
        background: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
    }

    /* Buttons */
    .btn {
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
    }
</style>

<div class="product-form-container">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vendor.products.index') }}">Products</a></li>
            <li class="breadcrumb-item active">Edit {{ $product->name }}</li>
        </ol>
    </nav>

    @if (session('success'))
    <div class="alert alert-success border-0 mb-4">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger border-0 mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger border-0 mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('products.update', ['store' => $store->id, 'product' => $product->id]) }}" method="POST" enctype="multipart/form-data"
        id="productForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="{{ $type }}">

        <!-- Header -->
        <div class="row g-3 mb-5">
            <div class="col-auto">
                <h2 class="mb-2 fw-bold">Edit {{ ucfirst($type) }}</h2>
                <h5 class="text-body-tertiary">Store: <strong class="text-primary">{{ $store->name }}</strong></h5>
            </div>
            <div class="col-auto ms-auto">
                <button class="btn btn-outline-secondary me-2" type="button" onclick="window.history.back()">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save me-1"></i> Update {{ ucfirst($type) }}
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-xl-8">

                <!-- Basic Information -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-info-circle"></i> Basic Information
                        </h4>

                        <div class="mb-4">
                            <label class="form-label">{{ ucfirst($type) }} Name <span
                                    class="text-danger">*</span></label>
                            <input class="form-control form-control-lg" type="text" name="name"
                                placeholder="e.g., {{ $type === 'product' ? 'Wireless Headphones' : ($type === 'service' ? 'House Cleaning Service' : 'Paracetamol 500mg') }}"
                                value="{{ old('name', $product->name ?? '') }}" required />
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Short Description</label>
                            <input class="form-control" type="text" name="short_description"
                                placeholder="Brief summary (shown in listings)" value="{{ old('short_description', $product->short_description ?? '') }}"
                                maxlength="500" />
                        </div>

                        <div>
                            <label class="form-label">Full Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="8" placeholder="Detailed description..." required>{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="fas fa-images"></i> {{ ucfirst($type) }} Images
                        </h4>
                        <p class="text-muted mb-4">Upload up to 10 images. First image will be the main image.</p>

                        <!-- Existing Images -->
                        @if ($product->images && $product->images->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Current Images</h6>
                            <div class="row g-3">
                                @foreach ($product->images as $index => $image)
                                <div class="col-md-3">
                                    <div class="card border shadow-sm">
                                        <img src="{{ $image->url }}" alt="{{ $product->name }}"
                                            class="card-img-top" style="height: 200px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            @if ($index === 0)
                                            <span class="badge bg-primary w-100">
                                                <i class="fas fa-star me-1"></i> Primary Image
                                            </span>
                                            @else
                                            <span class="badge bg-secondary w-100">Image {{ $index + 1 }}</span>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-danger w-100 mt-2"
                                                onclick="deleteImage({{ $image->id }}, this)">
                                                <i class="fas fa-trash me-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <hr class="my-4">
                        <h6 class="text-muted mb-3">Add More Images</h6>
                        @endif

                        <div class="image-upload-zone" id="imageUploadZone">
                            <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                                class="d-none" />
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5>Drag & Drop Images Here</h5>
                            <p class="text-muted mb-3">or</p>
                            <button type="button" class="btn btn-outline-primary"
                                onclick="document.getElementById('imageInput').click()">
                                <i class="fas fa-folder-open me-2"></i> Browse Files
                            </button>
                            <div class="form-text mt-3">Supported: JPG, PNG, WEBP (Max 5MB each)</div>
                        </div>

                        <div id="imagePreviewGrid" class="image-preview-grid" style="display: none;"></div>
                    </div>
                </div>

                <!-- Product Type (only for products and pharmacy) -->
                @if (in_array($type, ['product', 'pharmacy']))
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-layer-group"></i> {{ ucfirst($type) }} Type
                        </h4>

                        <div class="product-type-toggle">
                            <input type="radio" name="product_type" id="typeSimple" value="simple"
                                {{ old('product_type', $product->product_type) === 'simple' ? 'checked' : '' }} />
                            <label for="typeSimple">
                                <i class="fas fa-box me-2"></i> Simple
                            </label>

                            <input type="radio" name="product_type" id="typeVariable" value="variable"
                                {{ old('product_type', $product->product_type) === 'variable' ? 'checked' : '' }} />
                            <label for="typeVariable">
                                <i class="fas fa-layer-group me-2"></i> Variable
                            </label>
                        </div>

                        <div class="form-text mt-3">
                            Simple: Single item without variations. Variable: Multiple options like size, color,
                            etc.
                        </div>
                    </div>
                </div>
                @else
                <input type="hidden" name="product_type" value="simple">
                @endif

                <!-- Pricing & Inventory -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-dollar-sign"></i> Pricing & Inventory
                        </h4>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Regular Price (RM) <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="number" step="0.01" name="regular_price"
                                    placeholder="99.00" value="{{ old('regular_price', $product->regular_price ?? '') }}" required />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sale Price (RM)</label>
                                <input class="form-control" type="number" step="0.01" name="sale_price"
                                    placeholder="79.00" value="{{ old('sale_price', $product->sale_price ?? '') }}" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cost Price (RM)</label>
                                <input class="form-control" type="number" step="0.01" name="cost_price"
                                    placeholder="50.00" value="{{ old('cost_price') }}" />
                            </div>
                        </div>

                        @if ($type !== 'service')
                        <div class="row g-3 mt-2" id="simpleInventory">
                            <div class="col-md-4">
                                <label class="form-label">SKU</label>
                                <input class="form-control" type="text" name="sku"
                                    placeholder="Auto-generated" value="{{ old('sku') }}" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Quantity <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="stock_quantity"
                                    placeholder="100"
                                    value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Alert</label>
                                <input class="form-control" type="number" name="low_stock_threshold"
                                    placeholder="5" value="{{ old('low_stock_threshold', 5) }}" />
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowBackorder"
                                    name="allow_backorder" value="1"
                                    {{ old('allow_backorder', $product->allow_backorder) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowBackorder">
                                    Allow backorders (sell when out of stock)
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Variant Builder -->
                @if (in_array($type, ['product', 'pharmacy']))
                <div class="card" id="variantBuilder" style="display: none;">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-sliders-h"></i> Product Variants
                        </h4>

                        <div id="variantTypesContainer"></div>

                        <button type="button" class="btn btn-primary mt-3" id="generateVariantsBtn">
                            <i class="fas fa-magic me-2"></i> Generate Variants
                        </button>

                        <div id="variantsTableContainer" style="display: none;" class="mt-4">
                            <h5 class="mb-3">Manage Variants</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Variant</th>
                                            <th>SKU</th>
                                            <th>Price (RM)</th>
                                            <th>Sale Price</th>
                                            <th>Stock</th>
                                            <th>Image</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantsTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Type-Specific Fields -->
                @if ($type === 'product')
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-box-open"></i> Product Attributes
                        </h4>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Weight (kg)</label>
                                <input class="form-control" type="number" step="0.01" name="weight"
                                    placeholder="0.5" value="{{ old('weight', $product->weight) }}" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Length (cm)</label>
                                <input class="form-control" type="number" step="0.01" name="length"
                                    placeholder="20" value="{{ old('length', $product->length) }}" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Width (cm)</label>
                                <input class="form-control" type="number" step="0.01" name="width"
                                    placeholder="15" value="{{ old('width', $product->width) }}" />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Height (cm)</label>
                                <input class="form-control" type="number" step="0.01" name="height"
                                    placeholder="10" value="{{ old('height', $product->height) }}" />
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="fragile"
                                        name="is_fragile" value="1"
                                        {{ old('is_fragile', $product->is_fragile) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="fragile">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i> Fragile
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="biodegradable"
                                        name="is_biodegradable" value="1"
                                        {{ old('is_biodegradable', $product->is_biodegradable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="biodegradable">
                                        <i class="fas fa-leaf text-success me-2"></i> Biodegradable
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="frozen"
                                        name="is_frozen" value="1"
                                        {{ old('is_frozen', $product->is_frozen) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="frozen">
                                        <i class="fas fa-snowflake text-info me-2"></i> Frozen
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6" id="tempField" style="display: none;">
                                <label class="form-label">Max Temperature</label>
                                <input class="form-control" type="text" name="max_temperature"
                                    placeholder="-18°C" value="{{ old('max_temperature') }}" />
                            </div>
                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="hasExpiry"
                                        name="has_expiry" value="1">
                                    <label class="form-check-label" for="hasExpiry">Has Expiry Date</label>
                                </div>
                            </div>
                            <div class="col-md-3" id="expiryField" style="display: none;">
                                <label class="form-label">Expiry Date</label>
                                <input class="form-control" type="date" name="expiry_date"
                                    value="{{ old('expiry_date') }}" />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($type === 'service')
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-concierge-bell"></i> Service Details
                        </h4>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Service Duration (minutes) <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="service_duration"
                                    placeholder="60" value="{{ old('service_duration') }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Availability <span class="text-danger">*</span></label>
                                <select class="form-select" name="service_availability" required>
                                    <option value="instant">Instant Booking</option>
                                    <option value="scheduled">Scheduled Only</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Available Days</label>
                            <div class="row g-2">
                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <div class="col-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="service_days[]"
                                            value="{{ $day }}" id="day{{ $day }}">
                                        <label class="form-check-label"
                                            for="day{{ $day }}">{{ $day }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input class="form-control" type="time" name="service_start_time"
                                    value="{{ old('service_start_time', '09:00') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Time</label>
                                <input class="form-control" type="time" name="service_end_time"
                                    value="{{ old('service_end_time', '18:00') }}" />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if ($type === 'pharmacy')
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-pills"></i> Pharmacy Details
                        </h4>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requiresPrescription"
                                    name="requires_prescription" value="1">
                                <label class="form-check-label fw-bold text-danger" for="requiresPrescription">
                                    <i class="fas fa-prescription me-2"></i> Requires Prescription
                                </label>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Drug Code</label>
                                <input class="form-control" type="text" name="drug_code"
                                    placeholder="e.g., MAL19980001A" value="{{ old('drug_code') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Manufacturer</label>
                                <input class="form-control" type="text" name="manufacturer"
                                    placeholder="e.g., Pfizer" value="{{ old('manufacturer') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Active Ingredient</label>
                                <input class="form-control" type="text" name="active_ingredient"
                                    placeholder="e.g., Paracetamol" value="{{ old('active_ingredient') }}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dosage Form</label>
                                <select class="form-select" name="dosage_form">
                                    <option value="">Select Form</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="capsule">Capsule</option>
                                    <option value="syrup">Syrup</option>
                                    <option value="injection">Injection</option>
                                    <option value="cream">Cream/Ointment</option>
                                    <option value="drops">Drops</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Strength</label>
                                <input class="form-control" type="text" name="strength"
                                    placeholder="e.g., 500mg" value="{{ old('strength') }}" />
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pharmacyExpiry"
                                        name="has_expiry" value="1" checked>
                                    <label class="form-check-label" for="pharmacyExpiry">
                                        Has Expiry Date <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="expiry_date"
                                    value="{{ old('expiry_date') }}" required />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Specifications -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i> Specifications
                            </h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSpecBtn">
                                <i class="fas fa-plus me-1"></i> Add Specification
                            </button>
                        </div>
                        <div id="specificationsContainer">
                            @foreach ($product->specifications as $index => $spec)
                            <div class="row g-2 mb-2 align-items-center">
                                <div class="col-md-3">
                                    <input type="text" class="form-control form-control-sm"
                                        name="specifications[{{ $index }}][key]" placeholder="Key (e.g., Display)"
                                        value="{{ $spec->spec_key }}" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm"
                                        name="specifications[{{ $index }}][value]"
                                        placeholder="Value (e.g., 6.1-inch OLED)" value="{{ $spec->spec_value }}"
                                        required>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm"
                                        name="specifications[{{ $index }}][group]">
                                        <option value="General"
                                            {{ $spec->spec_group == 'General' ? 'selected' : '' }}>General
                                        </option>
                                        <option value="Technical"
                                            {{ $spec->spec_group == 'Technical' ? 'selected' : '' }}>Technical
                                        </option>
                                        <option value="Physical"
                                            {{ $spec->spec_group == 'Physical' ? 'selected' : '' }}>Physical
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="this.closest('.row').remove()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="col-12 col-xl-4">

                <!-- Category & Tags -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-folder me-2"></i> Organization
                        </h5>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="product_category_id">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('product_category_id', $product->product_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Tags</label>
                            <select class="form-select" name="tags[]" multiple size="5">
                                @php
                                $selectedTags = old('tags', $product->tags->pluck('id')->toArray());
                                @endphp
                                @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}"
                                    {{ in_array($tag->id, $selectedTags) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-search me-2"></i> SEO
                        </h5>
                        <div class="mb-3">
                            <label class="form-label">Meta Title</label>
                            <input class="form-control" type="text" name="meta_title"
                                placeholder="Leave blank to use {{ $type }} name"
                                value="{{ old('meta_title', $product->meta_title) }}" maxlength="255" />
                        </div>
                        <div>
                            <label class="form-label">Meta Description</label>
                            <textarea class="form-control" name="meta_description" rows="3"
                                placeholder="Brief description for search engines" maxlength="500">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-lightbulb me-2"></i> Quick Tips
                        </h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Use high-quality images (min 1000x1000px)</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Write detailed descriptions for better visibility</small>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Set competitive pricing</small>
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <small>Use variants for different options</small>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    const variantTypes = @json($variantTypes);
    const productType = '{{ $type }}';

    document.addEventListener('DOMContentLoaded', function() {
        // ========== Image Upload & Preview ==========
        const imageInput = document.getElementById('imageInput');
        const imageUploadZone = document.getElementById('imageUploadZone');
        const imagePreviewGrid = document.getElementById('imagePreviewGrid');
        let selectedFiles = [];

        imageInput.addEventListener('change', handleFileSelect);

        imageUploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUploadZone.style.borderColor = '#3b82f6';
            imageUploadZone.style.background = '#eff6ff';
        });

        imageUploadZone.addEventListener('dragleave', () => {
            imageUploadZone.style.borderColor = '#d1d5db';
            imageUploadZone.style.background = '#f9fafb';
        });

        imageUploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUploadZone.style.borderColor = '#d1d5db';
            imageUploadZone.style.background = '#f9fafb';
            const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            addFiles(files);
        });

        function handleFileSelect(e) {
            addFiles(Array.from(e.target.files));
        }

        function addFiles(files) {
            files.forEach(file => {
                if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                    selectedFiles.push(file);
                }
            });
            renderPreviews();
        }

        function renderPreviews() {
            if (selectedFiles.length === 0) {
                imagePreviewGrid.style.display = 'none';
                return;
            }

            imagePreviewGrid.style.display = 'grid';
            imagePreviewGrid.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                    <img src="${e.target.result}" alt="${file.name}">
                    <button type="button" class="image-remove-btn" onclick="removeImage(${index})">×</button>
                    ${index === 0 ? '<span class="image-primary-badge">Primary</span>' : ''}
                `;
                    imagePreviewGrid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });

            updateFileInput();
        }

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }

        window.removeImage = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
        };

        // ========== Product Type Toggle ==========
        const typeSimple = document.getElementById('typeSimple');
        const typeVariable = document.getElementById('typeVariable');
        const simpleInventory = document.getElementById('simpleInventory');
        const variantBuilder = document.getElementById('variantBuilder');

        if (typeSimple && typeVariable) {
            typeSimple.addEventListener('change', function() {
                if (this.checked) {
                    if (simpleInventory) simpleInventory.style.display = 'flex';
                    if (variantBuilder) variantBuilder.style.display = 'none';
                }
            });

            typeVariable.addEventListener('change', function() {
                if (this.checked) {
                    if (simpleInventory) simpleInventory.style.display = 'none';
                    if (variantBuilder) {
                        variantBuilder.style.display = 'block';
                        initVariantBuilder();
                    }
                }
            });
        }

        // ========== Variant Builder ==========
        let selectedVariantTypes = {};
        let generatedVariants = [];

        function initVariantBuilder() {
            const container = document.getElementById('variantTypesContainer');
            if (!container) return;

            container.innerHTML =
                '<p class="text-muted mb-3">Select variant types (e.g., Size, Color) and enter options separated by commas:</p>';

            variantTypes.forEach(type => {
                const card = document.createElement('div');
                card.className = 'variant-type-card';
                card.innerHTML = `
                <div class="form-check mb-3">
                    <input class="form-check-input variant-type-check" type="checkbox" 
                           id="varType${type.id}" value="${type.id}" data-name="${type.name}">
                    <label class="form-check-label fw-bold" for="varType${type.id}">
                        ${type.name}
                    </label>
                </div>
                <div id="varOptions${type.id}" style="display: none;">
                    <input type="text" class="form-control mb-2" 
                           placeholder="Enter options separated by commas (e.g., ${type.name === 'Size' ? 'S, M, L, XL' : type.name === 'Color' ? 'Red, Blue, Green, Yellow' : 'Option1, Option2, Option3'})"
                           id="varInput${type.id}">
                    <small class="text-muted">Example: ${type.name === 'Size' ? 'Small, Medium, Large' : type.name === 'Color' ? 'Red, Blue, Black' : 'Value1, Value2'}</small>
                </div>
            `;
                container.appendChild(card);

                document.getElementById(`varType${type.id}`).addEventListener('change', function() {
                    document.getElementById(`varOptions${type.id}`).style.display = this
                        .checked ? 'block' : 'none';
                    if (!this.checked) delete selectedVariantTypes[type.id];
                });
            });
        }

        const generateBtn = document.getElementById('generateVariantsBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', generateVariants);
        }

        function generateVariants() {
            selectedVariantTypes = {};

            variantTypes.forEach(type => {
                const checkbox = document.getElementById(`varType${type.id}`);
                if (checkbox && checkbox.checked) {
                    const input = document.getElementById(`varInput${type.id}`);
                    const options = input.value.split(',').map(o => o.trim()).filter(o => o);
                    if (options.length > 0) {
                        selectedVariantTypes[type.id] = {
                            name: type.name,
                            options: options
                        };
                    }
                }
            });

            if (Object.keys(selectedVariantTypes).length === 0) {
                alert('Please select at least one variant type and add options');
                return;
            }

            const combinations = cartesianProduct(Object.values(selectedVariantTypes).map(t => t.options));
            generatedVariants = [];

            combinations.forEach((combo, index) => {
                const variantName = Array.isArray(combo) ? combo.join(' / ') : combo;
                const variantSku = 'VAR-' + Math.random().toString(36).substr(2, 8).toUpperCase();

                const options = {};
                const comboArray = Array.isArray(combo) ? combo : [combo];
                Object.keys(selectedVariantTypes).forEach((typeId, i) => {
                    options[typeId] = {
                        value: comboArray[i] || comboArray[0]
                    };
                });

                generatedVariants.push({
                    name: variantName,
                    sku: variantSku,
                    price: '',
                    sale_price: '',
                    stock: 0,
                    options: options
                });
            });

            renderVariantsTable();
            document.getElementById('variantsTableContainer').style.display = 'block';
        }

        function cartesianProduct(arrays) {
            return arrays.reduce((acc, curr) => {
                return acc.flatMap(a => curr.map(c => [...(Array.isArray(a) ? a : [a]), c]));
            }, [
                []
            ]);
        }

        function renderVariantsTable() {
            const tbody = document.getElementById('variantsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '';

            generatedVariants.forEach((variant, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>
                    <strong>${variant.name}</strong>
                    <input type="hidden" name="variants[${index}][name]" value="${variant.name}">
                    ${Object.entries(variant.options).map(([typeId, opt]) => 
                        `<input type="hidden" name="variants[${index}][options][${typeId}][value]" value="${opt.value}">`
                    ).join('')}
                </td>
                <td><input type="text" class="form-control form-control-sm" name="variants[${index}][sku]" value="${variant.sku}" required></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${index}][price]" placeholder="Optional"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="variants[${index}][sale_price]" placeholder="Optional"></td>
                <td><input type="number" class="form-control form-control-sm" name="variants[${index}][stock_quantity]" value="0" required></td>
                <td><input type="file" class="form-control form-control-sm" name="variants[${index}][image]" accept="image/*"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeVariant(${index})"><i class="fas fa-trash"></i></button></td>
            `;
                tbody.appendChild(row);
            });
        }

        window.removeVariant = function(index) {
            generatedVariants.splice(index, 1);
            renderVariantsTable();
        };

        // ========== Specifications ==========
        let specCount = {
            {
                $product - > specifications - > count()
            }
        };
        const addSpecBtn = document.getElementById('addSpecBtn');
        if (addSpecBtn) {
            addSpecBtn.addEventListener('click', addSpecification);
        }

        function addSpecification() {
            const container = document.getElementById('specificationsContainer');
            if (!container) return;

            const specRow = document.createElement('div');
            specRow.className = 'row g-2 mb-2 align-items-center';
            specRow.innerHTML = `
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="specifications[${specCount}][key]" 
                       placeholder="Key (e.g., Display)" required>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control form-control-sm" name="specifications[${specCount}][value]" 
                       placeholder="Value (e.g., 6.1-inch OLED)" required>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="specifications[${specCount}][group]">
                    <option value="General">General</option>
                    <option value="Technical">Technical</option>
                    <option value="Physical">Physical</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.row').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
            container.appendChild(specRow);
            specCount++;
        }

        // ========== Conditional Fields ==========
        const frozenCheckbox = document.getElementById('frozen');
        if (frozenCheckbox) {
            frozenCheckbox.addEventListener('change', function() {
                const tempField = document.getElementById('tempField');
                if (tempField) {
                    tempField.style.display = this.checked ? 'block' : 'none';
                }
            });
        }

        const hasExpiryCheckbox = document.getElementById('hasExpiry');
        if (hasExpiryCheckbox) {
            hasExpiryCheckbox.addEventListener('change', function() {
                const expiryField = document.getElementById('expiryField');
                if (expiryField) {
                    expiryField.style.display = this.checked ? 'block' : 'none';
                }
            });
        }

        // ========== Form Validation ==========
        const productForm = document.getElementById('productForm');
        if (productForm) {
            productForm.addEventListener('submit', function(e) {
                const productTypeRadio = document.querySelector('input[name="product_type"]:checked');

                // Check if variable product has variants
                if (productTypeRadio && productTypeRadio.value === 'variable' && generatedVariants
                    .length === 0) {
                    e.preventDefault();
                    alert('Please generate at least one variant for variable products.');
                    return false;
                }

                // Validate images (warning only)
                if (selectedFiles.length === 0) {
                    const confirmSubmit = confirm(
                        'No images uploaded. Do you want to continue without images?');
                    if (!confirmSubmit) {
                        e.preventDefault();
                        return false;
                    }
                }

                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Publishing...';
                }
            });
        }
    });

    // Delete existing product image
    function deleteImage(imageId, button) {
        if (!confirm('Are you sure you want to delete this image?')) {
            return;
        }

        const card = button.closest('.col-md-3');
        const deleteUrl = '/vendor/products/' + {
            {
                $product - > id
            }
        } + '/images/' + imageId;

        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

        fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the card with animation
                    card.style.transition = 'opacity 0.3s ease-out';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        // Check if no more images
                        const remainingImages = document.querySelectorAll('.col-md-3').length;
                        if (remainingImages === 0) {
                            location.reload(); // Reload to show upload zone without "Current Images" section
                        }
                    }, 300);
                } else {
                    alert(data.message || 'Failed to delete image');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-trash me-1"></i> Remove';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the image');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-trash me-1"></i> Remove';
            });
    }
</script>

@endsection