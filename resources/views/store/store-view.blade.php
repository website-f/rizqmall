@extends('partials.app')

@section('title', $store->name . ' Store Profile')

@section('content')
<section class="pt-5 pb-9">
    <div class="container-small">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stores') }}">All Stores</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $store->name }}</li>
            </ol>
        </nav>

        {{-- Store Header Section --}}
        <div class="card mb-5 border border-translucent">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    {{-- Large Logo Placeholder --}}
                    <div class="border border-translucent d-flex flex-center rounded-3 p-5 me-4 bg-body-tertiary" style="width:100px; height:100px;">
                        <span class="fs-2 fw-bold text-primary">{{ substr($store->name, 0, 1) }}</span>
                    </div>

                    <div>
                        <h2 class="mb-1">{{ $store->name }}</h2>
                        <p class="text-body-secondary mb-1">
                            <span class="fas fa-map-marker-alt me-1"></span>{{ $store->location ?? 'Online Global Seller' }}
                        </p>
                        <p class="text-body-tertiary mb-0">{{ $store->description ?? 'No detailed description available.' }}</p>
                        
                        {{-- Dummy Rating --}}
                        <div class="mb-1 fs-9">
                            @for ($i = 0; $i < floor($dummyRating); $i++)
                                <span class="fa fa-star text-warning"></span>
                            @endfor
                            @if ($dummyRating - floor($dummyRating) >= 0.5)
                                <span class="fa fa-star-half-alt text-warning"></span>
                            @endif
                            <span class="text-body-quaternary fw-semibold ms-1">(123 Reviews)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Products Section --}}
        <h3 class="mb-4">Products by {{ $store->name }}</h3>
        
        <div class="row gx-3 gy-6 mb-8">
            @forelse ($products as $product)
                @php
                    $firstImage = $product->images->first()->path ?? 'path/to/default-image.png';
                    $onSale = !is_null($product->sale_price) && $product->sale_price < $product->regular_price;
                    $displayPrice = $onSale ? $product->sale_price : $product->regular_price;
                    $oldPrice = $onSale ? $product->regular_price : null;
                @endphp

                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="product-card-container h-100">
                        <div class="position-relative text-decoration-none product-card h-100">
                            <div class="d-flex flex-column justify-content-between h-100">
                                <div>
                                    <div class="border border-1 border-translucent rounded-3 position-relative mb-3">
                                        {{-- Wishlist button --}}
                                        <button class="btn btn-wish btn-wish-primary z-2 d-toggle-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Add to wishlist">
                                            <span class="fas fa-heart d-block-hover" data-fa-transform="down-1"></span>
                                            <span class="far fa-heart d-none-hover" data-fa-transform="down-1"></span>
                                        </button>
                                        {{-- Product Image --}}
                                        <img class="img-fluid" src="{{ asset('storage/' . $firstImage) }}" alt="{{ $product->name }}" />
                                        
                                        @if ($onSale)
                                            <span class="badge text-bg-warning fs-10 product-verified-badge">SALE</span>
                                        @endif
                                    </div>
                                    {{-- Product Name Link --}}
                                    <a class="stretched-link" href="{{ route('product.show', $product->slug) }}">
                                        <h6 class="mb-2 lh-sm line-clamp-3 product-name">{{ $product->name }}</h6>
                                    </a>
                                    {{-- Dummy Rating Display --}}
                                    <p class="fs-9">
                                        @for ($i = 0; $i < 5; $i++)
                                            <span class="fa fa-star text-warning"></span>
                                        @endfor
                                        <span class="text-body-quaternary fw-semibold ms-1">(100 ratings)</span>
                                    </p>
                                </div>
                                <div>
                                    {{-- Pricing --}}
                                    <div class="d-flex align-items-center mb-1">
                                        @if ($oldPrice)
                                            <p class="me-2 text-body text-decoration-line-through mb-0">RM{{ number_format($oldPrice, 2) }}</p>
                                        @endif
                                        <h3 class="text-body-emphasis mb-0">RM{{ number_format($displayPrice, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h4 class="text-body-secondary">This store has no products yet!</h4>
                </div>
            @endforelse
        </div>
        
        {{-- Pagination Links --}}
        <div class="d-flex justify-content-end">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
        
    </div>
</section>
@endsection