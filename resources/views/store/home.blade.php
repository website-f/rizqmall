@extends('partials.app')

@section('title', 'Rizqmall')

@section('content')
    <!-- ============================================-->
    <!-- Top Category Navigation -->
    <!-- ============================================-->
    <section class="py-0">
        <div class="container-small">
            <div class="scrollbar">
                <div class="d-flex justify-content-between">
                    <a class="icon-nav-item" href="{{ route('products.index') }}">
                        <div class="icon-container mb-2 bg-warning-subtle" data-bs-theme="light">
                            <span class="fs-4 uil uil-star text-warning"></span>
                        </div>
                        <p class="nav-label">All Deals</p>
                    </a>

                    @foreach ($storeCategories as $category)
                        @if (in_array($category->slug, ['marketplace', 'services', 'pharmacy', 'premises', 'hardware']))
                            <a class="icon-nav-item" href="{{ route('products.index', ['type' => $category->slug]) }}">
                                <div class="icon-container mb-2" data-bs-theme="light">
                                    <span class="fs-4 {{ $category->icon }}"></span>
                                </div>
                                <p class="nav-label">{{ $category->name }}</p>
                            </a>
                        @else
                            <div class="icon-nav-item" style="opacity: 0.5; cursor: not-allowed;" title="Coming Soon">
                                <div class="icon-container mb-2" data-bs-theme="light">
                                    <span class="fs-4 {{ $category->icon }}"></span>
                                </div>
                                <p class="nav-label">{{ $category->name }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================-->
    <!-- Banners and Main Content -->
    <!-- ============================================-->
    <section class="py-0 px-xl-3">
        <div class="container px-xl-0 px-xxl-3">
            {{-- Banners --}}
            <div class="row g-3 mb-9">
                <div class="col-12">
                    <div class="whooping-banner w-100 rounded-3 overflow-hidden">
                        <div class="bg-holder z-n1 product-bg"
                            style="background-image:url({{ asset('assets/img/e-commerce/whooping_banner_product.png') }});background-position: bottom right;">
                        </div>
                        <div class="bg-holder z-n1 shape-bg"
                            style="background-image:url({{ asset('assets/img/e-commerce/whooping_banner_shape_2.png') }});background-position: bottom left;">
                        </div>
                        <div class="banner-text" data-bs-theme="light">
                            <h2 class="text-warning-light fw-bolder fs-lg-3 fs-xxl-2">Whooping <span
                                    class="gradient-text">60% </span>Off</h2>
                            <h3 class="fw-bolder fs-lg-5 fs-xxl-3 text-white">on everyday items</h3>
                        </div>
                        <a class="btn btn-lg btn-primary rounded-pill banner-button"
                            href="{{ route('products.index') }}">Shop Now</a>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="gift-items-banner w-100 rounded-3 overflow-hidden">
                        <div class="bg-holder z-n1 banner-bg"
                            style="background-image:url({{ asset('assets/img/e-commerce/gift-items-banner-bg.png') }});">
                        </div>
                        <div class="banner-text text-md-center">
                            <h2 class="text-white fw-bolder fs-xl-4">Get <span class="gradient-text">10% Off </span><br
                                    class="d-md-none"> on gift items</h2>
                            <a class="btn btn-lg btn-primary rounded-pill banner-button"
                                href="{{ route('products.index') }}">Buy Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="best-in-market-banner d-flex h-100 px-4 px-sm-7 py-5 px-md-11 rounded-3 overflow-hidden">
                        <div class="bg-holder z-n1 banner-bg"
                            style="background-image:url({{ asset('assets/img/e-commerce/best-in-market-bg.png') }});">
                        </div>
                        <div class="row align-items-center w-sm-100">
                            <div class="col-8">
                                <div class="banner-text">
                                    <h2 class="text-white fw-bolder fs-sm-4 mb-5">RizqMall<br><span
                                            class="fs-7 fs-sm-6">Best in Malaysia</span></h2>
                                    <a class="btn btn-lg btn-warning rounded-pill banner-button"
                                        href="{{ route('products.index') }}">Shop Now</a>
                                </div>
                            </div>
                            <div class="col-4">
                                <img class="w-100 w-sm-75" src="{{ asset('assets/img/e-commerce/5.png') }}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DYNAMIC PRODUCT SLIDER: TOP DEALS --}}
            <div class="row g-4 mb-6">
                <div class="col-12">
                    <div class="d-flex flex-between-center mb-3">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-bolt text-warning fs-5"></span>
                            <h3 class="mx-2 mb-0">Top Deals Today</h3>
                            <span class="fas fa-bolt text-warning fs-5"></span>
                        </div>
                        <a class="btn btn-link btn-lg p-0 d-none d-md-block" href="{{ route('products.index') }}">
                            Explore more<span class="fas fa-chevron-right fs-9 ms-1"></span>
                        </a>
                    </div>

                    @if ($featuredProducts->count() > 0)
                        <div class="swiper-theme-container products-slider">
                            <div class="swiper theme-slider"
                                data-swiper='{"slidesPerView":1,"spaceBetween":16,"breakpoints":{"450":{"slidesPerView":2,"spaceBetween":16},"768":{"slidesPerView":3,"spaceBetween":20},"1200":{"slidesPerView":4,"spaceBetween":16},"1540":{"slidesPerView":5,"spaceBetween":16}}}'>
                                <div class="swiper-wrapper">
                                    @foreach ($featuredProducts as $product)
                                        <div class="swiper-slide">
                                            <div class="position-relative text-decoration-none product-card h-100">
                                                <div class="d-flex flex-column justify-content-between h-100">
                                                    <div>
                                                        <div
                                                            class="border border-1 border-translucent rounded-3 position-relative mb-3">
                                                            {{-- Wishlist Button --}}
                                                            @php
                                                                $inWishlist = in_array(
                                                                    $product->id,
                                                                    $wishlistProductIds ?? [],
                                                                );
                                                            @endphp
                                                            <button
                                                                class="btn btn-wish btn-wish-primary z-2 d-toggle-container {{ $inWishlist ? 'active' : '' }}"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                                                                onclick="event.preventDefault(); toggleWishlist({{ $product->id }}, this)">
                                                                <span
                                                                    class="fas fa-heart {{ $inWishlist ? 'd-block-hover' : 'd-none-hover' }}"
                                                                    data-fa-transform="down-1"></span>
                                                                <span
                                                                    class="far fa-heart {{ $inWishlist ? 'd-none-hover' : 'd-block-hover' }}"
                                                                    data-fa-transform="down-1"></span>
                                                            </button>

                                                            {{-- Product Image --}}
                                                            <a href="{{ route('product.show', $product->slug) }}">
                                                                @if ($product->images->isNotEmpty())
                                                                    <img class="img-fluid"
                                                                        src="{{ asset('storage/' . $product->images->first()->path) }}"
                                                                        alt="{{ $product->name }}" />
                                                                @else
                                                                    <img class="img-fluid"
                                                                        src="https://placehold.co/400x400/667eea/FFFFFF?text={{ substr($product->name, 0, 1) }}"
                                                                        alt="{{ $product->name }}" />
                                                                @endif
                                                            </a>

                                                            {{-- Badge for Sale/New --}}
                                                            @if ($product->sale_price)
                                                                <div
                                                                    class="badge bg-danger position-absolute top-0 start-0 m-2">
                                                                    {{ round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100) }}%
                                                                    OFF
                                                                </div>
                                                            @elseif($product->created_at->diffInDays(now()) <= 7)
                                                                <div
                                                                    class="badge bg-success position-absolute top-0 start-0 m-2">
                                                                    NEW
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Product Info --}}
                                                        <a class="stretched-link text-decoration-none"
                                                            href="{{ route('product.show', $product->slug) }}">
                                                            <h6
                                                                class="mb-2 lh-sm line-clamp-3 product-name text-body-emphasis">
                                                                {{ $product->name }}
                                                            </h6>
                                                        </a>

                                                        {{-- Store Name --}}
                                                        <p class="fs-9 text-body-tertiary mb-2">
                                                            <i class="fas fa-store me-1"></i>
                                                            {{ $product->store->name }}
                                                        </p>

                                                        {{-- Rating --}}
                                                        <p class="fs-9 mb-2">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                @if ($i <= floor($product->rating_average))
                                                                    <span class="fa fa-star text-warning"></span>
                                                                @elseif($i - 0.5 <= $product->rating_average)
                                                                    <span class="fa fa-star-half-alt text-warning"></span>
                                                                @else
                                                                    <span class="far fa-star text-warning-light"></span>
                                                                @endif
                                                            @endfor
                                                            <span class="text-body-quaternary fw-semibold ms-1">
                                                                ({{ $product->rating_count }} reviews)
                                                            </span>
                                                        </p>

                                                        {{-- Variants Count --}}
                                                        @if ($product->product_type === 'variable' && $product->variants->count() > 0)
                                                            <p class="fs-9 text-body-secondary mb-2">
                                                                <i class="fas fa-layer-group me-1"></i>
                                                                {{ $product->variants->count() }} variants available
                                                            </p>
                                                        @endif

                                                        {{-- Stock Status --}}
                                                        @php
                                                            $stockQty =
                                                                $product->product_type === 'simple'
                                                                    ? $product->stock_quantity
                                                                    : $product->variants->sum('stock_quantity');
                                                        @endphp
                                                        @if ($product->type !== 'service')
                                                            @if ($stockQty > 0)
                                                                <p class="fs-9 text-success mb-2">
                                                                    <i class="fas fa-check-circle me-1"></i>
                                                                    In Stock ({{ $stockQty }} available)
                                                                </p>
                                                            @else
                                                                <p class="fs-9 text-danger mb-2">
                                                                    <i class="fas fa-times-circle me-1"></i>
                                                                    Out of Stock
                                                                </p>
                                                            @endif
                                                        @else
                                                            <p class="fs-9 text-info mb-2">
                                                                <i class="fas fa-calendar-check me-1"></i>
                                                                Available for Booking
                                                            </p>
                                                        @endif
                                                    </div>

                                                    {{-- Price Section --}}
                                                    <div>
                                                        @if ($product->sale_price)
                                                            <p class="fs-9 text-danger fw-bold mb-2">On Sale!</p>
                                                        @else
                                                            <p class="fs-9 text-success fw-bold mb-2">Best Price</p>
                                                        @endif

                                                        <div class="d-flex align-items-center mb-2">
                                                            @if ($product->sale_price)
                                                                <p
                                                                    class="me-2 text-body text-decoration-line-through mb-0 fs-8">
                                                                    RM{{ number_format($product->regular_price, 2) }}
                                                                </p>
                                                                <h3 class="text-primary mb-0 fw-bold">
                                                                    RM{{ number_format($product->sale_price, 2) }}
                                                                </h3>
                                                            @else
                                                                <h3 class="text-primary mb-0 fw-bold">
                                                                    RM{{ number_format($product->regular_price, 2) }}
                                                                </h3>
                                                            @endif
                                                        </div>

                                                        {{-- Sold Count --}}
                                                        <p class="text-body-tertiary fw-semibold fs-9 lh-1 mb-2">
                                                            <i class="fas fa-shopping-bag me-1"></i>
                                                            {{ number_format($product->sold_count) }} sold
                                                        </p>

                                                        {{-- Add to Cart Button --}}
                                                        @php
                                                            $hasVariants =
                                                                $product->product_type === 'variable' &&
                                                                $product->variants->count() > 0;
                                                            $stockQty =
                                                                $product->product_type === 'simple'
                                                                    ? $product->stock_quantity
                                                                    : $product->variants->sum('stock_quantity');
                                                            $inStock = $product->type === 'service' || $stockQty > 0;
                                                        @endphp

                                                        @if ($hasVariants)
                                                            {{-- Variable product: Go to product page to select variant --}}
                                                            <a href="{{ route('product.show', $product->slug) }}"
                                                                class="btn btn-primary btn-sm w-100 z-3 position-relative">
                                                                <i class="fas fa-layer-group me-1"></i>
                                                                Select Options
                                                            </a>
                                                        @else
                                                            {{-- Simple product: Add directly to cart --}}
                                                            <button
                                                                class="btn btn-primary btn-sm w-100 z-3 position-relative"
                                                                onclick="event.preventDefault(); event.stopPropagation(); addToCartDirect({{ $product->id }}, null)"
                                                                {{ !$inStock ? 'disabled' : '' }}>
                                                                <i class="fas fa-shopping-cart me-1"></i>
                                                                {{ $inStock ? 'Add to Cart' : 'Out of Stock' }}
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="swiper-nav">
                                <div class="swiper-button-next"><span class="fas fa-chevron-right nav-icon"></span></div>
                                <div class="swiper-button-prev"><span class="fas fa-chevron-left nav-icon"></span></div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            No products available at the moment. Check back soon!
                        </div>
                    @endif

                    <a class="fw-bold d-md-none px-0" href="{{ route('products.index') }}">
                        Explore more<span class="fas fa-chevron-right fs-9 ms-1"></span>
                    </a>
                </div>
            </div>

            {{-- Services Section --}}
            @php
                $services = $featuredProducts->where('type', 'service')->take(5);
            @endphp
            @if ($services->count() > 0)
                <div class="row g-4 mb-6">
                    <div class="col-12">
                        <div class="d-flex flex-between-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-concierge-bell text-primary fs-5"></span>
                                <h3 class="mx-2 mb-0">Featured Services</h3>
                            </div>
                            <a class="btn btn-link btn-lg p-0 d-none d-md-block"
                                href="{{ route('products.index', ['type' => 'service']) }}">
                                View all<span class="fas fa-chevron-right fs-9 ms-1"></span>
                            </a>
                        </div>

                        <div class="row g-3">
                            @foreach ($services as $service)
                                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                                    <div class="card h-100 hover-shadow">
                                        <div class="card-body p-3">
                                            <a href="{{ route('product.show', $service->slug) }}"
                                                class="text-decoration-none">
                                                <div class="text-center mb-2">
                                                    <i class="fas fa-concierge-bell text-primary"
                                                        style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="mb-1 fs-9 text-body-emphasis line-clamp-2">{{ $service->name }}
                                                </h6>
                                                <p class="fs-10 text-body-tertiary mb-1">{{ $service->store->name }}</p>
                                                <p class="text-primary fw-bold mb-0">
                                                    RM{{ number_format($service->regular_price, 2) }}</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pharmacy Section --}}
            @php
                $pharmacy = $featuredProducts->where('type', 'pharmacy')->take(5);
            @endphp
            @if ($pharmacy->count() > 0)
                <div class="row g-4 mb-6">
                    <div class="col-12">
                        <div class="d-flex flex-between-center mb-3">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-pills text-success fs-5"></span>
                                <h3 class="mx-2 mb-0">Pharmacy Products</h3>
                            </div>
                            <a class="btn btn-link btn-lg p-0 d-none d-md-block"
                                href="{{ route('products.index', ['type' => 'pharmacy']) }}">
                                View all<span class="fas fa-chevron-right fs-9 ms-1"></span>
                            </a>
                        </div>

                        <div class="row g-3">
                            @foreach ($pharmacy as $drug)
                                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                                    <div class="card h-100 hover-shadow">
                                        <div class="card-body p-3">
                                            <a href="{{ route('product.show', $drug->slug) }}"
                                                class="text-decoration-none">
                                                @if ($drug->images->isNotEmpty())
                                                    <img src="{{ asset('storage/' . $drug->images->first()->path) }}"
                                                        class="img-fluid rounded mb-2" alt="{{ $drug->name }}">
                                                @else
                                                    <div class="text-center mb-2">
                                                        <i class="fas fa-pills text-success" style="font-size: 2rem;"></i>
                                                    </div>
                                                @endif
                                                <h6 class="mb-1 fs-9 text-body-emphasis line-clamp-2">{{ $drug->name }}
                                                </h6>
                                                @if ($drug->requires_prescription)
                                                    <p class="fs-10 text-danger mb-1">
                                                        <i class="fas fa-prescription me-1"></i>Rx Required
                                                    </p>
                                                @endif
                                                <p class="text-success fw-bold mb-0">
                                                    RM{{ number_format($drug->regular_price, 2) }}</p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- CTA Section --}}
            <div class="row flex-center mb-15 mt-11 gy-6">
                <div class="col-auto">
                    <img class="d-dark-none" src="{{ asset('assets/img/spot-illustrations/light_30.png') }}"
                        alt="" width="305" />
                    <img class="d-light-none" src="{{ asset('assets/img/spot-illustrations/dark_30.png') }}"
                        alt="" width="305" />
                </div>
                <div class="col-auto">
                    <div class="text-center text-lg-start">
                        <h3 class="text-body-highlight mb-2">
                            <span class="fw-semibold">Want to start </span>selling <br class="d-md-none" />on RizqMall?
                        </h3>
                        <h1 class="display-3 fw-semibold mb-4">
                            Create your <span class="text-primary fw-bolder">store </span>today!
                        </h1>
                        <a class="btn btn-lg btn-primary px-7" href="https://rm.sandboxmalaysia.com/register/">
                            Get Started<span class="fas fa-chevron-right ms-2 fs-9"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Wishlist functionality
        function toggleWishlist(productId, button) {
            // Check if user is logged in
            @guest
            window.location.href = '{{ route('login') }}';
            return;
        @endguest

        const heartFull = button.querySelector('.fas.fa-heart');
        const heartEmpty = button.querySelector('.far.fa-heart');

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
                    // Toggle heart icons based on wishlist status
                    if (data.in_wishlist) {
                        // Added to wishlist - show filled heart
                        heartFull.classList.remove('d-none-hover');
                        heartFull.classList.add('d-block-hover');
                        heartEmpty.classList.remove('d-block-hover');
                        heartEmpty.classList.add('d-none-hover');
                        button.classList.add('active');
                    } else {
                        // Removed from wishlist - show empty heart
                        heartFull.classList.remove('d-block-hover');
                        heartFull.classList.add('d-none-hover');
                        heartEmpty.classList.remove('d-none-hover');
                        heartEmpty.classList.add('d-block-hover');
                        button.classList.remove('active');
                    }

                    // Show success message
                    showToast(data.message || 'Wishlist updated successfully', 'success');
                } else {
                    showToast(data.message || 'Failed to update wishlist', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }

        // Add to Cart - Direct (for simple products)
        function addToCartDirect(productId, variantId = null) {
            @guest
            window.location.href = '{{ route('login') }}';
            return;
        @endguest

        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        if (variantId) {
            formData.append('variant_id', variantId);
        }

        fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Product added to cart!', 'success');
                    updateCartCount();
                } else {
                    showToast(data.message || 'Failed to add to cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }

        function updateCartCount() {
            fetch('{{ route('cart.count') }}')
                .then(response => response.json())
                .then(data => {
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge && data.count !== undefined) {
                        cartBadge.textContent = data.count;
                    }
                })
                .catch(error => console.error('Error updating cart count:', error));
        }

        // Toast notification function
        function showToast(message, type = 'info') {
            const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHtml;
            toastContainer.appendChild(toastElement.firstElementChild);

            const toast = new bootstrap.Toast(toastElement.firstElementChild, {
                delay: 3000
            });
            toast.show();

            toastElement.firstElementChild.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }
    </script>
@endsection
