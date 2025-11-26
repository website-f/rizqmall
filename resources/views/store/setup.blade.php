@extends('partials.admin')

@section('title', 'Setup Your Store')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/dropzone@6.0.0-beta.2/dist/dropzone.css" />

    <style>
        .setup-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-bottom: 2px solid #e5e7eb;
            padding: 20px 24px;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 12px 16px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .dropzone {
            border: 3px dashed #d1d5db;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .dropzone:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .dropzone .dz-message {
            margin: 0;
        }

        #map {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 3px;
            background: #e5e7eb;
            z-index: -1;
        }

        .progress-step:last-child::before {
            display: none;
        }

        .progress-step.active .step-circle {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .progress-step.completed .step-circle {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
        }

        .autocomplete-results {
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>

    <div class="setup-container">
        <nav class="mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('store.select-category') }}">Select Category</a></li>
                <li class="breadcrumb-item active">Store Setup</li>
            </ol>
        </nav>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Category</div>
            </div>
            <div class="progress-step active">
                <div class="step-circle">2</div>
                <div class="step-label">Store Details</div>
            </div>
            <div class="progress-step">
                <div class="step-circle">3</div>
                <div class="step-label">Add Items</div>
            </div>
        </div>

        <div class="text-center mb-4">
            <span class="category-badge">
                <i class="{{ $category->icon }}"></i>
                {{ $category->name }}
            </span>
            <h2 class="mb-2 fw-bold">Setup Your {{ $category->name }} Store</h2>
            <p class="text-muted">Fill in your store details to get started</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger border-0 mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('store.store') }}" method="POST">
            @csrf

            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Store Name <span class="text-danger">*</span></label>
                        <input class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                            placeholder="E.g., Fresh Market Store" value="{{ old('name') }}" required />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input class="form-control @error('phone') is-invalid @enderror" type="text" name="phone"
                                placeholder="+60 12-345 6789" value="{{ old('phone') }}" required />
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                                placeholder="store@example.com" value="{{ old('email', $prefill['email'] ?? '') }}"
                                required />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Store Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4"
                            placeholder="Tell customers about your store...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Store Images -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-images"></i> Store Images</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Store Logo</label>
                        <div id="logo-dropzone" class="dropzone">
                            <div class="dz-message">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <p class="mb-0">Drop your logo here or click to upload</p>
                                <small class="text-muted">Recommended: 500x500px, Max 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Store Banner</label>
                        <div id="banner-dropzone" class="dropzone">
                            <div class="dz-message">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <p class="mb-0">Drop your banner here or click to upload</p>
                                <small class="text-muted">Recommended: 1920x400px, Max 3MB</small>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="image" id="logo_path">
                    <input type="hidden" name="banner" id="banner_path">
                </div>
            </div>

            <!-- Business Verification -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-certificate"></i> Business Verification</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Business Registration Number</label>
                        <input class="form-control @error('business_registration_number') is-invalid @enderror"
                            type="text" name="business_registration_number"
                            placeholder="E.g., 202301234567 (12 digits)"
                            value="{{ old('business_registration_number') }}" />
                        <small class="text-muted">Enter your SSM business registration number</small>
                        @error('business_registration_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">SSM Certificate <span class="text-muted">(Optional)</span></label>
                        <div id="ssm-dropzone" class="dropzone">
                            <div class="dz-message">
                                <i class="fas fa-file-pdf fa-3x text-primary mb-3"></i>
                                <p class="mb-0">Drop your SSM certificate here or click to upload</p>
                                <small class="text-muted">PDF, JPG, or PNG - Max 5MB</small>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">IC/MyKad Document <span class="text-muted">(Optional)</span></label>
                        <div id="ic-dropzone" class="dropzone">
                            <div class="dz-message">
                                <i class="fas fa-id-card fa-3x text-primary mb-3"></i>
                                <p class="mb-0">Drop your IC/MyKad here or click to upload</p>
                                <small class="text-muted">JPG or PNG - Max 5MB</small>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="ssm_document" id="ssm_path">
                    <input type="hidden" name="ic_document" id="ic_path">
                </div>
            </div>

            <!-- Location -->
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-map-marker-alt"></i> Store Location</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label">Search Location</label>
                        <input class="form-control" id="locationText" type="text" name="location"
                            placeholder="Start typing to search..." value="{{ old('location') }}" autocomplete="off" />
                        <ul id="autocomplete-results" class="list-group position-absolute w-100 autocomplete-results"
                            style="z-index: 1000; display: none;"></ul>
                    </div>

                    <div id="map"></div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input class="form-control @error('latitude') is-invalid @enderror" id="latitude"
                                type="text" name="latitude" value="{{ old('latitude') }}" readonly required />
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input class="form-control @error('longitude') is-invalid @enderror" id="longitude"
                                type="text" name="longitude" value="{{ old('longitude') }}" readonly required />
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary btn-lg px-5" type="submit">
                    <i class="fas fa-arrow-right me-2"></i> Continue to Add Items
                </button>
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

            const map = L.map('map').setView([3.1390, 101.6869], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            let marker = null;

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
                    const res = await fetch(url);
                    const data = await res.json();

                    resultsList.innerHTML = '';

                    if (data.length > 0) {
                        resultsList.style.display = 'block';
                        data.forEach(place => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action';
                            li.textContent = place.display_name;
                            li.style.cursor = 'pointer';
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

            // Restore marker if values exist
            if (latInput.value && lngInput.value) {
                setMarker(parseFloat(latInput.value), parseFloat(lngInput.value), addressInput.value);
            }

            // Hide autocomplete when clicking outside
            document.addEventListener('click', (e) => {
                if (!addressInput.contains(e.target) && !resultsList.contains(e.target)) {
                    resultsList.style.display = 'none';
                }
            });
        });
    </script>
@endpush
