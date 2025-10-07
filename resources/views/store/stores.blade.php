@extends('partials.app')

@section('title', 'Rizqmall Stores')
    
@section('content')
<style>
    /* Modern Stores Page Styling */
    .stores-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        border-radius: 24px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }
    
    .stores-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.3;
    }
    
    .stores-hero-content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
    }
    
    .stores-hero h1 {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 16px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stores-hero p {
        font-size: 20px;
        opacity: 0.95;
        margin-bottom: 0;
    }
    
    /* Map Section */
    .map-container {
        background: white;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 40px;
    }
    
    .map-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .map-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }
    
    .map-toggle {
        display: flex;
        gap: 8px;
    }
    
    .map-view-btn {
        padding: 8px 16px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
    }
    .map-view-btn:hover { border-color: #3b82f6; color: #3b82f6; }
    .map-view-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #storeMap {
        height: 450px;
        border-radius: 16px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }
    
    /* Store Cards */
    .stores-grid-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .stores-count {
        font-size: 18px;
        font-weight: 600;
        color: #374151;
    }
    
    .view-mode-toggle {
        display: flex;
        gap: 8px;
    }
    
    .view-mode-btn {
        width: 40px;
        height: 40px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
    }
    .view-mode-btn:hover { border-color: #3b82f6; color: #3b82f6; }
    .view-mode-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .store-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 2px solid transparent;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        position: relative;
    }
    .store-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.15);
        border-color: #e0e7ff;
    }
    
    .store-logo-wrapper {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        padding: 40px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 200px;
        position: relative;
    }
    
    .store-logo-img {
        max-width: 120px;
        max-height: 120px;
        object-fit: contain;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    
    .store-logo-placeholder {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: 800;
        color: white;
        box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
    }
    
    .store-verified-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .store-info {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .store-category {
        font-size: 12px;
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .store-name {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
        line-height: 1.3;
    }
    
    .store-location {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .store-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .store-rating .stars { color: #fbbf24; }
    .store-rating .count { color: #9ca3af; font-weight: 600; }
    
    .store-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }
    
    .store-stat {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .store-stat-value {
        font-size: 18px;
        font-weight: 800;
        color: #3b82f6;
    }
    
    .store-stat-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 600;
    }
    
    .store-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }
    
    .btn-visit-store {
        flex: 1;
        padding: 12px 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }
    .btn-visit-store:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        color: white;
    }
    
    .btn-store-menu {
        width: 44px;
        height: 44px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
    }
    .btn-store-menu:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    /* List View */
    .store-card.list-view {
        flex-direction: row;
    }
    
    .store-card.list-view .store-logo-wrapper {
        min-width: 200px;
        min-height: auto;
    }
    
    .store-card.list-view .store-info {
        flex-direction: row;
        align-items: center;
        gap: 24px;
    }
    
    .store-card.list-view .store-stats {
        border-top: none;
        border-left: 1px solid #e5e7eb;
        padding-left: 24px;
        margin-bottom: 0;
    }
    
    /* Modal Styling */
    .store-modal .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
    }
    
    .store-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 24px;
    }
    
    .store-modal-banner {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 20px;
    }
    
    .store-modal-logo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        margin-bottom: 16px;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }
    
    .empty-state-icon {
        font-size: 80px;
        color: #d1d5db;
        margin-bottom: 24px;
    }
    
    .empty-state h4 {
        font-size: 24px;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 12px;
    }
    
    .empty-state p {
        color: #9ca3af;
        font-size: 16px;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .stores-hero h1 { font-size: 36px; }
        .stores-hero p { font-size: 16px; }
        
        .map-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        
        .store-card.list-view {
            flex-direction: column;
        }
        
        .store-card.list-view .store-info {
            flex-direction: column;
            align-items: stretch;
        }
        
        .store-card.list-view .store-stats {
            border-left: none;
            border-top: 1px solid #e5e7eb;
            padding-left: 0;
            padding-top: 16px;
        }
    }
    
    @media (max-width: 575px) {
        .stores-hero {
            padding: 40px 0;
            border-radius: 16px;
        }
        
        .stores-hero h1 { font-size: 28px; }
        
        #storeMap { height: 300px; }
        
        .map-toggle, .view-mode-toggle {
            width: 100%;
        }
        
        .map-view-btn, .view-mode-btn {
            flex: 1;
        }
    }
</style>

<section class="py-5">
    <div class="container-small">
        {{-- Hero Section --}}
        <div class="stores-hero">
            <div class="stores-hero-content">
                <h1>🏪 Discover Amazing Stores</h1>
                <p>Browse through our collection of verified sellers and local businesses</p>
            </div>
        </div>
        
        {{-- Breadcrumb --}}
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('rizqmall.home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">All Stores</li>
            </ol>
        </nav>

        {{-- Interactive Map --}}
        <div class="map-container">
            <div class="map-header">
                <h2 class="map-title">
                    <i class="fas fa-map-marked-alt text-primary"></i>
                    Store Locations
                </h2>
                <div class="map-toggle">
                    <button class="map-view-btn active" onclick="toggleMap(true)">
                        <i class="fas fa-map me-1"></i> Show Map
                    </button>
                    <button class="map-view-btn" onclick="toggleMap(false)">
                        <i class="fas fa-list me-1"></i> Hide Map
                    </button>
                </div>
            </div>
            <div id="storeMap"></div>
        </div>

        {{-- Stores Grid Header --}}
        <div class="stores-grid-header">
            <h3 class="stores-count">
                <i class="fas fa-store me-2 text-primary"></i>
                {{ $stores->total() }} Stores Found
            </h3>
            <div class="view-mode-toggle">
                <button class="view-mode-btn active" data-view="grid" onclick="changeView('grid')">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-mode-btn" data-view="list" onclick="changeView('list')">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        {{-- Stores Grid --}}
        <div class="row g-4" id="storesGrid">
            @forelse($stores as $store)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 store-grid-item">
                    <div class="store-card">
                        {{-- Store Logo --}}
                        <div class="store-logo-wrapper">
                            @if($store->image)
                                <img src="{{ asset('storage/' . $store->image) }}" 
                                     alt="{{ $store->name }}" 
                                     class="store-logo-img">
                            @else
                                <div class="store-logo-placeholder">
                                    {{ substr($store->name, 0, 1) }}
                                </div>
                            @endif
                            
                            @if($store->is_verified)
                                <div class="store-verified-badge">
                                    <i class="fas fa-check-circle"></i>
                                    Verified
                                </div>
                            @endif
                        </div>

                        {{-- Store Info --}}
                        <div class="store-info">
                            <div class="store-category">
                                {{ $store->category->name ?? 'General Store' }}
                            </div>
                            
                            <h5 class="store-name">{{ $store->name }}</h5>
                            
                            <div class="store-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $store->location ?? 'Location not specified' }}
                            </div>
                            
                            <div class="store-rating">
                                <div class="stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star"></i>
                                    @endfor
                                </div>
                                <span class="count">(4.8)</span>
                            </div>
                            
                            <div class="store-stats">
                                <div class="store-stat">
                                    <span class="store-stat-value">{{ $store->products_count ?? 0 }}</span>
                                    <span class="store-stat-label">Products</span>
                                </div>
                                <div class="store-stat">
                                    <span class="store-stat-value">1.2k</span>
                                    <span class="store-stat-label">Followers</span>
                                </div>
                            </div>
                            
                            <div class="store-actions">
                                <a href="{{ route('store.profile', $store->slug) }}" class="btn-visit-store">
                                    <i class="fas fa-store me-2"></i>Visit Store
                                </a>
                                <button class="btn-store-menu" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" 
                                           href="{{ route('store.profile', $store->slug) }}">
                                            <i class="fas fa-eye me-2"></i>View Profile
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#!">
                                            <i class="fas fa-flag me-2"></i>Report Store
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-store-slash"></i>
                        </div>
                        <h4>No stores found</h4>
                        <p>Be the first seller to set up shop on RizqMall!</p>
                        <a href="{{ route('store.select-category') }}" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-plus me-2"></i>Create Your Store
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($stores->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $stores->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</section>

{{-- Store Detail Modal --}}
<div class="modal fade store-modal" id="storeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-store me-2"></i>Store Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                {{-- Banner --}}
                <div id="modalStoreBanner" class="mb-3" style="display: none;">
                    <img src="" alt="Store Banner" class="store-modal-banner">
                </div>

                {{-- Logo --}}
                <div id="modalStoreLogo" class="mb-3" style="display: none;">
                    <img src="" alt="Store Logo" class="store-modal-logo">
                </div>

                <h3 id="modalStoreName" class="mb-2"></h3>
                <p id="modalStoreLocation" class="mb-3 text-muted">
                    <i class="fas fa-map-marker-alt me-1"></i>
                </p>
                <p id="modalStoreDescription" class="mb-4"></p>

                <a id="modalVisitStoreBtn" href="#" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-arrow-right me-2"></i>Visit Store
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
let map;
let markersLayer;

document.addEventListener('DOMContentLoaded', () => {
    initMap();
});

function initMap() {
    // Initialize map
    map = L.map('storeMap').setView([3.139, 101.6869], 11); // Default to KL

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(map);

    // Create markers layer group
    markersLayer = L.layerGroup().addTo(map);

    // Try to get user location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 13);

            L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: "#3b82f6",
                color: "#fff",
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map).bindPopup("<b>📍 You are here</b>");
        });
    }

    // Add store markers
    const stores = @json($stores->items());
    
    stores.forEach(store => {
        if (!store.latitude || !store.longitude) return;

        const marker = L.marker([store.latitude, store.longitude]).addTo(markersLayer);

        // Custom popup content
        const popupContent = `
            <div style="text-align: center; min-width: 200px;">
                <h6 style="margin-bottom: 8px; font-weight: 700;">${store.name}</h6>
                <p style="margin-bottom: 8px; color: #6b7280; font-size: 13px;">
                    <i class="fas fa-map-marker-alt"></i> ${store.location || 'No location'}
                </p>
                <a href="#" onclick="showStoreModal(${store.id}); return false;" 
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i>View Details
                </a>
            </div>
        `;
        
        marker.bindPopup(popupContent);

        // Store data in marker for modal
        marker.storeData = store;
        
        marker.on('click', () => {
            showStoreModal(store);
        });
    });
}

function toggleMap(show) {
    const mapElement = document.getElementById('storeMap');
    const buttons = document.querySelectorAll('.map-view-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.map-view-btn').classList.add('active');
    
    if (show) {
        mapElement.style.display = 'block';
        setTimeout(() => map.invalidateSize(), 100);
    } else {
        mapElement.style.display = 'none';
    }
}

function changeView(view) {
    const grid = document.getElementById('storesGrid');
    const items = document.querySelectorAll('.store-grid-item');
    const buttons = document.querySelectorAll('.view-mode-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('.view-mode-btn').classList.add('active');
    
    if (view === 'list') {
        items.forEach(item => {
            item.className = 'col-12 store-grid-item';
            item.querySelector('.store-card').classList.add('list-view');
        });
    } else {
        items.forEach(item => {
            item.className = 'col-12 col-sm-6 col-md-4 col-lg-3 store-grid-item';
            item.querySelector('.store-card').classList.remove('list-view');
        });
    }
}

function showStoreModal(store) {
    document.getElementById('modalStoreName').textContent = store.name;
    document.getElementById('modalStoreLocation').textContent = store.location || 'No location info';
    document.getElementById('modalStoreDescription').textContent = store.description || 'No description available';
    
    // Banner
    const bannerContainer = document.getElementById('modalStoreBanner');
    const bannerImg = bannerContainer.querySelector('img');
    if(store.banner) {
        bannerImg.src = '/storage/' + store.banner;
        bannerContainer.style.display = 'block';
    } else {
        bannerContainer.style.display = 'none';
    }

    // Logo
    const logoContainer = document.getElementById('modalStoreLogo');
    const logoImg = logoContainer.querySelector('img');
    if(store.image) {
        logoImg.src = '/storage/' + store.image;
        logoContainer.style.display = 'block';
    } else {
        logoContainer.style.display = 'none';
    }

    // Visit Store button
    document.getElementById('modalVisitStoreBtn').href = `/stores/${store.slug}`;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('storeModal'));
    modal.show();
}
</script>
@endpush