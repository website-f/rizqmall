@extends('partials.app')

@section('title', 'RizqMall - Premium Shopping Experience')

@section('content')
<style>
    /* Hero Section */
    .hero-section {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 80px 0;
        position: relative;
        overflow: hidden;
        border-radius: 0 0 40px 40px;
        margin-bottom: 60px;
    }

    .hero-bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
        background-image: radial-gradient(circle at 20% 50%, #3b82f6 0%, transparent 25%),
            radial-gradient(circle at 80% 80%, #10b981 0%, transparent 25%);
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 24px;
        background: linear-gradient(to right, #ffffff, #93c5fd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: 1.25rem;
        color: #cbd5e1;
        margin-bottom: 32px;
        max-width: 600px;
    }

    .hero-btn {
        padding: 14px 32px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .hero-btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }

    .hero-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(59, 130, 246, 0.5);
        color: white;
    }

    /* Features Section */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 80px;
    }

    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-align: center;
        border: 1px solid #f1f5f9;
        height: 100%;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        border-color: #e2e8f0;
    }

    .feature-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 28px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #3b82f6;
    }

    .feature-title {
        font-weight: 700;
        margin-bottom: 10px;
        color: #1e293b;
    }

    .feature-desc {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Categories Section */
    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
    }

    .section-subtitle {
        color: #64748b;
        font-size: 1.1rem;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 24px;
        margin-bottom: 80px;
    }

    .category-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        height: 100%;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .category-icon-box {
        height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        font-size: 48px;
        color: #cbd5e1;
        transition: all 0.3s ease;
    }

    .category-card:hover .category-icon-box {
        background: #eff6ff;
        color: #3b82f6;
    }

    .category-info {
        padding: 16px;
        text-align: center;
    }

    .category-name {
        font-weight: 600;
        color: #334155;
        font-size: 1rem;
        margin: 0;
    }

    /* Product Grid Modern */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .product-card {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
    }

    .product-image-container {
        position: relative;
        padding-top: 100%;
        /* 1:1 Aspect Ratio */
        overflow: hidden;
        background: #f8fafc;
    }

    .product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .product-card:hover .product-image {
        transform: scale(1.1);
    }

    .product-badges {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 2;
    }

    .badge-label {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background: #3b82f6;
    }

    .badge-danger {
        background: #ef4444;
    }

    .badge-success {
        background: #10b981;
    }

    .product-action-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        z-index: 2;
        opacity: 0;
        transform: translateX(20px);
    }

    .product-card:hover .product-action-btn {
        opacity: 1;
        transform: translateX(0);
    }

    .product-action-btn:hover {
        background: #ef4444;
        color: white;
    }

    .product-details {
        padding: 24px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-category {
        color: #94a3b8;
        font-size: 0.85rem;
        margin-bottom: 8px;
    }

    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-decoration: none;
        line-height: 1.5;
    }

    .product-title:hover {
        color: #3b82f6;
    }

    .product-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-price {
        font-size: 1.25rem;
        font-weight: 700;
        color: #3b82f6;
    }

    .add-to-cart-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .add-to-cart-btn:hover {
        background: #3b82f6;
        color: white;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-section {
            padding: 60px 0;
            border-radius: 0 0 30px 30px;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-bg-pattern"></div>
    <div class="container hero-content text-center">
        <h1 class="hero-title animate__animated animate__fadeInDown">Welcome to RizqMall</h1>
        <p class="hero-subtitle mx-auto animate__animated animate__fadeInUp animate__delay-1s">
            Discover a premium shopping experience with our curated collection of quality products, direct from trusted vendors.
        </p>
        <div class="animate__animated animate__fadeInUp animate__delay-2s">
            <a href="{{ route('products.index') }}" class="hero-btn hero-btn-primary text-decoration-none">
                Start Shopping Now <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<div class="container">
    <!-- Features -->
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <h3 class="feature-title">Fast Delivery</h3>
            <p class="feature-desc">Reliable and quick shipping to your doorstep.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="feature-title">Secure Payments</h3>
            <p class="feature-desc">100% secure payment processing with ToyyibPay.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <i class="fas fa-headset"></i>
            </div>
            <h3 class="feature-title">24/7 Support</h3>
            <p class="feature-desc">Dedicated customer support whenever you need us.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper">
                <i class="fas fa-undo"></i>
            </div>
            <h3 class="feature-title">Easy Returns</h3>
            <p class="feature-desc">Hassle-free return policy for peace of mind.</p>
        </div>
    </div>

    <!-- Featured Categories -->
    <section class="mb-5">
        <div class="section-header">
            <h2 class="section-title">Browse By Category</h2>
            <p class="section-subtitle">Find exactly what you are looking for</p>
        </div>

        @php
        $displayCategories = \App\Models\ProductCategory::where('is_active', true)
        ->whereNull('parent_id')
        ->inRandomOrder()
        ->limit(6)
        ->get();
        @endphp

        <div class="categories-grid">
            @foreach($displayCategories as $cat)
            <a href="{{ route('products.index', ['category' => $cat->id]) }}" class="category-card">
                <div class="category-icon-box">
                    <i class="fas {{ $cat->icon ?? 'fa-box' }}"></i>
                </div>
                <div class="category-info">
                    <h4 class="category-name">{{ $cat->name }}</h4>
                </div>
            </a>
            @endforeach

            <!-- Explicit Link to Food & Catering if not in random 5, just to be sure -->
            <a href="{{ route('products.index', ['category' => \App\Models\ProductCategory::where('name', 'Food & Catering')->value('id')]) }}" class="category-card">
                <div class="category-icon-box" style="background: #fff7ed; color: #ea580c;">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="category-info">
                    <h4 class="category-name">Food & Catering</h4>
                </div>
            </a>
        </div>
    </section>

    <!-- Latest Products -->
    <section class="mb-5">
        <div class="section-header d-flex justify-content-between align-items-end text-start">
            <div>
                <h2 class="section-title mb-0">Latest Arrivals</h2>
                <p class="section-subtitle mb-0 text-start">Fresh additions to our store</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary rounded-pill px-4">View All</a>
        </div>

        <div class="product-grid">
            @foreach($products as $product)
            <div class="product-card">
                <div class="product-image-container">
                    <img src="{{ $product->images->first() ? asset('storage/' . $product->images->first()->path) : 'https://placehold.co/400x400/e2e8f0/64748b?text=' . substr($product->name, 0, 1) }}"
                        alt="{{ $product->name }}" class="product-image">

                    <div class="product-badges">
                        @if($product->stock_quantity <= 0)
                            <span class="badge-label badge-danger">Out of Stock</span>
                            @elseif($product->created_at->diffInDays(now()) < 7)
                                <span class="badge-label badge-success">New</span>
                                @endif
                    </div>

                    <button class="product-action-btn" onclick="toggleWishlist({{ $product->id }}, this)"
                        title="Add to Wishlist">
                        <i class="far fa-heart"></i>
                    </button>
                </div>

                <div class="product-details">
                    <div class="product-category">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </div>
                    <a href="{{ route('product.show', $product->slug) }}" class="product-title">
                        {{ $product->name }}
                    </a>

                    <div class="product-footer">
                        <div class="product-price">
                            RM {{ number_format($product->sale_price ?? $product->regular_price, 2) }}
                        </div>
                        <a href="{{ route('product.show', $product->slug) }}" class="add-to-cart-btn" title="View Details">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>

<script>
    // Re-use wishlist logic safely
    async function toggleWishlist(productId, btn) {
        @guest
        window.location.href = "{{ route('login') }}";
        return;
        @endguest

        try {
            const response = await fetch(`/customer/wishlist/add/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data.success) {
                const icon = btn.querySelector('i');
                if (data.in_wishlist) {
                    icon.classList.remove('far');
                    icon.classList.add('fas', 'text-danger');
                } else {
                    icon.classList.remove('fas', 'text-danger');
                    icon.classList.add('far');
                }
                showToast('Success', data.message, 'success');
            } else {
                showToast('Error', data.message, 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Error', 'Something went wrong', 'error');
        }
    }
</script>
@endsection