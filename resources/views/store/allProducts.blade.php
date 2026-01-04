@extends('partials.app')

@section('title', 'All Products - Rizqmall')

@section('content')
<style>
    /* Modern Filter Styles */
    .filter-sidebar {
        background: white;
        border-radius: 2px;
        padding: 20px;
        box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }

    .filter-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .filter-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .filter-sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .filter-sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f3f4f6;
    }

    .filter-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .clear-filters {
        color: #3b82f6;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }

    .clear-filters:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .filter-section {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .filter-section:last-child {
        border-bottom: none;
    }

    .filter-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-option:hover {
        padding-left: 4px;
    }

    .filter-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .filter-checkbox:checked {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .filter-label {
        font-size: 14px;
        color: #6b7280;
        cursor: pointer;
        user-select: none;
        flex: 1;
    }

    .filter-count {
        font-size: 12px;
        color: #9ca3af;
    }

    /* Price Range Inputs */
    .price-range-inputs {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: nowrap;
    }

    .price-input {
        flex: 1;
        width: 100%;
        min-width: 0;
        padding: 8px 10px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .price-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .price-apply-btn {
        padding: 10px 20px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .price-apply-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    /* Rating Stars */
    .rating-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        cursor: pointer;
    }

    .rating-filter:hover {
        opacity: 0.8;
    }

    /* Products Header */
    .products-header {
        background: white;
        border-radius: 2px;
        padding: 12px 20px;
        margin-bottom: 10px;
        box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .products-count {
        font-size: 16px;
        color: #6b7280;
        font-weight: 500;
    }

    .products-count strong {
        color: #1f2937;
        font-weight: 700;
    }

    .products-controls {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .view-toggle {
        display: flex;
        gap: 8px;
    }

    .view-btn {
        width: 40px;
        height: 40px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
    }

    .view-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .view-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .sort-select {
        padding: 10px 40px 10px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 12px center;
        appearance: none;
        transition: all 0.2s ease;
    }

    .sort-select:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Modern Product Card - Shopee Style */
    .product-card-modern {
        background: white;
        border-radius: 4px;
        overflow: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(0, 0, 0, 0.09);
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .product-card-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 1px 20px 0 rgba(0, 0, 0, 0.08);
        border-color: rgba(238, 77, 45, 0.4);
    }

    .product-image-wrapper {
        position: relative;
        aspect-ratio: 1;
        background: #f9fafb;
        overflow: hidden;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .product-card-modern:hover .product-image {
        transform: scale(1.05);
    }

    .product-badge {
        position: absolute;
        top: 0;
        left: 0;
        padding: 2px 4px;
        border-radius: 0;
        font-size: 10px;
        font-weight: 600;
        z-index: 2;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-sale {
        background: #ee4d2d;
        color: #fff;
    }

    .badge-new {
        background: #26aa99;
        color: #fff;
    }

    .product-wishlist {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        background: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .product-wishlist:hover {
        transform: scale(1.1);
        background: #fee2e2;
    }

    .product-wishlist.active {
        background: #ef4444;
        color: white;
    }

    .product-info {
        padding: 8px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-store {
        font-size: 11px;
        color: #767676;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 3px;
        text-transform: capitalize;
    }

    .product-store i {
        font-size: 10px;
    }

    .product-name {
        font-size: 14px;
        font-weight: 400;
        color: rgba(0, 0, 0, 0.87);
        line-height: 1.2;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 33px;
        text-overflow: ellipsis;
    }

    .product-name:hover {
        color: #ee4d2d;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .product-rating .stars {
        color: #fbbf24;
    }

    .product-rating .count {
        color: #9ca3af;
        font-weight: 600;
    }

    .product-variants-info {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .product-stock {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stock-in {
        color: #10b981;
    }

    .stock-out {
        color: #ef4444;
    }

    .product-pricing {
        margin-top: auto;
    }

    .product-price-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .product-price {
        font-size: 16px;
        font-weight: 500;
        color: #ee4d2d;
    }

    .product-old-price {
        font-size: 12px;
        color: rgba(0, 0, 0, 0.54);
        text-decoration: line-through;
        font-weight: 400;
    }

    .product-discount {
        background: rgba(255, 212, 36, 0.2);
        color: #ee4d2d;
        padding: 1px 4px;
        border-radius: 2px;
        font-size: 10px;
        font-weight: 600;
    }

    .product-sold {
        font-size: 10px;
        color: rgba(0, 0, 0, 0.54);
    }

    /* Mobile Filter Button */
    .mobile-filter-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        cursor: pointer;
        z-index: 1000;
        transition: all 0.3s ease;
    }

    .mobile-filter-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }

    /* Responsive */
    @media (max-width: 991px) {
        .filter-sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            width: 300px;
            height: 100vh;
            max-height: 100vh;
            z-index: 1050;
            transition: left 0.3s ease;
        }

        .filter-sidebar.show {
            left: 0;
        }

        .filter-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }

        .filter-backdrop.show {
            display: block;
        }

        .mobile-filter-btn {
            display: flex;
        }

        .products-header {
            flex-direction: column;
            align-items: stretch;
        }

        .products-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .sort-select {
            width: 100%;
        }
    }

    @media (max-width: 575px) {
        .product-card-modern {
            border-radius: 4px;
        }

        .product-info {
            padding: 6px;
        }

        .product-name {
            font-size: 12px;
            min-height: 29px;
            -webkit-line-clamp: 2;
        }

        .product-price {
            font-size: 14px;
        }

        .product-old-price {
            font-size: 10px;
        }

        .product-sold {
            font-size: 9px;
        }

        .product-store {
            font-size: 10px;
        }

        .product-discount {
            font-size: 9px;
            padding: 1px 3px;
        }

        .products-header {
            padding: 12px 16px;
        }

        .filter-sidebar {
            width: 280px;
        }
    }
</style>

<section class="py-3 py-md-4" style="background-color: #f5f5f5;">
    <div class="container-fluid px-2 px-md-4">
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">
                    @if (request('type'))
                    {{ ucfirst(request('type')) }}s
                    @elseif(request('category'))
                    Products
                    @else
                    All Products
                    @endif
                </li>
            </ol>
        </nav>

        <div class="row g-4">
            {{-- Filter Sidebar --}}
            <div class="col-lg-3">
                <div class="filter-sidebar" id="filterSidebar">
                    <div class="filter-header">
                        <h3 class="filter-title">
                            <i class="fas fa-sliders-h me-2"></i>Filters
                        </h3>
                        <a href="{{ route('products.index') }}" class="clear-filters">
                            Clear All
                        </a>
                    </div>

                    {{-- Category Filter --}}
                    <div class="filter-section">
                        <div class="filter-section-title">
                            <i class="fas fa-folder text-primary"></i>
                            Category
                        </div>
                        <form method="GET" action="{{ route('products.index') }}" id="filterForm">
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">

                            @php
                            $categories = \App\Models\ProductCategory::where('is_active', true)
                            ->whereNull('parent_id')
                            ->orderBy('name')
                            ->get();
                            @endphp

                            @foreach ($categories as $category)
                            <label class="filter-option">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                    class="filter-checkbox"
                                    {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}
                                    onchange="document.getElementById('filterForm').submit()">
                                <span class="filter-label">{{ $category->name }}</span>
                            </label>
                            @endforeach
                        </form>
                    </div>

                    {{-- Price Range --}}
                    <div class="filter-section">
                        <div class="filter-section-title">
                            <i class="fas fa-dollar-sign text-success"></i>
                            Price Range
                        </div>
                        <form method="GET" action="{{ route('products.index') }}" id="priceForm">
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">

                            <div class="price-range-inputs">
                                <input type="number" name="min_price" class="price-input" placeholder="Min"
                                    value="{{ request('min_price') }}">
                                <span>—</span>
                                <input type="number" name="max_price" class="price-input" placeholder="Max"
                                    value="{{ request('max_price') }}">
                                <button type="submit" class="price-apply-btn">Go</button>
                            </div>
                        </form>
                    </div>

                    {{-- Rating Filter --}}
                    <div class="filter-section">
                        <div class="filter-section-title">
                            <i class="fas fa-star text-warning"></i>
                            Rating
                        </div>
                        @for ($i = 5; $i >= 1; $i--)
                        <a href="{{ route('products.index', array_merge(request()->all(), ['rating' => $i])) }}"
                            class="rating-filter text-decoration-none">
                            <input type="radio" name="rating" class="filter-checkbox"
                                {{ request('rating') == $i ? 'checked' : '' }}>
                            <div class="d-flex align-items-center gap-1">
                                @for ($j = 0; $j < $i; $j++)
                                    <i class="fas fa-star text-warning"></i>
                                    @endfor
                                    @for ($j = $i; $j < 5; $j++)
                                        <i class="far fa-star text-warning"></i>
                                        @endfor
                                        <span class="filter-label ms-1">& up</span>
                            </div>
                        </a>
                        @endfor
                    </div>

                    {{-- Stock Status --}}
                    <div class="filter-section">
                        <div class="filter-section-title">
                            <i class="fas fa-box text-info"></i>
                            Availability
                        </div>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" id="inStock">
                            <span class="filter-label">In Stock</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox" id="onSale">
                            <span class="filter-label">On Sale</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Products Display --}}
            <div class="col-lg-9">
                {{-- Products Header --}}
                <div class="products-header">
                    <div class="products-count">
                        Showing <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong>
                        of <strong>{{ $products->total() }}</strong> products
                    </div>

                    <div class="products-controls">
                        <form method="GET" action="{{ route('products.index') }}"
                            class="d-flex align-items-center gap-3">
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="search" value="{{ request('search') }}">

                            <select name="sort" class="sort-select" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>
                                    Latest
                                </option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                    Price: Low to High
                                </option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                    Price: High to Low
                                </option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>
                                    Most Popular
                                </option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>
                                    Top Rated
                                </option>
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Products Grid --}}
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2 g-md-3">
                    @forelse($products as $product)
                    <div class="col">
                        <div class="product-card-modern">
                            <div class="product-image-wrapper">
                                <a href="{{ route('product.show', $product->slug) }}">
                                    @if ($product->images->isNotEmpty())
                                    <img class="product-image"
                                        src="{{ asset('storage/' . $product->images->first()->path) }}"
                                        alt="{{ $product->name }}">
                                    @else
                                    <img class="product-image"
                                        src="https://placehold.co/400x400/667eea/FFFFFF?text={{ substr($product->name, 0, 1) }}"
                                        alt="{{ $product->name }}">
                                    @endif
                                </a>

                                {{-- Badges --}}
                                @if ($product->sale_price)
                                <div class="product-badge badge-sale">
                                    {{ round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100) }}%
                                    OFF
                                </div>
                                @elseif($product->created_at->diffInDays(now()) <= 7)
                                    <div class="product-badge badge-new">NEW
                            </div>
                            @endif

                            {{-- Wishlist --}}
                            @php
                            $inWishlist = in_array($product->id, $wishlistProductIds ?? []);
                            @endphp
                            <button class="product-wishlist {{ $inWishlist ? 'active' : '' }}"
                                onclick="toggleWishlist({{ $product->id }}, this)">
                                <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart"></i>
                            </button>
                        </div>

                        <div class="product-info">
                            <a href="{{ route('product.show', $product->slug) }}"
                                class="text-decoration-none">
                                <h6 class="product-name">{{ $product->name }}</h6>
                            </a>

                            <div class="product-pricing mt-auto">
                                <div class="product-price-row">
                                    @if ($product->sale_price)
                                    <span class="product-discount me-1">
                                        {{ round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100) }}% OFF
                                    </span>
                                    @endif
                                    <span class="product-price">
                                        RM{{ number_format($product->sale_price ?? $product->regular_price, 2) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    @if ($product->sale_price)
                                    <span class="product-old-price">
                                        RM{{ number_format($product->regular_price, 2) }}
                                    </span>
                                    @endif
                                    <div class="product-sold ms-auto">
                                        {{ number_format($product->sold_count) }} sold
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No products found</h4>
                        <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-redo me-2"></i>Clear Filters
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
    </div>
</section>

{{-- Mobile Filter Button --}}
<button class="mobile-filter-btn" onclick="toggleMobileFilters()">
    <i class="fas fa-filter"></i>
</button>

{{-- Filter Backdrop --}}
<div class="filter-backdrop" id="filterBackdrop" onclick="toggleMobileFilters()"></div>

<script>
    function toggleMobileFilters() {
        const sidebar = document.getElementById('filterSidebar');
        const backdrop = document.getElementById('filterBackdrop');
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    }

    // Wishlist functionality
    function toggleWishlist(productId, button) {
        // Check if user is logged in
        @guest
        window.location.href = '{{ route("login") }}';
        return;
        @endguest

        const icon = button.querySelector('i');

        // Make API call to toggle wishlist
        fetch(`/customer/wishlist/add/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle UI based on response
                    if (data.in_wishlist) {
                        button.classList.add('active');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        button.classList.remove('active');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }

                    // Show success message
                    showToast('Success', data.message || 'Wishlist updated successfully', 'success');
                } else {
                    showToast('Error', data.message || 'Failed to update wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'An error occurred. Please try again.', 'error');
            });
    }

    // Removed local showToast function to use global one from app.blade.php

    // Close mobile filters when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('filterSidebar');
        const mobileBtn = document.querySelector('.mobile-filter-btn');

        if (window.innerWidth <= 991 &&
            sidebar.classList.contains('show') &&
            !sidebar.contains(event.target) &&
            !mobileBtn.contains(event.target)) {
            toggleMobileFilters();
        }
    });
</script>

@endsection