@extends('partials.app')

@section('title', $store->name . ' Store Profile')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-o9N1jV+0v+0QvC8FzCkUq/0bDjhS+PSHkP6u5A9gCtg=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone.css"/>
<style>
    /* Ensure the map container has height and corner styling */
    #storeLocationMap { border-radius: 10px; z-index: 1; height: 350px !important; } 
    /* Style for the logo placeholder to ensure it's centered */
    .store-logo-placeholder { 
        width:100%; 
        height:100%; 
        object-fit:cover; 
        border-radius: 50%;
        background-color: var(--phoenix-body-tertiary-bg);
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

        {{-- 1. Banner Section (with edit button) --}}
        {{-- Banner --}}
<div class="position-relative mb-5 rounded-3 overflow-hidden bg-body-tertiary" style="height: 300px;">
    @if($store->banner)
        <img src="{{ asset($store->banner) }}" alt="Store Banner" class="img-fluid" style="width:100%; height: 100%; object-fit: cover;">
    @else
        <div class="d-flex flex-center h-100"><h3 class="text-body-secondary">Store Banner Placeholder</h3></div>
    @endif

    {{-- The Edit Button with Z-index to ensure visibility --}}
    @if(session('auth_user_id') && session('auth_user_id') == $store->auth_user_id)
        <button 
            class="btn btn-warning position-absolute top-3 end-3 shadow-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#changeBannerModal"
            style="z-index: 10;" {{-- Add z-index to bring it forward --}}
        >
            <span class="fas fa-edit me-1"></span> Change Banner
        </button>
    @endif
</div>

        {{-- 2. Store Header / Info Card --}}
        <div class="card mb-5 border border-translucent shadow-sm">
            <div class="card-body p-4 p-md-5 d-flex align-items-center justify-content-between flex-wrap">
                
                <div class="d-flex align-items-center flex-grow-1 me-3">
                    {{-- Logo/Image --}}
                    <div class="avatar avatar-3xl border border-translucent me-4">
                        @if($store->image)
                            <img src="{{ asset($store->image) }}" alt="Store Logo" class="rounded-circle store-logo-placeholder">
                        @else
                            <div class="avatar-name rounded-circle"><span class="fs-4">{{ substr($store->name, 0, 1) }}</span></div>
                        @endif
                    </div>

                    {{-- Text Details --}}
                    <div>
                        <h2 class="mb-1 text-body-emphasis">{{ $store->name }}</h2>
                        <p class="text-body-secondary mb-1 fs-9">
                            <span class="fas fa-map-marker-alt me-1"></span>{{ $store->location ?? 'Online Global Seller' }}
                        </p>
                        <p class="text-body-tertiary mb-2 fs-9">{{ $store->description ?? 'No detailed description available.' }}</p>
                        
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

                {{-- Action Button (Add Product) --}}
                @if(session('auth_user_id') && session('auth_user_id') == $store->auth_user_id)
                    <div class="mt-3 mt-md-0">
                        <a href="{{ route('store.products', ['store' => $store->id]) }}" class="btn btn-primary">
                            <span class="fas fa-plus me-1"></span> Add Product
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. Map Section --}}
        @if($store->latitude && $store->longitude)
            <h3 class="mb-3 text-body-emphasis">Our Location</h3>
            <div id="storeLocationMap" class="mb-5 shadow-sm"></div>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-o9N1jV+0v+0QvC8FzCkUq/0bDjhS+PSHkP6u5A9gCtg=" crossorigin=""></script>
<script src="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone-min.js"></script>

@if($store->latitude && $store->longitude)
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Wrap map initialization in a timeout to allow CSS/DOM rendering to complete
    setTimeout(() => {
        const mapContainer = document.getElementById('storeLocationMap');
        
        if (mapContainer) {
            const latitude = {{ $store->latitude }};
            const longitude = {{ $store->longitude }};
            
            // 1. Initialize Map
            const map = L.map('storeLocationMap').setView([latitude, longitude], 16);
            
            // 2. Add Tile Layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
            
            // 3. Add Marker
            L.marker([latitude, longitude]).addTo(map)
                .bindPopup("<b>{{ $store->name }}</b><br>{{ $store->location ?? 'No location' }}")
                .openPopup();
                
            // 4. CRUCIAL: Force map to redraw based on container's actual size
            map.invalidateSize(); 
        }
    }, 100); // 100ms delay is usually enough
});
</script>
@endif

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