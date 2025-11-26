@extends('partials.admin')

@section('title', 'Edit Store Settings')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone.css" />

    <style>
        #map {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .dropzone {
            border: 3px dashed #d1d5db;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .autocomplete-results {
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: none;
            position: absolute;
            width: 100%;
            z-index: 1000;
            background: white;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .autocomplete-results li {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .autocomplete-results li:hover {
            background-color: #f8f9fa;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Store Settings</h2>
            <a href="{{ route('store.profile', $store->slug) }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>View Store
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('vendor.store.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Info -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Store Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $store->name) }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $store->phone) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $store->email) }}" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4">{{ old('description', $store->description) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Business Registration No.</label>
                                    <input type="text" name="business_registration_no" class="form-control"
                                        value="{{ old('business_registration_no', $store->business_registration_no) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tax ID</label>
                                    <input type="text" name="tax_id" class="form-control"
                                        value="{{ old('tax_id', $store->tax_id) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Verification -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Business Verification</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Business Registration Number</label>
                                <input type="text" name="business_registration_number" class="form-control"
                                    value="{{ old('business_registration_number', $store->business_registration_number) }}"
                                    placeholder="E.g., 202301234567 (12 digits)">
                                <small class="text-muted">Enter your SSM business registration number</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">SSM Certificate <span class="text-muted">(Optional)</span></label>
                                <div id="ssm-dropzone" class="dropzone">
                                    <div class="dz-message">
                                        <p class="mb-0">Drop SSM certificate here</p>
                                    </div>
                                </div>
                                <input type="hidden" name="ssm_document" id="ssm_path">
                                @if ($store->ssm_document)
                                    <div class="mt-2">
                                        <p class="text-muted small mb-1">Current Document:</p>
                                        <a href="{{ Storage::url($store->ssm_document) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf me-1"></i>View SSM Document
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">IC/MyKad Document <span
                                        class="text-muted">(Optional)</span></label>
                                <div id="ic-dropzone" class="dropzone">
                                    <div class="dz-message">
                                        <p class="mb-0">Drop IC/MyKad here</p>
                                    </div>
                                </div>
                                <input type="hidden" name="ic_document" id="ic_path">
                                @if ($store->ic_document)
                                    <div class="mt-2 text-center">
                                        <p class="text-muted small mb-1">Current IC:</p>
                                        <img src="{{ Storage::url($store->ic_document) }}" class="rounded"
                                            style="max-width: 200px; height: auto;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Location</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 position-relative">
                                <label class="form-label">Search Location</label>
                                <input type="text" id="locationText" name="location" class="form-control"
                                    value="{{ old('location', $store->location) }}" placeholder="Search address..."
                                    autocomplete="off">
                                <ul id="autocomplete-results" class="autocomplete-results"></ul>
                            </div>
                            <div id="map" class="mb-3"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" class="form-control"
                                        value="{{ old('latitude', $store->latitude) }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" class="form-control"
                                        value="{{ old('longitude', $store->longitude) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Images -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">Store Branding</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">Logo</label>
                                <div id="logo-dropzone" class="dropzone">
                                    <div class="dz-message">
                                        <p class="mb-0">Drop logo here</p>
                                    </div>
                                </div>
                                <input type="hidden" name="image" id="logo_path">
                                @if ($store->image)
                                    <div class="mt-2 text-center">
                                        <p class="text-muted small mb-1">Current Logo:</p>
                                        <img src="{{ Storage::url($store->image) }}" class="rounded"
                                            style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Banner</label>
                                <div id="banner-dropzone" class="dropzone">
                                    <div class="dz-message">
                                        <p class="mb-0">Drop banner here</p>
                                    </div>
                                </div>
                                <input type="hidden" name="banner" id="banner_path">
                                @if ($store->banner)
                                    <div class="mt-2 text-center">
                                        <p class="text-muted small mb-1">Current Banner:</p>
                                        <img src="{{ Storage::url($store->banner) }}" class="rounded w-100"
                                            style="height: 100px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone-min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        function initDropzone(elementId, hiddenInputId, uploadUrl, acceptedFiles = 'image/*') {
            if (!document.getElementById(elementId)) return;

            return new Dropzone(`#${elementId}`, {
                url: uploadUrl,
                paramName: 'file',
                maxFiles: 1,
                acceptedFiles: acceptedFiles,
                addRemoveLinks: true,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                init: function() {
                    this.on("success", function(file, response) {
                        document.getElementById(hiddenInputId).value = response.path;
                    });
                    this.on("removedfile", function() {
                        document.getElementById(hiddenInputId).value = '';
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Dropzones
            initDropzone('logo-dropzone', 'logo_path', '{{ route('uploads.temp') }}');
            initDropzone('banner-dropzone', 'banner_path', '{{ route('uploads.temp') }}');
            initDropzone('ssm-dropzone', 'ssm_path', '{{ route('uploads.temp') }}', '.pdf,.jpg,.jpeg,.png');
            initDropzone('ic-dropzone', 'ic_path', '{{ route('uploads.temp') }}', '.jpg,.jpeg,.png');

            // Initialize Map
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const addressInput = document.getElementById('locationText');
            const resultsList = document.getElementById('autocomplete-results');

            // Default to KL or store location
            const defaultLat = {{ $store->latitude ?? 3.139 }};
            const defaultLng = {{ $store->longitude ?? 101.6869 }};

            const map = L.map('map').setView([defaultLat, defaultLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLng]).addTo(map);

            function setMarker(lat, lng, address = '') {
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map);
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                if (address) addressInput.value = address;
                map.setView([lat, lng], 16);
            }

            // Autocomplete search
            let debounceTimeout;
            addressInput.addEventListener('input', function() {
                clearTimeout(debounceTimeout);
                const query = this.value.trim();

                if (query.length < 3) {
                    resultsList.style.display = 'none';
                    return;
                }

                debounceTimeout = setTimeout(async () => {
                    const url =
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`;
                    try {
                        const res = await fetch(url);
                        const data = await res.json();

                        resultsList.innerHTML = '';

                        if (data.length > 0) {
                            resultsList.style.display = 'block';
                            data.forEach(place => {
                                const li = document.createElement('li');
                                li.textContent = place.display_name;
                                li.addEventListener('click', () => {
                                    setMarker(parseFloat(place.lat), parseFloat(
                                        place.lon), place.display_name);
                                    resultsList.style.display = 'none';
                                });
                                resultsList.appendChild(li);
                            });
                        } else {
                            resultsList.style.display = 'none';
                        }
                    } catch (e) {
                        console.error('Search error:', e);
                    }
                }, 300);
            });

            // Click map
            map.on('click', async (e) => {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                const url =
                    `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    setMarker(lat, lng, data.display_name || '');
                } catch (err) {
                    setMarker(lat, lng);
                }
            });

            // Hide autocomplete when clicking outside
            document.addEventListener('click', (e) => {
                if (!addressInput.contains(e.target) && !resultsList.contains(e.target)) {
                    resultsList.style.display = 'none';
                }
            });
        });
    </script>
@endpush
