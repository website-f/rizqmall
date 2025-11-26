@extends('partials.app')

@section('title', 'My Addresses - RizqMall')

@section('content')
    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar avatar-4xl mb-3">
                            <img class="rounded-circle" src="{{ auth()->user()->avatar ?? asset('defUse.jpg') }}"
                                alt="{{ auth()->user()->name }}">
                        </div>
                        <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                        <p class="text-muted small">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('customer.profile') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Profile Information
                        </a>
                        <a href="{{ route('customer.addresses') }}" class="list-group-item list-group-item-action active">
                            <i class="fas fa-map-marker-alt me-2"></i>Addresses
                        </a>
                        <a href="{{ route('customer.orders.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </a>
                        <a href="{{ route('customer.wishlist') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-heart me-2"></i>Wishlist
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">My Addresses</h2>
                        <p class="text-muted mb-0">Manage your delivery addresses</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                        <i class="fas fa-plus me-2"></i>Add New Address
                    </button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Addresses List -->
                <div class="row g-3">
                    @forelse($addresses ?? [] as $address)
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100 {{ $address->is_default ? 'border-primary' : '' }}">
                                <div class="card-body">
                                    @if ($address->is_default)
                                        <span class="badge bg-primary mb-2">Default Address</span>
                                    @endif

                                    <h5 class="card-title">{{ $address->label ?? 'Home' }}</h5>

                                    <p class="card-text mb-2">
                                        <strong>{{ $address->recipient_name }}</strong><br>
                                        {{ $address->phone }}<br>
                                        {{ $address->address_line1 }}<br>
                                        @if ($address->address_line2)
                                            {{ $address->address_line2 }}<br>
                                        @endif
                                        {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                        {{ $address->country ?? 'Malaysia' }}
                                    </p>

                                    <div class="btn-group w-100 mt-3">
                                        <button class="btn btn-outline-primary btn-sm"
                                            onclick="editAddress({{ $address->id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        @if (!$address->is_default)
                                            <form action="{{ route('customer.addresses.set-default', $address) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-star"></i> Set Default
                                                </button>
                                            </form>
                                        @endif
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="deleteAddress({{ $address->id }})"
                                            {{ $address->is_default ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No addresses yet</h5>
                                    <p class="text-muted mb-3">Add your first delivery address</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addAddressModal">
                                        <i class="fas fa-plus me-2"></i>Add Address
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('customer.addresses.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="label" class="form-label">Address Label</label>
                                <select class="form-select" id="label" name="label" required>
                                    <option value="Home">Home</option>
                                    <option value="Work">Work</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                                    value="{{ auth()->user()->name }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="{{ auth()->user()->phone }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address_line1" class="form-label">Address Line 1</label>
                                <input type="text" class="form-control" id="address_line1" name="address_line1"
                                    placeholder="Street address, P.O. box" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                                <input type="text" class="form-control" id="address_line2" name="address_line2"
                                    placeholder="Apartment, suite, unit, building, floor, etc.">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select State</option>
                                    <option value="Johor">Johor</option>
                                    <option value="Kedah">Kedah</option>
                                    <option value="Kelantan">Kelantan</option>
                                    <option value="Kuala Lumpur">Kuala Lumpur</option>
                                    <option value="Labuan">Labuan</option>
                                    <option value="Melaka">Melaka</option>
                                    <option value="Negeri Sembilan">Negeri Sembilan</option>
                                    <option value="Pahang">Pahang</option>
                                    <option value="Penang">Penang</option>
                                    <option value="Perak">Perak</option>
                                    <option value="Perlis">Perlis</option>
                                    <option value="Putrajaya">Putrajaya</option>
                                    <option value="Sabah">Sabah</option>
                                    <option value="Sarawak">Sarawak</option>
                                    <option value="Selangor">Selangor</option>
                                    <option value="Terengganu">Terengganu</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                    value="Malaysia" readonly>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default"
                                        value="1">
                                    <label class="form-check-label" for="is_default">
                                        Set as default address
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editAddressForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <!-- Same fields as add address -->
                        <div id="editAddressFields"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteAddressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this address?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteAddressForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function editAddress(id) {
            // TODO: Load address data and populate edit form
            const editModal = new bootstrap.Modal(document.getElementById('editAddressModal'));
            editModal.show();
        }

        function deleteAddress(id) {
            const deleteForm = document.getElementById('deleteAddressForm');
            deleteForm.action = `/customer/addresses/${id}`;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteAddressModal'));
            deleteModal.show();
        }
    </script>
@endpush
