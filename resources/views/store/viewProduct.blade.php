@extends('partials.app')

@section('title', $product->name . ' - Rizqmall')

@section('content')
<div class="pt-5 pb-9">
    <section class="py-0">

        <div class="container-small">
            {{-- Breadcrumb --}}
            <nav class="mb-3" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                    @if ($product->category)
                        <li class="breadcrumb-item"><a href="#">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
            
            <div class="row g-5 mb-5 mb-lg-8" data-product-details="data-product-details">
                
                {{-- 1. PRODUCT IMAGES SECTION (COL-LG-6) --}}
                <div class="col-12 col-lg-6">
                    <div class="row g-3 mb-3">
                        
                        {{-- Thumbnails Column: Vertical Layout --}}
                        <div class="col-3 col-md-2 col-lg-2 col-xl-2">
                            <div class="swiper swiper-products-thumb theme-slider overflow-visible" id="swiper-products-thumb">
                                {{-- Use flex-column to stack thumbnails vertically --}}
                                <div class="swiper-wrapper flex-column"> 
                                    @forelse ($product->images as $image)
                                        <div class="swiper-slide text-center mb-2"> {{-- Added mb-2 for vertical spacing --}}
                                            <img
                                                class="cursor-pointer rounded-2 border border-translucent p-1 w-100"
                                                src="{{ asset('storage/' . $image->path) }}"
                                                alt="{{ $product->name }} thumbnail {{ $loop->iteration }}"
                                                width="70"
                                            />
                                        </div>
                                    @empty
                                        <div class="swiper-slide text-center mb-2">
                                            <img class="cursor-pointer rounded-2 border border-translucent p-1 w-100" src="{{ asset('assets/img/default-product.png') }}" alt="Default Product Image" width="70" />
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Main Image Display Column --}}
                        <div class="col-9 col-md-10 col-lg-10 col-xl-10">
                            <div class="d-flex align-items-center border border-translucent rounded-3 text-center p-5 h-100 bg-body-tertiary">
                                <div class="swiper theme-slider w-100" id="swiper-products-main">
                                    <div class="swiper-wrapper">
                                        @forelse ($product->images as $image)
                                            <div class="swiper-slide">
                                                <a href="{{ asset('storage/' . $image->path) }}" data-gallery="product-gallery">
                                                    <img class="img-fluid rounded" src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }} Image {{ $loop->iteration }}" />
                                                </a>
                                            </div>
                                        @empty
                                            <div class="swiper-slide">
                                                <img class="img-fluid rounded" src="{{ asset('assets/img/default-product.png') }}" alt="Default Product Image" />
                                            </div>
                                        @endforelse
                                    </div>
                                    {{-- Swiper navigation and pagination are usually handled by theme JS, but adding containers is safe --}}
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-3">
                        <button class="btn btn-lg btn-outline-warning rounded-pill w-100 fs-9 fs-sm-8"><span class="me-2 far fa-heart"></span>Add to wishlist</button>
                        <button class="btn btn-lg btn-warning rounded-pill w-100 fs-9 fs-sm-8"><span class="fas fa-shopping-cart me-2"></span>Add to cart</button>
                    </div>
                </div>
                
                {{-- 2. PRODUCT DETAILS & ACTIONS SECTION (COL-LG-6) --}}
                <div class="col-12 col-lg-6">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <div>
                            {{-- Rating --}}
                            <div class="d-flex flex-wrap align-items-center mb-2">
                                <div class="me-2 text-warning">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($dummyRating >= $i)
                                            <span class="fa fa-star"></span>
                                        @elseif ($dummyRating > ($i - 1))
                                            <span class="fa fa-star-half-alt"></span>
                                        @else
                                            <span class="fa-regular fa-star text-warning-light"></span>
                                        @endif
                                    @endfor
                                </div>
                                <p class="text-primary fw-semibold mb-0 fs-9">({{ number_format($dummyReviewsCount) }} ratings) </p>
                            </div>

                            {{-- Product Name and Description Snippet --}}
                            <h3 class="mb-3 lh-sm text-body-emphasis">{{ $product->name }}</h3>
                            <p class="text-body-secondary mb-4 line-clamp-3">{{ Str::limit(strip_tags($product->description), 150) }}</p>

                            {{-- Tags / Badges --}}
                            <div class="d-flex flex-wrap align-items-start mb-4 gap-2">
                                @foreach ($product->tags as $tag)
                                    <span class="badge text-bg-info fs-10 rounded-pill fw-semibold">{{ $tag->name }}</span>
                                @endforeach
                                <span class="badge text-bg-success fs-9 rounded-pill fw-semibold">#1 Best Seller</span>
                            </div>

                            {{-- Pricing --}}
                            <div class="d-flex flex-wrap align-items-center mb-1">
                                <h1 class="me-3 text-primary">RM{{ number_format($price, 2) }}</h1>
                                @if ($oldPrice)
                                    <p class="text-body-quaternary text-decoration-line-through fs-6 mb-0 me-3">RM{{ number_format($oldPrice, 2) }}</p>
                                @endif
                                @if ($discountPercentage)
                                    <p class="text-warning fw-bolder fs-6 mb-0">{{ $discountPercentage }}% off</p>
                                @endif
                            </div>

                            {{-- Stock/Shipping Info --}}
                            <p class="text-{{ $inStock ? 'success' : 'danger' }} fw-semibold fs-7 mb-4">{{ $inStock ? 'In stock' : 'Out of stock' }}</p>

                        </div>

                        {{-- Variants and Quantity Selector --}}
                        <div>
                            @if ($product->variants->isNotEmpty())
                                <div class="mb-4">
                                    <p class="fw-semibold mb-2 text-body-emphasis">Select Option:</p>
                                    <div class="d-flex flex-wrap gap-2" id="variant-selector">
                                        @foreach ($product->variants as $variant)
                                            <button 
                                                class="btn btn-sm btn-outline-secondary variant-btn" 
                                                data-variant-id="{{ $variant->id }}"
                                                data-price="{{ $variant->price ?? $product->regular_price }}"
                                            >
                                                {{ $variant->name }}
                                                <small class="text-body-tertiary ms-1">(RM{{ number_format($variant->price ?? $product->regular_price, 2) }})</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3 g-sm-5 align-items-end">
                                <div class="col-12 col-sm-auto">
                                    <p class="fw-semibold mb-2 text-body-emphasis">Quantity : </p>
                                    <div class="d-flex align-items-center" data-quantity-wrapper>
                                        <button class="btn btn-phoenix-secondary px-3 border" data-qty-action="minus"><span class="fas fa-minus"></span></button>
                                        <input
                                            id="product-quantity"
                                            class="form-control text-center input-spin-none bg-transparent border-0 outline-none"
                                            style="width:70px;"
                                            type="number"
                                            min="1"
                                            value="1"
                                            max="{{ $product->stock_quantity ?? 9999 }}"
                                        />
                                        <button class="btn btn-phoenix-secondary px-3 border" data-qty-action="plus"><span class="fas fa-plus"></span></button>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <button class="btn btn-link px-0 text-body-tertiary"><span class="fas fa-share-alt me-2"></span>Share</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Product Tabs (Description, Specs, Reviews) --}}
    <section class="py-0">
        <div class="container-small">
            <ul class="nav nav-underline fs-9 mb-4" id="productTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#tab-description" role="tab" aria-controls="tab-description" aria-selected="true">Description</a></li>
                <li class="nav-item"><a class="nav-link" id="specification-tab" data-bs-toggle="tab" href="#tab-specification" role="tab" aria-controls="tab-specification" aria-selected="false">Details / Specs</a></li>
                <li class="nav-item"><a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#tab-reviews" role="tab" aria-controls="tab-reviews" aria-selected="false">Ratings &amp; reviews</a></li>
            </ul>
            <div class="row gx-3 gy-7">
                <div class="col-12 col-lg-7 col-xl-8">
                    <div class="tab-content" id="productTabContent">
                        
                        {{-- DESCRIPTION TAB --}}
                        <div class="tab-pane pe-lg-6 pe-xl-12 fade show active text-body-emphasis" id="tab-description" role="tabpanel" aria-labelledby="description-tab">
                            <p class="mb-5">{!! nl2br(e($product->description)) !!}</p>
                            @if ($product->images->isNotEmpty())
                                <a href="{{ asset('storage/' . $product->images->first()->path) }}" data-gallery="gallery-description">
                                    <img class="img-fluid mb-5 rounded-3 border" src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }} large image">
                                </a>
                            @endif
                        </div>

                        {{-- SPECIFICATION TAB --}}
                        <div class="tab-pane pe-lg-6 pe-xl-12 fade" id="tab-specification" role="tabpanel" aria-labelledby="specification-tab">
                            @if ($product->category)
                                <h3 class="mb-3 ms-4 fw-bold text-body-emphasis">Category</h3>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="bg-body-highlight align-middle" style="width: 40%">
                                                <h6 class="mb-0 text-body text-uppercase fw-bolder px-4 fs-9 lh-sm">Product Category</h6>
                                            </td>
                                            <td class="px-5 mb-0" style="width: 60%">{{ $product->category->name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif

                            @if (!empty($attributes))
                                <h3 class="mb-3 mt-6 ms-4 fw-bold text-body-emphasis">Product Details</h3>
                                <table class="table">
                                    <tbody>
                                        @foreach ($attributes as $attribute)
                                            <tr>
                                                <td class="bg-body-highlight align-middle" style="width: 40%">
                                                    <h6 class="mb-0 text-body text-uppercase fw-bolder px-4 fs-9 lh-sm">
                                                        {{ Str::before($attribute, ':') }}
                                                    </h6>
                                                </td>
                                                <td class="px-5 mb-0" style="width: 60%">
                                                    {{ Str::contains($attribute, ':') ? Str::after($attribute, ':') : 'Yes' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            
                            <h3 class="mb-3 mt-6 ms-4 fw-bold text-body-emphasis">Tags</h3>
                            <div class="px-4">
                                @forelse ($product->tags as $tag)
                                    <span class="badge bg-primary me-2 mb-2">{{ $tag->name }}</span>
                                @empty
                                    <p class="lh-sm mb-0 px-4 text-body-secondary">No tags assigned.</p>
                                @endforelse
                            </div>
                        </div>

                       
                        <div class="tab-pane fade" id="tab-reviews" role="tabpanel" aria-labelledby="reviews-tab">
                           
                        </div>
                    </div>
                </div>
                
                {{-- 3. Side Content / Upsell (Placeholder) --}}
                <div class="col-12 col-lg-5 col-xl-4">
                    {{-- This space is perfect for an "Also Bought" card or Vendor Info --}}
                    <div class="card p-4 border rounded-3 sticky-top" style="top: 20px;">
                        <h5 class="text-body-emphasis mb-3">Shop from this Vendor</h5>
                        <p class="text-body-secondary mb-0">Vendor Name: **{{ $product->store->name ?? 'RizqMall Seller' }}**</p>
                        <hr class="my-3">
                        <h5 class="text-body-emphasis mb-3">Frequently Bought Together</h5>
                        {{-- Dummy FBT list structure --}}
                        <div class="d-flex align-items-center mb-3">
                            <input class="form-check-input me-3" type="checkbox" checked>
                            <img src="{{ asset('assets/img/products/1.png') }}" class="rounded me-3" width="50" alt="Accessory">
                            <a href="#" class="fs-9 fw-semibold text-body-emphasis">Product Accessory A</a>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary w-100">Add 2 items to cart (RMXX.XX)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    

</div>

{{-- Custom JavaScript for Swiper and Interactions --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. SWIPER INITIALIZATION ---
        const mainSwiperEl = document.getElementById('swiper-products-main');
        const thumbsSwiperEl = document.getElementById('swiper-products-thumb');

        if (typeof Swiper !== 'undefined' && mainSwiperEl && thumbsSwiperEl) {
            // Check screen size for direction
            const isDesktop = window.innerWidth >= 992;
            
            // THUMBS SWIPER (Small Thumbnails)
            const thumbsSwiper = new Swiper(thumbsSwiperEl, {
                spaceBetween: 10,
                slidesPerView: 4,
                freeMode: true,
                watchSlidesProgress: true,
                // Set vertical on desktop/large screens, horizontal on smaller screens
                direction: isDesktop ? 'vertical' : 'horizontal', 
                breakpoints: {
                    0: { direction: 'horizontal', slidesPerView: 4 },
                    768: { direction: 'horizontal', slidesPerView: 4 },
                    992: { direction: 'vertical', slidesPerView: 4 }
                },
            });

            // MAIN SWIPER (Large Display Image)
            const mainSwiper = new Swiper(mainSwiperEl, {
                spaceBetween: 16,
                slidesPerView: 1,
                loop: false, // Set to true if you want endless looping
                navigation: {
                    nextEl: mainSwiperEl.querySelector('.swiper-button-next'),
                    prevEl: mainSwiperEl.querySelector('.swiper-button-prev'),
                },
                thumbs: {
                    swiper: thumbsSwiper
                },
            });
        }
        
        // --- 2. QUANTITY BUTTONS ---
        const qtyInput = document.getElementById('product-quantity');
        if (qtyInput) {
            const min = parseInt(qtyInput.getAttribute('min') || '1', 10);
            const max = parseInt(qtyInput.getAttribute('max') || '9999', 10);

            document.querySelectorAll('[data-qty-action]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const action = this.getAttribute('data-qty-action');
                    let current = parseInt(qtyInput.value || '1', 10);
                    if (action === 'plus') {
                        current = Math.min(max, current + 1);
                    } else if (action === 'minus') {
                        current = Math.max(min, current - 1);
                    }
                    qtyInput.value = current;
                });
            });
        }

        // --- 3. VARIANT SELECTION LOGIC ---
        document.querySelectorAll('.variant-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove 'active' class from all buttons
                document.querySelectorAll('.variant-btn').forEach(btn => {
                    btn.classList.remove('active', 'btn-secondary');
                    btn.classList.add('btn-outline-secondary');
                });

                // Add 'active' class to the clicked button
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-secondary');
                
                const variantId = this.getAttribute('data-variant-id');
                const variantPrice = parseFloat(this.getAttribute('data-price')).toFixed(2);
                
                // Update the main price display (optional, but good UX)
                const priceElement = document.querySelector('.d-flex.flex-wrap.align-items-center h1.me-3');
                if(priceElement) {
                     priceElement.innerHTML = `RM${variantPrice}`;
                }
                
                // Here you would typically update hidden form fields for cart submission
                console.log(`Variant Selected: ID ${variantId}, Price RM${variantPrice}`);
            });
        });
        
        // --- 4. GALLERY LIGHTBOX ---
        if (typeof GLightbox !== 'undefined') {
            GLightbox({ selector: '[data-gallery="product-gallery"]' });
        }
    });
</script>

@endsection