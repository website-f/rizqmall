@extends('partials.app')

@section('title', $product->name)

@section('content')
<!-- Hidden data attributes for JavaScript -->
<div data-product-id="{{ $product->id }}" data-product-type="{{ $product->product_type }}" style="display:none;"></div>

<style>
    .product-detail-container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
    
    /* Image Gallery */
    .image-gallery { position: sticky; top: 20px; }
    .main-image-container {
        border-radius: 20px;
        overflow: hidden;
        background: #f9fafb;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        border: 2px solid #e5e7eb;
    }
    .main-image { width: 100%; height: 100%; object-fit: contain; }
    
    .thumbnail-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 12px;
    }
    .thumbnail-item {
        aspect-ratio: 1;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.2s ease;
    }
    .thumbnail-item:hover, .thumbnail-item.active { 
        border-color: #3b82f6; 
        transform: translateY(-2px); 
    }
    .thumbnail-item img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Product Info */
    .product-title { 
        font-size: 32px; 
        font-weight: 800; 
        line-height: 1.2; 
        margin-bottom: 16px;
        color: #1f2937;
    }
    
    .price-section { 
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 24px;
        border-radius: 16px;
        margin: 24px 0;
    }
    .current-price { 
        font-size: 38px; 
        font-weight: 800; 
        color: #3b82f6; 
    }
    .old-price { 
        font-size: 24px; 
        color: #9ca3af; 
        text-decoration: line-through; 
        margin-left: 12px; 
    }
    .discount-badge {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 700;
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
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        margin: 24px 0;
    }
    .variant-group { margin-bottom: 24px; }
    .variant-label {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 12px;
        display: block;
        color: #374151;
    }
    .variant-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .variant-option {
        padding: 12px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        color: #6b7280;
    }
    .variant-option:hover { 
        border-color: #3b82f6; 
        transform: translateY(-2px);
    }
    .variant-option.selected {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
    }
    .variant-option.color-swatch {
        width: 50px;
        height: 50px;
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
        font-size: 22px;
        text-shadow: 0 0 4px rgba(0,0,0,0.8);
    }
    
    /* Quantity Selector */
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 24px 0;
    }
    .qty-btn {
        width: 44px;
        height: 44px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 10px;
        cursor: pointer;
        font-size: 20px;
        font-weight: 700;
        transition: all 0.2s ease;
        color: #6b7280;
    }
    .qty-btn:hover { 
        background: #f3f4f6; 
        border-color: #3b82f6;
        color: #3b82f6;
    }
    .qty-input {
        width: 80px;
        height: 44px;
        text-align: center;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
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
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-add-cart:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4); 
    }
    .btn-add-cart:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    .btn-buy-now {
        flex: 1;
        padding: 16px 32px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-buy-now:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4); 
    }
    .btn-buy-now:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
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
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
    }
    .stock-badge.in-stock { background: #d1fae5; color: #065f46; }
    .stock-badge.out-stock { background: #fee2e2; color: #991b1b; }
    
    .attribute-badges { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0; }
    .attribute-badge {
        padding: 8px 14px;
        background: #f3f4f6;
        border-radius: 8px;
        font-size: 14px;
        color: #4b5563;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
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
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">{{ ucfirst($product->type) }}s</a></li>
            @if($product->category)
                <li class="breadcrumb-item"><a href="#">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Image Gallery -->
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

        <!-- Right: Product Info -->
        <div class="col-lg-7">
            <h1 class="product-title">{{ $product->name }}</h1>
            
            <!-- Rating -->
            <div class="rating-display">
                <div class="stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($product->rating_average))
                            <i class="fas fa-star"></i>
                        @elseif($i - 0.5 <= $product->rating_average)
                            <i class="fas fa-star-half-alt"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <span><strong>{{ number_format($product->rating_average, 1) }}</strong></span>
                <span class="text-muted">({{ number_format($product->rating_count) }} reviews)</span>
                <span class="text-muted ms-3">|</span>
                <span class="text-muted ms-3">{{ number_format($product->sold_count) }} sold</span>
            </div>

            @if($product->short_description)
            <p class="text-muted mb-3">{{ $product->short_description }}</p>
            @endif

            <!-- Price Section -->
            <div class="price-section">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="current-price" id="displayPrice">RM {{ number_format($product->on_sale ? $product->sale_price : $product->regular_price, 2) }}</span>
                    @if($product->on_sale)
                    <span class="old-price">RM {{ number_format($product->regular_price, 2) }}</span>
                    <span class="discount-badge">{{ $product->discount_percentage }}% OFF</span>
                    @endif
                </div>
            </div>

            <!-- Stock Status -->
            <div class="mb-3">
                @php
                    $inStock = $product->product_type === 'simple' 
                        ? $product->stock_quantity > 0 
                        : $product->variants->sum('stock_quantity') > 0;
                @endphp
                
                @if($product->type === 'service')
                    <span class="stock-badge in-stock">
                        <i class="fas fa-check-circle"></i> Available for Booking
                    </span>
                @elseif($inStock)
                    <span class="stock-badge in-stock">
                        <i class="fas fa-check-circle"></i> In Stock
                    </span>
                @else
                    <span class="stock-badge out-stock">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </span>
                @endif
            </div>

            <!-- Attributes Badges -->
            @if($product->type === 'product' && ($product->is_fragile || $product->is_biodegradable || $product->is_frozen || $product->has_expiry))
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

            @if($product->type === 'pharmacy' && $product->requires_prescription)
            <div class="alert alert-warning border-0 d-flex align-items-center">
                <i class="fas fa-prescription fa-2x me-3"></i>
                <div>
                    <strong>Prescription Required</strong>
                    <p class="mb-0 small">This medication requires a valid prescription. Please upload your prescription during checkout.</p>
                </div>
            </div>
            @endif

            <!-- Variant Selector (for variable products) -->
            @if($product->product_type === 'variable' && $variantTypes->count() > 0)
            <div class="variant-section">
                <input type="hidden" id="selectedVariantId" value="">
                
                @foreach($variantTypes as $variantType)
                    @php
                        $typeOptions = $product->variants->pluck('options')->flatten()->where('variant_type_id', $variantType->id)->unique('value');
                    @endphp
                    
                    @if($typeOptions->count() > 0)
                    <div class="variant-group">
                        <label class="variant-label">
                            {{ $variantType->name }}: 
                            <span class="text-primary" id="selected_{{ $variantType->id }}">Please select</span>
                        </label>
                        <div class="variant-options">
                            @foreach($typeOptions as $option)
                            <button type="button" 
                                    class="variant-option {{ $variantType->display_type === 'color_swatch' ? 'color-swatch' : '' }}"
                                    data-type="{{ $variantType->id }}"
                                    data-value="{{ $option->value }}"
                                    @if($variantType->display_type === 'color_swatch' && $option->color_code)
                                        style="background-color: {{ $option->color_code }};"
                                        title="{{ $option->value }}"
                                    @endif
                                    onclick="selectVariantOption({{ $variantType->id }}, '{{ $option->value }}', this)">
                                @if($variantType->display_type !== 'color_swatch')
                                    {{ $option->value }}
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
                
                <div class="alert alert-info mt-3" id="variantAlert" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i> Please select all options
                </div>
            </div>
            @endif

            <!-- Service Details -->
            @if($product->type === 'service')
            <div class="variant-section">
                <h5 class="mb-3"><i class="fas fa-clock me-2"></i> Service Details</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Duration:</strong> {{ $product->service_duration }} minutes
                    </div>
                    <div class="col-md-6">
                        <strong>Availability:</strong> {{ ucfirst($product->service_availability) }}
                    </div>
                    @if($product->service_days)
                    <div class="col-12">
                        <strong>Available Days:</strong> {{ implode(', ', $product->service_days) }}
                    </div>
                    @endif
                    @if($product->service_start_time && $product->service_end_time)
                    <div class="col-12">
                        <strong>Service Hours:</strong> {{ $product->service_start_time }} - {{ $product->service_end_time }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Quantity Selector -->
            @if($product->type !== 'service')
            <div class="quantity-selector">
                <label class="fw-bold me-3">Quantity:</label>
                <button class="qty-btn" onclick="decrementQty()">−</button>
                <input type="number" class="qty-input" id="quantity" value="1" min="1" max="99">
                <button class="qty-btn" onclick="incrementQty()">+</button>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="action-buttons">
                @if($product->type === 'service')
                    <button class="btn-add-cart" onclick="bookService()">
                        <i class="fas fa-calendar-check me-2"></i> Book Service
                    </button>
                @else
                    <button class="btn-add-cart" onclick="addToCart()" {{ !$inStock ? 'disabled' : '' }}>
                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                    <button class="btn-buy-now" onclick="buyNow()" {{ !$inStock ? 'disabled' : '' }}>
                        <i class="fas fa-bolt me-2"></i> Buy Now
                    </button>
                @endif
                <button class="btn-wishlist" onclick="toggleWishlist(this)">
                    <i class="far fa-heart"></i>
                </button>
            </div>

            <!-- Additional Info -->
            <div class="mt-4 pt-4 border-top">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>SKU:</strong> <span class="text-muted">{{ $product->sku }}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Store:</strong> 
                        <a href="{{ route('store.profile', $product->store->slug) }}" class="text-primary">
                            {{ $product->store->name }}
                        </a>
                    </div>
                    @if($product->category)
                    <div class="col-md-6">
                        <strong>Category:</strong> 
                        <span class="text-muted">{{ $product->category->name }}</span>
                    </div>
                    @endif
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

    <!-- Product Details Tabs -->
    <div class="product-tabs" style="margin-top: 60px;">
        <ul class="nav nav-tabs" role="tablist" style="border-bottom: 3px solid #e5e7eb;">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description" style="border: none; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 700; padding: 16px 24px;">
                    Description
                </button>
            </li>
            @if($product->specifications->count() > 0)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specifications" style="border: none; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 700; padding: 16px 24px;">
                    Specifications
                </button>
            </li>
            @endif
            @if($product->type === 'product')
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping" style="border: none; border-bottom: 3px solid transparent; color: #6b7280; font-weight: 700; padding: 16px 24px;">
                    Shipping Info
                </button>
            </li>
            @endif
        </ul>

        <div class="tab-content p-4">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description">
                <div class="content-section">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            <!-- Specifications Tab -->
            @if($product->specifications->count() > 0)
            <div class="tab-pane fade" id="specifications">
                @php
                    $groupedSpecs = $product->specifications->groupBy('spec_group');
                @endphp
                
                @foreach($groupedSpecs as $group => $specs)
                <h5 class="mb-3 mt-4">{{ $group }}</h5>
                <table style="width: 100%;">
                    @foreach($specs as $spec)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 16px 0; font-weight: 700; width: 30%; color: #6b7280;">{{ $spec->spec_key }}</td>
                        <td style="padding: 16px 0; color: #1f2937;">{{ $spec->spec_value }}</td>
                    </tr>
                    @endforeach
                </table>
                @endforeach
            </div>
            @endif

            <!-- Shipping Tab -->
            @if($product->type === 'product')
            <div class="tab-pane fade" id="shipping">
                <h5 class="mb-3">Shipping Information</h5>
                <table style="width: 100%;">
                    @if($product->weight)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 16px 0; font-weight: 700; width: 30%; color: #6b7280;">Weight</td>
                        <td style="padding: 16px 0; color: #1f2937;">{{ $product->weight }} kg</td>
                    </tr>
                    @endif
                    @if($product->length && $product->width && $product->height)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 16px 0; font-weight: 700; width: 30%; color: #6b7280;">Dimensions</td>
                        <td style="padding: 16px 0; color: #1f2937;">{{ $product->length }} × {{ $product->width }} × {{ $product->height }} cm</td>
                    </tr>
                    @endif
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 16px 0; font-weight: 700; width: 30%; color: #6b7280;">Shipping</td>
                        <td style="padding: 16px 0; color: #1f2937;">Free shipping for orders above RM100</td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 0; font-weight: 700; width: 30%; color: #6b7280;">Delivery Time</td>
                        <td style="padding: 16px 0; color: #1f2937;">2-5 business days</td>
                    </tr>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Variant data
const variantsData = @json($variantData ?? []);
const selectedOptions = {};

// Image gallery
function changeMainImage(thumbnail) {
    const imageUrl = thumbnail.dataset.image;
    document.getElementById('mainImage').src = imageUrl;
    
    document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Quantity controls
function incrementQty() {
    const input = document.getElementById('quantity');
    if (input && parseInt(input.value) < 99) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    if (input && parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Variant selection
function selectVariantOption(typeId, value, button) {
    selectedOptions[typeId] = value;
    
    document.querySelectorAll(`[data-type="${typeId}"]`).forEach(btn => {
        btn.classList.remove('selected');
    });
    button.classList.add('selected');
    
    const label = document.getElementById(`selected_${typeId}`);
    if (label) label.textContent = value;
    
    findMatchingVariant();
}

function findMatchingVariant() {
    const variant = variantsData.find(v => {
        return Object.keys(selectedOptions).every(typeId => {
            const option = v.options[typeId];
            return option && option.value == selectedOptions[typeId];
        });
    });
    
    const alert = document.getElementById('variantAlert');
    
    if (variant) {
        document.getElementById('selectedVariantId').value = variant.id;
        if (alert) alert.style.display = 'none';
        
        const price = variant.sale_price || variant.price;
        const priceDisplay = document.getElementById('displayPrice');
        if (priceDisplay) {
            priceDisplay.textContent = `RM ${parseFloat(price).toFixed(2)}`;
        }
    } else {
        if (alert) alert.style.display = 'block';
    }
}

// Cart actions
async function addToCart() {
    const productId = parseInt(document.querySelector('[data-product-id]')?.dataset.productId);
    const productType = document.querySelector('[data-product-type]')?.dataset.productType;
    const quantity = parseInt(document.getElementById('quantity')?.value || 1);
    
    if (!productId) {
        CartManager.showErrorModal('Product not found');
        return false;
    }

    let variantId = null;
    
    if (productType === 'variable') {
        variantId = document.getElementById('selectedVariantId')?.value;
        if (!variantId) {
            CartManager.showErrorModal('Please select all product options');
            return false;
        }
    }

    const success = await CartManager.addToCart(productId, variantId, quantity);
    if (success) {
        const qtyInput = document.getElementById('quantity');
        if (qtyInput) qtyInput.value = 1;
    }
    return success;
}

async function buyNow() {
    const success = await addToCart();
    if (success) {
        setTimeout(() => {
            window.location.href = '/cart';
        }, 2100);
    }
}

function bookService() {
    CartManager.showErrorModal('Service booking coming soon!');
}

function toggleWishlist(button) {
    button.classList.toggle('active');
    const icon = button.querySelector('i');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
}



</script>

@endsection