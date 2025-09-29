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

                    {{-- Store Location (Text) --}}
                    <div class="mb-4">
                        <label class="form-label" for="locationText">Physical Location/Address</label>
                        <input class="form-control" id="locationText" type="text" name="location" placeholder="E.g., 123 Main St, Kuala Lumpur" value="{{ old('location') }}" />
                        <div class="form-text">This address will be displayed to customers for local pickups.</div>
                        @error('location')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Location Coordinates Card --}}
            <div class="card mb-5">
                <div class="card-body">
                    <h4 class="card-title mb-4">Store Coordinate 📍</h4>
                    <p class="text-body-tertiary">We need your store's precise location for delivery and mapping. Click the button to detect your current coordinates.</p>
                    
                    <div class="mb-3">
                        <button class="btn btn-phoenix-info mb-2" type="button" id="detectLocationBtn">
                            <span class="fas fa-crosshairs me-2"></span> Detect Current Location
                        </button>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const detectLocationBtn = document.getElementById('detectLocationBtn');
        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');

        detectLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                detectLocationBtn.textContent = 'Detecting...';
                detectLocationBtn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        latitudeInput.value = lat.toFixed(6); // To keep it clean
                        longitudeInput.value = lng.toFixed(6); // To keep it clean

                        detectLocationBtn.textContent = 'Location Detected! (Click to re-detect)';
                        detectLocationBtn.disabled = false;
                        
                        // NOTE: You would show the Leaflet map here later, using lat and lng.
                        // For example: initializeMap(lat, lng);
                    },
                    (error) => {
                        console.error("Geolocation error:", error);
                        alert("Error getting location: " + error.message + ". Please enter the location manually.");
                        
                        // Reset button on error
                        detectLocationBtn.textContent = 'Detection Failed. Try Again.';
                        detectLocationBtn.disabled = false;
                    }, 
                    {
                        enableHighAccuracy: true, 
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });
    });
</script>
@endpush