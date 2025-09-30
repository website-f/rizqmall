@extends('partials.app')

@section('title', 'Rizqmall Stores')
    
@section('content')
<section class="pt-5 pb-9">

    <div class="container-small">
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Stores</li>
            </ol>
        </nav>
        <h2 class="mb-1">All Stores on Rizqmall</h2>
        <p class="mb-5 text-body-tertiary fw-semibold">Browse the best sellers and local businesses.</p>

        <div id="storeMap" style="height: 400px; border-radius: 10px; margin-bottom: 2rem;"></div>

        
        <div class="row gx-3 gy-5">
            {{-- Dynamically loop through the stores collection --}}
            @forelse($stores as $store)
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 hover-actions-trigger btn-reveal-trigger">
                
                {{-- Store Logo Placeholder: Uses the first letter of the store name --}}
                <div class="border border-translucent d-flex flex-center rounded-3 mb-3 p-4 bg-body-tertiary" style="height:180px;">
                    <span class="fs-1 fw-bold text-primary">{{ substr($store->name, 0, 1) }}</span>
                </div>
                
                {{-- Store Details --}}
                <h5 class="mb-2">{{ $store->name }}</h5>
                
                {{-- Static placeholder for rating (for visual consistency, awaiting review logic) --}}
                <div class="mb-1 fs-9">
                    <span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa fa-star text-warning"></span><span class="fa-regular fa-star text-warning-light"></span><span class="fa-regular fa-star text-warning-light"></span>
                </div>
                <p class="text-body-quaternary fs-9 mb-2 fw-semibold">{{ $store->location ?? 'Global Seller' }}</p>
                
                {{-- Link to visit the store profile page (using route model binding with slug) --}}
                <a class="btn btn-link p-0" href="{{ route('store.profile', $store->slug) }}">
                    Visit Store<span class="fas fa-chevron-right ms-1 fs-10"></span>
                </a>

                {{-- Dropdown menu for actions --}}
                <div class="hover-actions top-0 end-0 mt-2 me-3">
                    <div class="btn-reveal-trigger">
                        <button class="btn btn-sm dropdown-toggle dropdown-caret-none transition-none btn-reveal lh-1 bg-body-highlight rounded-1" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent"><span class="fas fa-ellipsis-h fs-9"></span></button>
                        <div class="dropdown-menu dropdown-menu-end py-2">
                            <a class="dropdown-item" href="{{ route('store.profile', $store->slug) }}">View Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#!">Report Store</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h4 class="text-body-secondary">No stores found.</h4>
                    <p class="text-body-tertiary">Be the first seller to set up shop!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Store Detail Modal -->
<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="storeModalLabel">Store Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4 id="modalStoreName"></h4>
        <p id="modalStoreLocation" class="mb-1 text-muted"></p>
        <p id="modalStoreDescription"></p>
        <a id="modalVisitStoreBtn" href="#" class="btn btn-primary w-100">Visit Store</a>
      </div>
    </div>
  </div>
</div>

</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 📌 Initialize map
    const map = L.map('storeMap').setView([3.139, 101.6869], 11); // Default to KL

    // 🗺️ Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    }).addTo(map);

    // 📍 Try to locate user and center map
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 13);

            L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: "#007bff",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map).bindPopup("📍 You are here");
        });
    }

    // 🏪 Store markers data
    const stores = @json($stores);

    stores.forEach(store => {
        if (!store.latitude || !store.longitude) return; // Skip if no coordinates

        const marker = L.marker([store.latitude, store.longitude]).addTo(map);

        // Marker popup (hover)
        marker.bindPopup(`<b>${store.name}</b><br>${store.location ?? 'No location'}`);

        // Marker click → show modal
        marker.on('click', () => {
            document.getElementById('modalStoreName').textContent = store.name;
            document.getElementById('modalStoreLocation').textContent = store.location ?? 'No location info';
            document.getElementById('modalStoreDescription').textContent = store.description ?? 'No description';

            const visitBtn = document.getElementById('modalVisitStoreBtn');
            visitBtn.href = `#`; // Adjust your route to store profile

            const modal = new bootstrap.Modal(document.getElementById('storeModal'));
            modal.show();
        });
    });
});
</script>
@endsection
