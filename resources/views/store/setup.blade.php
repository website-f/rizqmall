@extends('partials.admin')

@section('title', 'Setup Your Store')
    
@section('content')
    <div class="container-small">
        <nav class="mb-3 mt-6" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                <li class="breadcrumb-item active">Store Setup</li>
            </ol>
        </nav>
        
        <form class="mb-9" action="{{ route('store.store') }}" method="POST">
            @csrf
            <div class="row g-3 flex-between-end mb-5">
                <div class="col-auto">
                    <h2 class="mb-2">Setup Your Store 🏬</h2>
                    <h5 class="text-body-tertiary fw-semibold">This is the first step to selling on Rizqmall.</h5>
                </div>
                <div class="col-auto">
                    {{-- <button class="btn btn-primary mb-2 mb-sm-0" type="submit">Create Store & Continue</button> --}}
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-4">Store Details</h4>
                    
                    {{-- Store Name --}}
                    <div class="mb-4">
                        <label class="form-label" for="storeName">Store Name</label>
                        <input class="form-control" id="storeName" type="text" name="name" placeholder="E.g., Rizq Electronics" value="{{ old('name') }}" required />
                        @error('name')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                    </div>

                    {{-- Store Description --}}
                    <div class="mb-4">
                        <label class="form-label" for="description">Store Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Tell customers about your store (optional)">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>

            {{-- Location Coordinates Card --}}
            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-4">Store Coordinate 📍</h4>
                    <p class="text-body-tertiary">We need your store's precise location for delivery and mapping. Click the button to detect your current coordinates.</p>
                    
                   <div class="mb-4">
                       <label class="form-label">Search Location</label>
                       <div id="map"></div>
                       <div class="form-text mt-1">Search or click the map to set your store location.</div>
                   </div>


                   {{-- Store Location (Text) --}}
                    <div class="mb-4">
                        <label class="form-label" for="locationText">Physical Location/Address</label>
                        <input class="form-control" id="locationText" type="text" name="location" placeholder="E.g., 123 Main St, Kuala Lumpur" value="{{ old('location') }}" />
                        <div class="form-text">This address will be displayed to customers for local pickups.</div>
                        @error('location')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                    </div>

                    {{-- Hidden fields for latitude and longitude --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="latitude">Latitude (Lat)</label>
                            <input class="form-control" id="latitude" type="text" name="latitude" value="{{ old('latitude') }}" placeholder="Auto-filled by detection" readonly required />
                            @error('latitude')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="longitude">Longitude (Lng)</label>
                            <input class="form-control" id="longitude" type="text" name="longitude" value="{{ old('longitude') }}" placeholder="Auto-filled by detection" readonly required />
                            @error('longitude')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto">
                <button class="btn btn-primary mb-2 mb-sm-0" type="submit">Create Store & Continue</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-geosearch@3.7.0/dist/bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('locationText');

    // Default center (Malaysia)
    const map = L.map('map').setView([3.1390, 101.6869], 13);

    // Add OpenStreetMap layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Marker placeholder
    let marker = null;

    function setMarker(lat, lng, address = '') {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        if (address) addressInput.value = address;
    }

    // Click map to select location
    map.on('click', async (e) => {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        // Reverse geocode to get address name
        const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;
        try {
            const res = await fetch(url);
            const data = await res.json();
            const address = data.display_name || '';
            setMarker(lat, lng, address);
        } catch (err) {
            console.error('Reverse geocode failed', err);
            setMarker(lat, lng);
        }
    });

    // Geosearch (search bar)
    const provider = new window.GeoSearch.OpenStreetMapProvider();
    const search = new window.GeoSearch.GeoSearchControl({
        provider: provider,
        style: 'bar',
        showMarker: false,
        autoClose: true,
        retainZoomLevel: false,
        searchLabel: 'Search for location...',
        keepResult: true,
    });

    map.addControl(search);

    // Handle search result selection
    map.on('geosearch/showlocation', (result) => {
        const { x: lng, y: lat, label: address } = result.location;
        setMarker(lat, lng, address);
        map.setView([lat, lng], 16);
    });

    // Optional: If old values exist, restore marker
    if (latInput.value && lngInput.value) {
        setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), addressInput.value);
        map.setView([parseFloat(latInput.value), parseFloat(lngInput.value)], 16);
    }
});
</script>
@endpush

