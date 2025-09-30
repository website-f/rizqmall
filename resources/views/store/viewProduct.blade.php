@extends('partials.app')

@section('title', $product->name . ' - Rizqmall')

@section('content')
<div class="pt-5 pb-9">
    <section class="py-0">

      <div class="container-small">
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
            <div class="col-12 col-lg-6">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-2 col-lg-12 col-xl-2">
                        <div class="swiper-products-thumb swiper swiper theme-slider overflow-visible" id="swiper-products-thumb">
                            <div class="swiper-wrapper">
                                @forelse ($product->images as $image)
                                    <div class="swiper-slide text-center">
                                        {{-- Use 'asset' with 'storage' to access public-facing storage --}}
                                        <img class="cursor-pointer rounded-2 w-100" src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }} thumbnail {{ $loop->index + 1 }}" width="70" />
                                    </div>
                                @empty
                                    <div class="swiper-slide text-center">
                                        <img class="cursor-pointer rounded-2 w-100" src="path/to/default-image.png" alt="Default Product Image" width="70" />
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-10 col-lg-12 col-xl-10">
                        <div class="d-flex align-items-center border border-translucent rounded-3 text-center p-5 h-100">
                            {{-- IMPORTANT: You will need to dynamically set the images array here if your Swiper relies on it for data-products-swiper --}}
                            <div class="swiper swiper theme-slider" data-thumb-target="swiper-products-thumb" data-products-swiper='{"slidesPerView":1,"spaceBetween":16,"thumbsEl":".swiper-products-thumb"}'>
                                <div class="swiper-wrapper">
                                    @forelse ($product->images as $image)
                                        <div class="swiper-slide">
                                            <a href="{{ asset('storage/' . $image->path) }}" data-gallery="product-gallery">
                                                <img class="img-fluid" src="{{ asset('storage/' . $image->path) }}" alt="{{ $product->name }} Image {{ $loop->index + 1 }}" />
                                            </a>
                                        </div>
                                    @empty
                                        <div class="swiper-slide">
                                            <img class="img-fluid" src="path/to/default-image.png" alt="Default Product Image" />
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex">
                    <button class="btn btn-lg btn-outline-warning rounded-pill w-100 me-3 px-2 px-sm-4 fs-9 fs-sm-8"><span class="me-2 far fa-heart"></span>Add to wishlist</button>
                    <button class="btn btn-lg btn-warning rounded-pill w-100 fs-9 fs-sm-8"><span class="fas fa-shopping-cart me-2"></span>Add to cart</button>
                </div>
            </div>
            
            <div class="col-12 col-lg-6">
                <div class="d-flex flex-column justify-content-between h-100">
                    <div>
                        <div class="d-flex flex-wrap">
                            {{-- Dummy Rating --}}
                            <div class="me-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($dummyRating >= $i)
                                        <span class="fa fa-star text-warning"></span>
                                    @elseif ($dummyRating > ($i - 1))
                                        <span class="fa fa-star-half-alt star-icon text-warning"></span>
                                    @else
                                        <span class="fa-regular fa-star text-warning-light" data-bs-theme="light"></span>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-primary fw-semibold mb-2">{{ number_format($dummyReviewsCount) }} People rated and reviewed </p>
                        </div>
                        
                        {{-- Product Name --}}
                        <h3 class="mb-3 lh-sm">{{ $product->name }}</h3>
                        
                        {{-- Tags --}}
                        <div class="d-flex flex-wrap align-items-start mb-3">
                            @foreach ($product->tags as $tag)
                                <span class="badge text-bg-secondary fs-10 rounded-pill me-2 fw-semibold">{{ $tag->name }}</span>
                            @endforeach
                            {{-- Placeholder for Best Seller --}}
                             <span class="badge text-bg-success fs-9 rounded-pill me-2 fw-semibold">#1 Best seller</span>
                        </div>
                        
                        {{-- Pricing --}}
                        <div class="d-flex flex-wrap align-items-center">
                            <h1 class="me-3">RM{{ number_format($price, 2) }}</h1>
                            @if ($oldPrice)
                                <p class="text-body-quaternary text-decoration-line-through fs-6 mb-0 me-3">RM{{ number_format($oldPrice, 2) }}</p>
                            @endif
                            @if ($discountPercentage)
                                <p class="text-warning fw-bolder fs-6 mb-0">{{ $discountPercentage }}% off</p>
                            @endif
                        </div>
                        
                        {{-- Stock Status --}}
                        <p class="text-{{ $inStock ? 'success' : 'danger' }} fw-semibold fs-7 mb-2">{{ $inStock ? 'In stock' : 'Out of stock' }}</p>
                        
                        {{-- Availability / Shipping Info Placeholder --}}
                        <p class="mb-2 text-body-secondary"><strong class="text-body-highlight">Fast Shipping!</strong> Check delivery options at checkout.</p>
                        <p class="text-danger-dark fw-bold mb-5 mb-lg-0">Limited stock available.</p>
                    </div>
                    
                    <div>
                        {{-- Variants (Colors/Sizes from database) --}}
                        @if ($product->variants->isNotEmpty())
                        <div class="mb-3">
                             {{-- Grouping variants is complex. Here, we just list them all. --}}
                            <p class="fw-semibold mb-2 text-body">Available Variants:</p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($product->variants as $variant)
                                    <button class="btn btn-sm btn-outline-secondary" data-variant-id="{{ $variant->id }}">
                                        {{ $variant->name }} (RM{{ number_format($variant->price ?? $product->regular_price, 2) }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 g-sm-5 align-items-end">
                            {{-- Quantity Selector (Keep dummy for now) --}}
                            <div class="col-12 col-sm">
                                <p class="fw-semibold mb-2 text-body">Quantity : </p>
                                <div class="d-flex justify-content-between align-items-end">
                                    <div class="d-flex flex-between-center" data-quantity="data-quantity">
                                        <button class="btn btn-phoenix-primary px-3" data-type="minus"><span class="fas fa-minus"></span></button>
                                        <input class="form-control text-center input-spin-none bg-transparent border-0 outline-none" style="width:50px;" type="number" min="1" value="1" max="{{ $product->stock_quantity }}" />
                                        <button class="btn btn-phoenix-primary px-3" data-type="plus"><span class="fas fa-plus"></span></button>
                                    </div>
                                    <button class="btn btn-phoenix-primary px-3 border-0"><span class="fas fa-share-alt fs-7"></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      </section>
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
                    {{-- Display the first product image again in the description for rich content --}}
                    @if ($product->images->isNotEmpty())
                        <a href="{{ asset('storage/' . $product->images->first()->path) }}" data-gallery="gallery-description">
                            <img class="img-fluid mb-5 rounded-3" src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }} large image">
                        </a>
                    @endif
                </div>

                {{-- SPECIFICATION TAB (Using Product Model Attributes) --}}
                <div class="tab-pane pe-lg-6 pe-xl-12 fade" id="tab-specification" role="tabpanel" aria-labelledby="specification-tab">
                    
                    @if ($product->category)
                        <h3 class="mb-0 ms-4 fw-bold">Category</h3>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="bg-body-highlight align-middle">
                                        <h6 class="mb-0 text-body text-uppercase fw-bolder px-4 fs-9 lh-sm">Product Category</h6>
                                    </td>
                                    <td class="px-5 mb-0">{{ $product->category->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    @if (!empty($attributes))
                        <h3 class="mb-0 mt-6 ms-4 fw-bold">Product Details</h3>
                        <table class="table">
                            <tbody>
                                @foreach ($attributes as $attribute)
                                    <tr>
                                        <td class="bg-body-highlight align-middle">
                                            <h6 class="mb-0 text-body text-uppercase fw-bolder px-4 fs-9 lh-sm">
                                                {{ Str::before($attribute, ':') }}
                                            </h6>
                                        </td>
                                        <td class="px-5 mb-0">
                                            {{ Str::contains($attribute, ':') ? Str::after($attribute, ':') : 'Yes' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    
                    <h3 class="mb-3 mt-6 ms-4 fw-bold">Tags</h3>
                    <div class="px-4">
                        @forelse ($product->tags as $tag)
                            <span class="badge bg-primary me-2 mb-2">{{ $tag->name }}</span>
                        @empty
                            <p class="lh-sm mb-0 px-4">No tags assigned.</p>
                        @endforelse
                    </div>

                </div>

                {{-- REVIEWS TAB (Using Dummy Data) --}}
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <div class="bg-body-emphasis rounded-3 p-4 border border-translucent">
                        <div class="row g-3 justify-content-between mb-4">
                            <div class="col-auto">
                                <div class="d-flex align-items-center flex-wrap">
                                    <h2 class="fw-bolder me-3">{{ number_format($dummyRating, 1) }}<span class="fs-8 text-body-quaternary fw-bold">/5</span></h2>
                                    <div class="me-3">
                                        {{-- Dummy Stars based on dummyRating --}}
                                        @php $fullStars = floor($dummyRating); $hasHalf = ($dummyRating - $fullStars) >= 0.5; @endphp
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $fullStars)
                                                <span class="fa fa-star text-warning fs-6"></span>
                                            @elseif ($i == $fullStars + 1 && $hasHalf)
                                                <span class="fa fa-star-half-alt star-icon text-warning fs-6"></span>
                                            @else
                                                <span class="fa-regular fa-star text-warning fs-6"></span>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-body mb-0 fw-semibold fs-7">{{ number_format($dummyReviewsCount) }} ratings and 567 reviews</p>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#reviewModal">Rate this product</button>
                                {{-- ... (Modal content for review form remains the same) ... --}}
                            </div>
                        </div>
                        {{-- Keep dummy review content below for layout purposes --}}
                        <div class="mb-4 hover-actions-trigger btn-reveal-trigger">
                            <div class="d-flex justify-content-between">
                              <h5 class="mb-2"><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="text-body-secondary ms-1"> by</span> Zingko Kudobum
                              </h5>
                              {{-- ... (rest of the dummy review) ... --}}
                            </div>
                            <p class="text-body-tertiary fs-9 mb-1">35 mins ago</p>
                            <p class="text-body-highlight mb-3">100% satisfied</p>
                            <div class="row g-2 mb-2">
                              {{-- ... (dummy images) ... --}}
                            </div>
                            <div class="d-flex"><span class="fas fa-reply fa-rotate-180 me-2"></span>
                              <div>
                                <h5>Respond from store<span class="text-body-tertiary fs-9 ms-2">5 mins ago</span></h5>
                                <p class="text-body-highlight mb-0">Thank you for your valuable feedback</p>
                              </div>
                            </div>
                            {{-- ... (hover actions) ... --}}
                        </div>
                        {{-- ... (remaining dummy reviews and pagination) ... --}}
                    </div>
                </div>
            </div>
          </div>
          <div class="col-12 col-lg-5 col-xl-4">
            <div class="card">
              <div class="card-body">
                <h5 class="text-body-emphasis">Usually Bought Together</h5>
                {{-- Keep the "Bought Together" content static for now, as it's not product-specific logic --}}
                <div class="w-75">
                  <p class="text-body-tertiary fs-9 fw-bold line-clamp-1">with {{ $product->name }}</p>
                </div>
                <div class="border-dashed border-y border-translucent py-4">
                  <div class="d-flex align-items-center mb-5">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" checked="checked" />
                      <label class="form-check-label"></label>
                    </div><a href="#!"> <img class="border border-translucent rounded" src="../../../assets/img/products/2.png" width="53" alt="" /></a>
                    <div class="ms-2"><a class="fs-9 fw-bold line-clamp-2 mb-2" href="#!"> iPhone 13 pro max-Pacific Blue- 128GB</a>
                      <h5>RM899.99</h5>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-5">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" checked="checked" />
                      <label class="form-check-label"></label>
                    </div><a href="#!"> <img class="border border-translucent rounded" src="../../../assets/img/products/16.png" width="53" alt="" /></a>
                    <div class="ms-2"><a class="fs-9 fw-bold line-clamp-2 mb-2" href="#!">Apple AirPods Pro</a>
                      <h5>RM59.00</h5>
                    </div>
                  </div>
                  <div class="d-flex align-items-center mb-0">
                    <div class="form-check mb-0">
                      <input class="form-check-input" type="checkbox" />
                      <label class="form-check-label"></label>
                    </div><a href="#!"> <img class="border border-translucent rounded" src="../../../assets/img/products/10.png" width="53" alt="" /></a>
                    <div class="ms-2"><a class="fs-9 fw-bold line-clamp-2 mb-2" href="#!">Apple Magic Mouse (Wireless, Rechargable) - Silver</a>
                      <h5>RM89.00</h5>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-end justify-content-between pt-3">
                  <div>
                    <h5 class="mb-2 text-body-tertiary text-opacity-85">Total</h5>
                    <h4 class="mb-0 text-body-emphasis">RM958.99</h4>
                  </div>
                  <div class="btn btn-outline-warning">Add 3 items to cart<span class="fas fa-shopping-cart ms-2"></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </section>
    {{-- ... (Similar Products Section remains the same, but remember to update currency symbols if you extend it) ... --}}

</div>
@endsection