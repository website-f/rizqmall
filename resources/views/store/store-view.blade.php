@extends('partials.app')

@section('title', $store->name . ' Store Profile')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-o9N1jV+0v+0QvC8FzCkUq/0bDjhS+PSHkP6u5A9gCtg=" crossorigin=""/>
<style>
    /* Style for the logo placeholder to ensure it's centered */
    .store-logo-placeholder { 
        width:100%; 
        height:100%; 
        object-fit:cover; 
        border-radius: 50%;
        background-color: var(--phoenix-body-tertiary-bg);
    }

    .dropzone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f9f9f9;
        }
</style>
@endsection

@section('content')
<section class="pt-5 pb-9">
    <div class="container-small">
        <nav class="mb-5" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stores') }}">All Stores</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $store->name }}</li>
            </ol>
        </nav>

        <div class="position-relative mb-5 rounded-3 overflow-hidden bg-body-tertiary" style="height: 300px; z-index: 1;">
            @if($store->banner)
                <img src="{{ asset($store->banner) }}" 
                     alt="Store Banner" 
                     class="img-fluid" 
                     style="width:100%; height: 100%; object-fit: cover;">
            @else
                <div class="d-flex flex-center h-100">
                    <h3 class="text-body-secondary">Store Banner Placeholder</h3>
                </div>
            @endif
        
            {{-- ✨ Restyled Change Banner Button - Top Left --}}
           
        </div>


        {{-- 2. Store Header / Info Card --}}
        <div class="card mb-5 border-0 shadow-sm rounded-4" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.85);">
            <div class="card-body p-4 p-md-5 d-flex flex-wrap align-items-center justify-content-between">
        
                <div class="d-flex align-items-start flex-grow-1 me-3">
                    {{-- Logo --}}
                    <div class="avatar avatar-4xl border border-light shadow-sm me-4">
                        @if($store->image)
                            <img src="{{ asset($store->image) }}" alt="Store Logo" class="rounded-circle store-logo-placeholder">
                        @else
                            <div class="avatar-name rounded-circle bg-secondary text-white fs-3 d-flex align-items-center justify-content-center">
                                {{ substr($store->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
        
                    {{-- Store Text Details --}}
                    <div>
                        <h2 class="mb-1 fw-bold text-body-emphasis">{{ $store->name }}</h2>
        
                        @if($store->location)
                            <p class="text-body-secondary mb-1 fs-9">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $store->location }}
                            </p>
                        @endif
        
                        {{-- ✨ New Contact Info Section --}}
                        <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 fs-9 mb-2 text-body-secondary">
                            @if($store->phone)
                                <div><i class="fas fa-phone me-1 text-success"></i> {{ $store->phone }}</div>
                            @endif
                            @if($store->email)
                                <div><i class="fas fa-envelope me-1 text-primary"></i> {{ $store->email }}</div>
                            @endif
                        </div>
        
                        {{-- Description --}}
                        <p class="text-body-tertiary fs-9 mb-2">
                            {{ $store->description ?? 'No detailed description available.' }}
                        </p>
        
                        {{-- Rating --}}
                        <div class="fs-9">
                            @for ($i = 0; $i < floor($dummyRating); $i++)
                                <span class="fa fa-star text-warning"></span>
                            @endfor
                            @if ($dummyRating - floor($dummyRating) >= 0.5)
                                <span class="fa fa-star-half-alt text-warning"></span>
                            @endif
                            @for ($i = ceil($dummyRating); $i < 5; $i++)
                                <span class="far fa-star text-warning-light"></span>
                            @endfor
                            <span class="text-body-quaternary fw-semibold ms-1">(123 Reviews)</span>
                        </div>
                    </div>
                </div>
        
                {{-- Add Product & Change Banner Buttons (Centered) --}}
                @if(session('auth_user_id') && session('auth_user_id') == $store->auth_user_id)
                    <div class="w-100 mt-4 d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ route('store.products', ['store' => $store->id]) }}" 
                           class="btn btn-primary rounded-pill px-4 shadow-sm d-flex align-items-center">
                            <i class="fas fa-plus me-1"></i> Add Product
                        </a>
                
                        <button 
                            class="btn btn-warning rounded-pill px-4 shadow-sm d-flex align-items-center"
                            data-bs-toggle="modal" 
                            data-bs-target="#changeBannerModal"
                        >
                            <i class="fas fa-edit me-1"></i>
                            <span class="fw-semibold d-none d-sm-inline">Change Banner</span>
                        </button>
                    </div>
                @endif

        
            </div>
        </div>

        {{-- 3. Map Section --}}
        @if(isset($store->latitude) && isset($store->longitude))
            <h3 class="mb-3 text-body-emphasis">Our Location</h3>
           
        @else
          <h3>No Map Recorded</h3>
        @endif

        <hr class="my-6">
        
        {{-- 4. Products Section --}}
        <h3 class="mb-4 text-body-emphasis">Products by {{ $store->name }}</h3>
        <div class="row gx-3 gy-6 mb-8">
            @forelse ($products as $product)
                @php
                    $firstImage = $product->images->first()->path ?? 'path/to/default-image.png';
                    $onSale = !is_null($product->sale_price) && $product->sale_price < $product->regular_price;
                    $displayPrice = $onSale ? $product->sale_price : $product->regular_price;
                    $oldPrice = $onSale ? $product->regular_price : null;
                @endphp
                {{-- Use flexible columns: 2 per row on small, 3 on tablet, 4 on small desktop, 6 on large desktop --}}
                <div class="col-6 col-sm-4 col-md-3 col-lg-2"> 
                    <div class="product-card-container h-100">
                        <div class="position-relative text-decoration-none product-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="border border-1 border-translucent rounded-3 position-relative mb-3 bg-body-tertiary">
                                    <button class="btn btn-wish btn-wish-primary z-2 d-toggle-container" data-bs-toggle="tooltip" title="Add to wishlist">
                                        <span class="fas fa-heart d-block-hover" data-fa-transform="down-1"></span>
                                        <span class="far fa-heart d-none-hover" data-fa-transform="down-1"></span>
                                    </button>
                                    <img class="img-fluid rounded-3" src="{{ asset('storage/' . $firstImage) }}" alt="{{ $product->name }}" />
                                    @if ($onSale)
                                        <span class="badge text-bg-warning fs-10 product-verified-badge">SALE</span>
                                    @endif
                                </div>
                                <a class="stretched-link" href="{{ route('product.show', $product->slug) }}">
                                    <h6 class="mb-1 lh-sm line-clamp-2 product-name text-body-emphasis">{{ $product->name }}</h6>
                                </a>
                                <p class="fs-10 text-body-secondary mb-1">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                <p class="fs-9">
                                    @for ($i = 0; $i < 5; $i++)
                                        <span class="fa fa-star text-warning"></span>
                                    @endfor
                                    <span class="text-body-quaternary fw-semibold ms-1">(100)</span>
                                </p>
                            </div>
                            <div>
                                <div class="d-flex align-items-center mt-1">
                                    @if ($oldPrice)
                                        <p class="me-2 text-body-tertiary text-decoration-line-through mb-0 fs-10">RM{{ number_format($oldPrice, 2) }}</p>
                                    @endif
                                    <h4 class="text-body-emphasis mb-0 fs-7">RM{{ number_format($displayPrice, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 border-dashed border-top rounded-3">
                    <h4 class="text-body-secondary">This store has no products yet!</h4>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</section>

{{-- Change Banner Modal (Kept unchanged) --}}
@if(session('auth_user_id') && session('auth_user_id') == $store->auth_user_id)
<div class="modal fade" id="changeBannerModal" tabindex="-1" aria-labelledby="changeBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="bannerDropzoneForm" action="{{ route('store.changeBanner', $store->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="changeBannerModalLabel">Change Store Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bannerDropzone" class="dropzone"></div>
                <input type="hidden" name="banner_path" id="banner_path">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="uploadBannerBtn">Upload Banner</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')




<script>
Dropzone.autoDiscover = false;

document.addEventListener('DOMContentLoaded', () => {
    const bannerDropzone = new Dropzone("#bannerDropzone", {
        url: "{{ route('uploads.temp') }}",
        paramName: 'file',
        maxFiles: 1,
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        init: function() {
            this.on("success", function(file, response) {
                document.getElementById('banner_path').value = response.path;
            });
            this.on("removedfile", function() {
                document.getElementById('banner_path').value = '';
            });
        }
    });

    document.getElementById('bannerDropzoneForm').addEventListener('submit', function(e) {
        if (!document.getElementById('banner_path').value) {
            e.preventDefault();
            alert('Please upload a banner image first.');
        }
    });
});
</script>
@endpush