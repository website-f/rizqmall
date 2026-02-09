@extends('partials.admin')

@section('title', 'Bulk Product Import')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-1">Bulk Product Import</h3>
                        <p class="text-muted mb-0">Upload Excel/CSV and add products to one or multiple stores.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if (session('import_errors'))
                    <div class="alert alert-warning">
                        <strong>Some rows were skipped:</strong>
                        <ul class="mb-0">
                            @foreach (session('import_errors') as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Upload File</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Select Store(s)</label>
                                <select class="form-select" name="stores[]" multiple required>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold CTRL/CMD to select multiple stores. Each row will be added to all selected stores.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Excel/CSV File</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.txt" required>
                                <small class="text-muted">Supports .xlsx and .csv</small>
                            </div>

                            <div class="alert alert-info">
                                <strong>Required Columns:</strong> Name, Price (or Sale Price)
                                <br>
                                <strong>Optional:</strong> Description, Short Description, SKU, Stock Quantity, Category, Type, Status, Bulk/Preorder/Booking fields.
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Import Products
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Auto Column Detection</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">We try to auto-detect column names. These header names are supported:</p>
                        <div class="row">
                            <div class="col-md-4">
                                <ul class="mb-0">
                                    <li>Name / Product Name / Item</li>
                                    <li>Description / Desc / Details</li>
                                    <li>Short Description / Summary</li>
                                    <li>Price / Regular Price</li>
                                    <li>Sale Price / Discount Price</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="mb-0">
                                    <li>SKU / Code</li>
                                    <li>Stock / Quantity / Qty</li>
                                    <li>Category / Category Name</li>
                                    <li>Type (product, service, pharmacy)</li>
                                    <li>Status (published, draft, archived)</li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <ul class="mb-0">
                                    <li>Bulk Price / Min Qty / Bulk Threshold</li>
                                    <li>Preorder / Release Date / Lead Time</li>
                                    <li>Booking Fee / Package Price</li>
                                    <li>Service Duration / Availability</li>
                                    <li>Weight / Length / Width / Height</li>
                                </ul>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            If your file uses different headers, rename them to match one of these.
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
