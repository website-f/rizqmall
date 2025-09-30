@extends('partials.admin')

@section('title', 'Setup Your Store')
    
@section('content')
    <link rel="stylesheet" href="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone.css" />
    <style>
        .dropzone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f9f9f9;
        }
    </style>
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

            {{-- Logo Upload --}}
            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-4">Store Logo 🖼️</h4>
                    <div id="logo-dropzone" class="dropzone"></div>
                    @error('image')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                </div>
            </div>
            
            {{-- Banner Upload --}}
            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-4">Store Banner 🏞️</h4>
                    <div id="banner-dropzone" class="dropzone"></div>
                    @error('banner')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                </div>
            </div>
            
            {{-- Hidden fields to store final uploaded file paths --}}
            <input type="hidden" name="image" id="logo_path">
            <input type="hidden" name="banner" id="banner_path">

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
                    <div class="mb-4 position-relative">
                        <label class="form-label" for="locationText">Physical Location/Address</label>
                        <input class="form-control" id="locationText" type="text" name="location"
                               placeholder="E.g., 123 Main St, Kuala Lumpur" value="{{ old('location') }}" autocomplete="off" />
                        <ul id="autocomplete-results" class="list-group position-absolute w-100" style="z-index: 1000;"></ul>
                        <div class="form-text">Start typing to search for your store location.</div>
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
<script src="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone-min.js"></script>
<script>
Dropzone.autoDiscover = false;

function initDropzone(elementId, hiddenInputId, uploadUrl) {
    const dz = new Dropzone(`#${elementId}`, {
        url: uploadUrl,
        paramName: 'file',
        maxFiles: 1,
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        init: function () {
            this.on("success", function (file, response) {
                document.getElementById(hiddenInputId).value = response.path;
            });
            this.on("removedfile", function () {
                document.getElementById(hiddenInputId).value = '';
            });
        }
    });
    return dz;
}


document.addEventListener('DOMContentLoaded', () => {
    initDropzone('logo-dropzone', 'logo_path', '{{ route('uploads.temp') }}');
    initDropzone('banner-dropzone', 'banner_path', '{{ route('uploads.temp') }}');
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('locationText');
    const resultsList = document.getElementById('autocomplete-results');

    const map = L.map('map').setView([3.1390, 101.6869], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    let marker = null;
    function setMarker(lat, lng, address = '') {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        if (address) addressInput.value = address;
    }

    // Autocomplete search
    let debounceTimeout;
    addressInput.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        const query = this.value.trim();
        if (query.length < 3) {
            resultsList.innerHTML = '';
            return;
        }
        debounceTimeout = setTimeout(async () => {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`;
            const res = await fetch(url);
            const data = await res.json();
            resultsList.innerHTML = '';
            data.forEach(place => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.textContent = place.display_name;
                li.addEventListener('click', () => {
                    setMarker(parseFloat(place.lat), parseFloat(place.lon), place.display_name);
                    map.setView([place.lat, place.lon], 16);
                    resultsList.innerHTML = '';
                });
                resultsList.appendChild(li);
            });
        }, 300);
    });

    // Click map manually
    map.on('click', async (e) => {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;
        try {
            const res = await fetch(url);
            const data = await res.json();
            setMarker(lat, lng, data.display_name || '');
        } catch (err) {
            console.error('Reverse geocode failed', err);
            setMarker(lat, lng);
        }
    });

    // Restore marker if values exist
    if (latInput.value && lngInput.value) {
        setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), addressInput.value);
        map.setView([parseFloat(latInput.value), parseFloat(lngInput.value)], 16);
    }
});
</script>
@endpush

