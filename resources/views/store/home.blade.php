@extends('partials.app')

@section('title', 'Rizqmall')
    
@section('content')
    <!-- ============================================-->
    <!-- Top Category Navigation -->
    <!-- ============================================-->
    <section class="py-0">
        <div class="container-small">
            <div class="scrollbar">
              <div class="d-flex justify-content-between"><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2 bg-warning-subtle" data-bs-theme="light"><span class="fs-4 uil uil-star text-warning"></span></div>
                  <p class="nav-label">Deals</p>
                </a><a class="icon-nav-item " href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-shopping-bag"></span></div>
                  <p class="nav-label">Marketplace</p>
                </a><a class="icon-nav-item d-flex flex-column align-items-center justify-content-center" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-watch-alt"></span></div>
                  <p class="nav-label">Accomodation <br>Booking & Rent</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-mobile-android"></span></div>
                  <p class="nav-label">Services</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-monitor"></span></div>
                  <p class="nav-label">Pharmacy</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-estate"></span></div>
                  <p class="nav-label">Premises</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-lamp"></span></div>
                  <p class="nav-label">Contractors</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-gift"></span></div>
                  <p class="nav-label">Food & Catering</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-wrench"></span></div>
                  <p class="nav-label">Hardware</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-plane-departure"></span></div>
                  <p class="nav-label">Delivery</p>
                </a><a class="icon-nav-item" href="#!">
                  <div class="icon-container mb-2" data-bs-theme="undefined"><span class="fs-4 uil uil-palette"></span></div>
                  <p class="nav-label">Taxi & Rent</p>
                </a>
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
                        <div class="bg-holder z-n1 product-bg" style="background-image:url(assets/img/e-commerce/whooping_banner_product.png);background-position: bottom right;">
                        </div>
                        <div class="bg-holder z-n1 shape-bg" style="background-image:url(assets/img/e-commerce/whooping_banner_shape_2.png);background-position: bottom left;">
                        </div>
                        <div class="banner-text" data-bs-theme="light">
                            <h2 class="text-warning-light fw-bolder fs-lg-3 fs-xxl-2">Whooping <span class="gradient-text">60% </span>Off</h2>
                            <h3 class="fw-bolder fs-lg-5 fs-xxl-3 text-white">on everyday items</h3>
                        </div><a class="btn btn-lg btn-primary rounded-pill banner-button" href="#!">Shop Now</a>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="gift-items-banner w-100 rounded-3 overflow-hidden">
                        <div class="bg-holder z-n1 banner-bg" style="background-image:url(assets/img/e-commerce/gift-items-banner-bg.png);">
                        </div>
                        <div class="banner-text text-md-center">
                            <h2 class="text-white fw-bolder fs-xl-4">Get <span class="gradient-text">10% Off </span><br class="d-md-none"> on gift items</h2><a class="btn btn-lg btn-primary rounded-pill banner-button" href="#!">Buy Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="best-in-market-banner d-flex h-100 px-4 px-sm-7 py-5 px-md-11 rounded-3 overflow-hidden">
                        <div class="bg-holder z-n1 banner-bg" style="background-image:url(assets/img/e-commerce/best-in-market-bg.png);">
                        </div>
                        <div class="row align-items-center w-sm-100">
                            <div class="col-8">
                                <div class="banner-text">
                                    <h2 class="text-white fw-bolder fs-sm-4 mb-5">MI 11 Pro<br><span class="fs-7 fs-sm-6"> Best in the market</span></h2><a class="btn btn-lg btn-warning rounded-pill banner-button" href="#!">Buy Now</a>
                                </div>
                            </div>
                            <div class="col-4"><img class="w-100 w-sm-75" src="assets/img/e-commerce/5.png" alt=""></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DYNAMIC PRODUCT SLIDER: TOP DEALS (using the $products variable) --}}
            <div class="row g-4 mb-6">
                <div class="col-12 col-lg-12 col-xxl-10">
                    <div class="d-flex flex-between-center mb-3">
                        <div class="d-flex"><span class="fas fa-bolt text-warning fs-6"></span>
                            <h3 class="mx-2">Top Deals today</h3><span class="fas fa-bolt text-warning fs-6"></span>
                        </div><a class="btn btn-link btn-lg p-0 d-none d-md-block" href="#!">Explore more<span class="fas fa-chevron-right fs-9 ms-1"></span></a>
                    </div>
                    
                    <div class="swiper-theme-container products-slider">
                        <div class="swiper swiper theme-slider" data-swiper='{"slidesPerView":1,"spaceBetween":16,"breakpoints":{"450":{"slidesPerView":2,"spaceBetween":16},"768":{"slidesPerView":3,"spaceBetween":20},"1200":{"slidesPerView":4,"spaceBetween":16},"1540":{"slidesPerView":5,"spaceBetween":16}}}'>
                            <div class="swiper-wrapper">
                                @forelse($products as $product)
                                    <div class="swiper-slide">
                                        <div class="position-relative text-decoration-none product-card h-100">
                                            <div class="d-flex flex-column justify-content-between h-100">
                                                <div>
                                                    <div class="border border-1 border-translucent rounded-3 position-relative mb-3">
                                                        <button class="btn btn-wish btn-wish-primary z-2 d-toggle-container" 
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top" 
                                                                title="Add to wishlist">
                                                            <span class="fas fa-heart d-block-hover" data-fa-transform="down-1"></span>
                                                            <span class="far fa-heart d-none-hover" data-fa-transform="down-1"></span>
                                                        </button>
                                
                                                        {{-- ✅ Show product image if exists, otherwise fallback to placeholder --}}
                                                        @if($product->images->isNotEmpty())
                                                            <img class="img-fluid" 
                                                                 src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                                                 alt="{{ $product->name }}" />
                                                        @else
                                                            <img class="img-fluid" 
                                                                 src="https://placehold.co/300x300/4F46E5/FFFFFF?text={{ substr($product->name, 0, 1) }}" 
                                                                 alt="{{ $product->name }}" />
                                                        @endif
                                                    </div>
                                
                                                    {{-- Placeholder link to product detail page --}}
                                                    <a class="stretched-link" href="#">
                                                        <h6 class="mb-2 lh-sm line-clamp-3 product-name">{{ $product->name }}</h6>
                                                    </a>
                                
                                                    <p class="fs-9">
                                                        <span class="fa fa-star text-warning"></span>
                                                        <span class="fa fa-star text-warning"></span>
                                                        <span class="fa fa-star text-warning"></span>
                                                        <span class="fa-regular fa-star text-warning-light"></span>
                                                        <span class="fa-regular fa-star text-warning-light"></span>
                                                        <span class="text-body-quaternary fw-semibold ms-1">
                                                            ({{ rand(5, 500) }} rated)
                                                        </span>
                                                    </p>
                                                </div>
                                
                                                <div>
                                                    <p class="fs-9 text-body-highlight fw-bold mb-2">
                                                        @if($product->sale_price) On Sale! @else New Arrival @endif
                                                    </p>
                                                    <div class="d-flex align-items-center mb-1">
                                                        @if($product->sale_price)
                                                            <p class="me-2 text-body text-decoration-line-through mb-0">
                                                                RM{{ number_format($product->regular_price, 2) }}
                                                            </p>
                                                            <h3 class="text-body-emphasis mb-0">
                                                                RM{{ number_format($product->sale_price, 2) }}
                                                            </h3>
                                                        @else
                                                            <h3 class="text-body-emphasis mb-0">
                                                                RM{{ number_format($product->regular_price, 2) }}
                                                            </h3>
                                                        @endif
                                                    </div>
                                                    <p class="text-body-tertiary fw-semibold fs-9 lh-1 mb-0">
                                                        {{ $product->variants->count() }} variants
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p>No products available at the moment.</p>
                                @endforelse

                                {{-- End Product Loop --}}
                            </div>
                        </div>
                        <div class="swiper-nav swiper-product-nav">
                            <div class="swiper-button-next"><span class="fas fa-chevron-right nav-icon"></span></div>
                            <div class="swiper-button-prev"><span class="fas fa-chevron-left nav-icon"></span></div>
                        </div>
                    </div>
                    <a class="fw-bold d-md-none px-0" href="#!">Explore more<span class="fas fa-chevron-right fs-9 ms-1"></span></a>
                </div>
              
                <div class="col-12 d-lg-none"><a href="#!"><img class="w-100 rounded-3" src="assets/img/e-commerce/6.png" alt="" /></a></div>
            </div>

           
            
            <div class="row flex-center mb-15 mt-11 gy-6">
                <div class="col-auto"><img class="d-dark-none" src="assets/img/spot-illustrations/light_30.png" alt="" width="305" /><img class="d-light-none" src="assets/img/spot-illustrations/dark_30.png" alt="" width="305" /></div>
                <div class="col-auto">
                    <div class="text-center text-lg-start">
                        <h3 class="text-body-highlight mb-2"><span class="fw-semibold">Want to have the </span>ultimate <br class="d-md-none" />customer experience?</h3>
                        <h1 class="display-3 fw-semibold mb-4">Become a <span class="text-primary fw-bolder">member </span>today!</h1><a class="btn btn-lg btn-primary px-7" href="https://rm.sandboxmalaysia.com/register/">Sign up<span class="fas fa-chevron-right ms-2 fs-9"></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
