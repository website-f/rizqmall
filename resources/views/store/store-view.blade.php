@extends('partials.app')

@section('title', $store->name . ' Profile')
    
@section('content')
<section class="pt-5 pb-9">
    <div class="container-small">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmal.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('stores') }}">All Stores</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $store->name }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            {{-- Store Header & Main Content (Left Column) --}}
            <div class="col-12 col-xl-8">
                <div class="d-flex align-items-center mb-5">
                    
                    {{-- Logo/Initial Placeholder --}}
                    <div class="border border-translucent d-flex flex-center rounded-circle p-4 me-4 bg-body-tertiary" style="height:120px; width:120px;">
                        <span class="fs-1 fw-bold text-primary">{{ substr($store->name, 0, 1) }}</span>
                    </div>

                    <div>
                        <h1 class="mb-1">{{ $store->name }}</h1>
                        <p class="text-body-secondary fs-9 mb-2 fw-semibold">{{ $store->location ?? 'Global Seller' }}</p>
                        
                        {{-- Static placeholder for rating --}}
                        <div class="mb-1 fs-9">
                            <span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa-regular fa-star text-warning-light"></span><span class="fa-regular fa-star text-warning-light"></span>
                            <span class="ms-2 text-body-tertiary">(0 ratings)</span>
                        </div>
                    </div>
                </div>

                {{-- Store Description and About --}}
                <div class="mb-5">
                    <h4 class="mb-3">About the Store</h4>
                    <p class="text-body-emphasis">{{ $store->description ?? 'This seller has not provided a detailed description yet.' }}</p>
                </div>
                
                {{-- Store Products Listing --}}
                <h4 class="mb-4">Products by {{ $store->name }}</h4>
                <div class="row g-3">
                    {{-- Note: We only display products if the relation is successfully loaded and they exist. --}}
                    @forelse($store->products as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body p-3">
                                    <h5 class="card-title fs-8 line-clamp-2">{{ $product->name }}</h5>
                                    <p class="text-primary fw-bold mb-0">${{ number_format($product->regular_price, 2) }}</p>
                                    {{-- Placeholder for product show route --}}
                                    <a href="#product-{{ $product->id }}" class="stretched-link"></a> 
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-body-tertiary">No products have been added to this store yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sidebar (Right Column) --}}
            <div class="col-12 col-xl-4">

                {{-- Add Products Button (Conditional Visibility for Owner) --}}
                @if($is_owner)
                <div class="card mb-4 bg-primary-subtle border-0">
                    <div class="card-body text-center">
                        <h5 class="text-primary">Manage Your Store</h5>
                        <a href="{{ route('seller.products.create') }}" class="btn btn-warning w-100 rounded-pill fs-8 fw-bold">
                            <span class="fas fa-plus me-2"></span> Add New Product
                        </a>
                    </div>
                </div>
                @endif

                {{-- Store Location Map --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Store Location</h5>
                        <p class="text-body-secondary fs-9 mb-3">Approximate location for local services.</p>

                        {{-- Map Container --}}
                        <div id="storeMap" style="height: 300px; border-radius: 0.5rem; border: 1px solid #e3e6ed;"></div>
                        
                        <p class="mt-3 mb-0 fs-9 text-body-emphasis fw-semibold">
                            Coordinates: {{ $store->latitude }}, {{ $store->longitude }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{-- Include Leaflet CSS and JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfBIpBUy9S+Pwa16HN8z8AG6aM+nFCz8=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20n69zXpBO6TPW1QxVbY0hDM0WzE4F8C1T4G2A1E4Q6g=" crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = {{ $store->latitude ?? 0 }};
        const lng = {{ $store->longitude ?? 0 }};
        const storeName = '{{ $store->name }}';

        if (lat !== 0 && lng !== 0) {
            // Initialize the map
            // Use 13 for a decent zoom level (city view)
            const map = L.map('storeMap').setView([lat, lng], 13);

            // Add the OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a marker for the store location
            L.marker([lat, lng])
                .addTo(map)
                .bindPopup('<b>' + storeName + '</b><br>Store Location.')
                .openPopup();
        } else {
            // Display a message if coordinates are missing
            document.getElementById('storeMap').innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-body-tertiary">Location coordinates are unavailable.</div>';
        }
    });
</script>
@endpush
