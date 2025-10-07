@extends('layouts.app')

@section('title', $product->name)

@section('content')
<style>
    .product-detail-container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }
    
    /* Image Gallery */
    .image-gallery { position: sticky; top: 20px; }
    .main-image-container {
        border-radius: 16px;
        overflow: hidden;
        background: #f9fafb;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        border: 1px solid #e5e7eb;
    }
    .main-image { width: 100%; height: 100%; object-fit: contain; }
    
    .thumbnail-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 12px;
    }
    .thumbnail-item {
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }
    .thumbnail-item:hover { border-color: #3b82f6; transform: translateY(-2px); }
    .thumbnail-item.active { border-color: #3b82f6; }
    .thumbnail-item img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Product Info */
    .product-title { font-size: 32px; font-weight: 700; line-height: 1.2; margin-bottom: 16px; }
    .price-section { 
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 24px;
        border-radius: 12px;
        margin: 24px 0;
    }
    .current-price { font-size: 36px; font-weight: 700; color: #3b82f6; }
    .old-price { font-size: 24px; color: #9ca3af; text-decoration: line-through; margin-left: 12px; }
    .discount-badge {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        margin-left: 12px;
    }
    
    .rating-display {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        margin-bottom: 16px;
    }
    .stars { color: #fbbf24; font-size: 20px; }
    
    /* Variant Selector */
    .variant-section {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
    }
    .variant-group { margin-bottom: 24px; }
    .variant-label {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 12px;
        display: block;
    }
    .variant-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .variant-option {
        padding: 12px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    .variant-option:hover { border-color: #3b82f6; }
    .variant-option.selected {
        border-color: #3b82f6;
        background: #eff6ff;
        color: #3b82f6;
    }
    .variant-option.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: #f3f4f6;
    }
    .variant-option.color-swatch {
        width: 48px;
        height: 48px;
        padding: 0;
        border-radius: 50%;
        position: relative;
    }
    .variant-option.color-swatch.selected::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-weight: bold;
        font-size: 20px;
        text-shadow: 0 0 3px rgba(0,0,0,0.8);
    }
    
    /* Quantity Selector */
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 24px 0;
    }
    .qty-btn {
        width: 40px;
        height: 40px;
        border: 1px solid #e5e7eb;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.2s ease;
    }
    .qty-btn:hover { background: #f3f4f6; }
    .qty-input {
        width: 80px;
        height: 40px;
        text-align: center;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
    }
    
    /* Action Buttons */
    .action-buttons { display: flex; gap: 12px; margin: 24px 0; }
    .btn-add-cart {
        flex: 1;
        padding: 16px 32px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-add-cart:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4); }
    .btn-buy-now {
        flex: 1;
        padding: 16px 32px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-buy-now:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4); }
    .btn-wishlist {
        width: 56px;
        height: 56px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #6b7280;
    }
    .btn-wishlist:hover { border-color: #ef4444; color: #ef4444; }
    .btn-wishlist.active { background: #ef4444; color: white; border-color: #ef4444; }
    
    /* Stock Badge */
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    .stock-badge.in-stock { background: #d1fae5; color: #065f46; }
    .stock-badge.out-stock { background: #fee2e2; color: #991b1b; }
    .stock-badge.low-stock { background: #fef3c7; color: #92400e; }
    
    /* Product Details Tabs */
    .product-tabs { margin-top: 60px; }
    .nav-tabs { border-bottom: 2px solid #e5e7eb; }
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6b7280;
        font-weight: 600;
        padding: 16px 24px;
        transition: all 0.2s ease;
    }
    .nav-tabs .nav-link:hover { color: #3b82f6; }
    .nav-tabs .nav-link.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        background: transparent;
    }
    
    .specifications-table { width: 100%; }
    .specifications-table tr { border-bottom: 1px solid #e5e7eb; }
    .specifications-table td { padding: 16px 0; }
    .spec-label { font-weight: 600; width: 30%; color: #6b7280; }
    .spec-value { color: #1f2937; }
    
    /* Attributes Badges */
    .attribute-badges { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0; }
    .attribute-badge {
        padding: 6px 12px;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 14px;
        color: #4b5563;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .product-title { font-size: 24px; }
        .current-price { font-size: 28px; }
        .action-buttons { flex-direction: column; }
    }
</style>

<div class="product-detail-container">
    <nav class="mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="#">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Left: Image Gallery --}}
        <div class="col-lg-5">
            <div class="image-gallery">
                <div class="main-image-container">
                    <img id="mainImage" class="main-image" 
                         src="{{ $product->images->first() ? $product->images->first()->url : asset('images/placeholder.png') }}" 
                         alt="{{ $product->name }}">
                </div>
                
                @if($product->images->count() > 1)
                <div class="thumbnail-gallery">
                    @foreach($product->images as $index => $image)
                    <div class="thumbnail-item {{ $index === 0 ? 'active' : '' }}" 
                         data-image="{{ $image->url }}"
                         onclick="changeMainImage(this)">
                        <img src="{{ $image->thumbnail_url }}" alt="Thumbnail {{ $index + 1 }}">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Product Info --}}
        <div class="col-lg-7">
            <h1 class="product-title">{{ $product->name }}</h1>
            
            {{-- Rating --}}
            <div class="rating-display">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($dummyRating))
                            <i class="fas fa-star"></i>
                        @elseif($i - 0.5 <= $dummyRating)
                            <i class="fas fa-star-half-alt"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <span><strong>{{ $dummyRating }}</strong></span>
                <span class="text-muted">({{ number_format($dummyReviewsCount) }} reviews)</span>
                <span class="text-muted ms-3">|</span>
                <span class="text-muted ms-3">{{ number_format($product->sold_count) }} sold</span>
            </div>

            {{-- Short Description --}}
            @if($product->short_description)
            <p class="text-muted mb-3">{{ $product->short_description }}</p>
            @endif

            {{-- Price Section --}}
            <div class="price-section">
                <div class="d-flex align-items-center">
                    <span class="current-price" id="displayPrice">RM {{ number_format($price, 2) }}</span>
                    @if($onSale)
                    <span class="old-price" id="displayOldPrice">RM {{ number_format($oldPrice, 2) }}</span>
                    <span class="discount-badge">{{ $discountPercentage }}% OFF</span>
                    @endif
                </div>
            </div>

            {{-- Stock Status --}}
            <div class="mb-3">
                @if($inStock)
                    <span class="stock-badge in-stock">
                        <i class="fas fa-check-circle"></i> In Stock
                    </span>
                @else
                    <span class="stock-badge out-stock">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </span>
                @endif
            </div>

            {{-- Attributes Badges --}}
            @if($product->is_fragile || $product->is_biodegradable || $product->is_frozen || $product->has_expiry)
            <div class="attribute-badges">
                @if($product->is_fragile)
                    <span class="attribute-badge"><i class="fas fa-exclamation-triangle text-warning"></i> Fragile</span>
                @endif
                @if($product->is_biodegradable)
                    <span class="attribute-badge"><i class="fas fa-leaf text-success"></i> Biodegradable</span>
                @endif
                @if($product->is_frozen)
                    <span class="attribute-badge"><i class="fas fa-snowflake text-info"></i> Frozen ({{ $product->max_temperature }})</span>
                @endif
                @if($product->has_expiry)
                    <span class="attribute-badge"><i class="fas fa-calendar-times text-danger"></i> Expiry: {{ $product->expiry_date->format('M d, Y') }}</span>
                @endif
            </div>
            @endif

            {{-- Variant Selector (for variable products) --}}
            @if($product->product_type === 'variable')
            <div class="variant-section">
                <input type="hidden" id="selectedVariantId" value="">
                
                @foreach($product->attributes as $attribute)
                <div class="variant-group">
                    <label class="variant-label">
                        {{ $attribute->name }}: 
                        <span class="text-primary" id="selected_{{ $attribute->id }}"></span>
                    </label>
                    <div class="variant-options">
                        @foreach($attribute->options as $option)
                        <button type="button" 
                                class="variant-option {{ $attribute->display_type === 'color_swatch' ? 'color-swatch' : '' }}"
                                data-attribute="{{ $attribute->id }}"
                                data-option="{{ $option->id }}"
                                data-value="{{ $option->value }}"
                                @if($attribute->display_type === 'color_swatch' && $option->color_code)
                                    style="background-color: {{ $option->color_code }};"
                                    title="{{ $option->value }}"
                                @endif
                                onclick="selectVariantOption({{ $attribute->id }}, {{ $option->id }}, '{{ $option->value }}', this)">
                            @if($attribute->display_type !== 'color_swatch')
                                {{ $option->value }}
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
                
                <div class="alert alert-info mt-3" id="variantAlert" style="display: none;">
                    Please select all options
                </div>
            </div>
            @endif

            {{-- Quantity Selector --}}
            <div class="quantity-selector">
                <label class="fw-semibold me-3">Quantity:</label>
                <button class="qty-btn" onclick="decrementQty()">−</button>
                <input type="number" class="qty-input" id="quantity" value="1" min="1" max="99">
                <button class="qty-btn" onclick="incrementQty()">+</button>
            </div>

            {{-- Action Buttons --}}
            <div class="action-buttons">
                <button class="btn-add-cart" onclick="addToCart()" {{ !$inStock ? 'disabled' : '' }}>
                    <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                </button>
                <button class="btn-buy-now" onclick="buyNow()" {{ !$inStock ? 'disabled' : '' }}>
                    <i class="fas fa-bolt me-2"></i> Buy Now
                </button>
                <button class="btn-wishlist" onclick="toggleWishlist(this)">
                    <i class="far fa-heart"></i>
                </button>
            </div>

            {{-- Share & Additional Info --}}
            <div class="mt-4 pt-4 border-top">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>SKU:</strong> <span class="text-muted">{{ $product->sku }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Category:</strong> 
                        <span class="text-muted">{{ $product->category->name ?? 'Uncategorized' }}</span>
                    </div>
                    @if($product->tags->count() > 0)
                    <div class="col-12">
                        <strong>Tags:</strong>
                        @foreach($product->tags as $tag)
                            <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Product Details Tabs --}}
    <div class="product-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description">
                    Description
                </button>
            </li>
            @if($product->specifications->count() > 0)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specifications">
                    Specifications
                </button>
            </li>
            @endif
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping">
                    Shipping Info
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">
                    Reviews ({{ number_format($dummyReviewsCount) }})
                </button>
            </li>
        </ul>

        <div class="tab-content p-4">
            {{-- Description Tab --}}
            <div class="tab-pane fade show active" id="description">
                <div class="content-section">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            {{-- Specifications Tab --}}
            @if($product->specifications->count() > 0)
            <div class="tab-pane fade" id="specifications">
                @php
                    $groupedSpecs = $product->specifications->groupBy('spec_group');
                @endphp
                
                @foreach($groupedSpecs as $group => $specs)
                <h5 class="mb-3 mt-4 first:mt-0">{{ $group }}</h5>
                <table class="specifications-table">
                    @foreach($specs as $spec)
                    <tr>
                        <td class="spec-label">{{ $spec->spec_key }}</td>
                        <td class="spec-value">{{ $spec->spec_value }}</td>
                    </tr>
                    @endforeach
                </table>
                @endforeach
            </div>
            @endif

            {{-- Shipping Tab --}}
            <div class="tab-pane fade" id="shipping">
                <h5 class="mb-3">Shipping Information</h5>
                <table class="specifications-table">
                    @if($product->weight)
                    <tr>
                        <td class="spec-label">Weight</td>
                        <td class="spec-value">{{ $product->weight }} kg</td>
                    </tr>
                    @endif
                    @if($product->length && $product->width && $product->height)
                    <tr>
                        <td class="spec-label">Dimensions</td>
                        <td class="spec-value">{{ $product->length }} × {{ $product->width }} × {{ $product->height }} cm</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="spec-label">Shipping</td>
                        <td class="spec-value">Free shipping for orders above RM100</td>
                    </tr>
                    <tr>
                        <td class="spec-label">Delivery Time</td>
                        <td class="spec-value">2-5 business days</td>
                    </tr>
                </table>
            </div>

            {{-- Reviews Tab --}}
            <div class="tab-pane fade" id="reviews">
                <p class="text-muted">Reviews feature coming soon...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Variant data from backend
const variantsData = @json($variantData ?? []);
const selectedOptions = {};

// Image gallery
function changeMainImage(thumbnail) {
    const imageUrl = thumbnail.dataset.image;
    document.getElementById('mainImage').src = imageUrl;
    
    document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Quantity
function incrementQty() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
}

function decrementQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Variant selection
function selectVariantOption(attributeId, optionId, value, button) {
    // Update selected options
    selectedOptions[attributeId] = optionId;
    
    // Update UI
    document.querySelectorAll(`[data-attribute="${attributeId}"]`).forEach(btn => {
        btn.classList.remove('selected');
    });
    button.classList.add('selected');
    
    // Update label
    document.getElementById(`selected_${attributeId}`).textContent = value;
    
    // Find matching variant
    findMatchingVariant();
}

function findMatchingVariant() {
    const variant = variantsData.find(v => {
        return Object.keys(selectedOptions).every(attrId => {
            return v.options[attrId] == selectedOptions[attrId];
        });
    });
    
    if (variant) {
        document.getElementById('selectedVariantId').value = variant.id;
        document.getElementById('variantAlert').style.display = 'none';
        
        // Update price
        const price = variant.sale_price || variant.price;
        document.getElementById('displayPrice').textContent = `RM ${parseFloat(price).toFixed(2)}`;
        
        if (variant.sale_price) {
            document.getElementById('displayOldPrice').textContent = `RM ${parseFloat(variant.price).toFixed(2)}`;
        }
        
        // Update stock
        // You can add stock update logic here
    } else {
        document.getElementById('variantAlert').style.display = 'block';
    }
}

// Cart actions
function addToCart() {
    const productType = '{{ $product->product_type }}';
    const quantity = document.getElementById('quantity').value;
    
    if (productType === 'variable') {
        const variantId = document.getElementById('selectedVariantId').value;
        if (!variantId) {
            alert('Please select all product options');
            return;
        }
        console.log('Add to cart:', { productId: {{ $product->id }}, variantId, quantity });
    } else {
        console.log('Add to cart:', { productId: {{ $product->id }}, quantity });
    }
    
    alert('Added to cart! (Implement your cart logic)');
}

function buyNow() {
    addToCart();
    // Redirect to checkout
    // window.location.href = '/checkout';
}

function toggleWishlist(button) {
    button.classList.toggle('active');
    const icon = button.querySelector('i');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
}
</script>

@endsection