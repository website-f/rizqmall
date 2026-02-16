@extends('partials.admin')

@section('title', 'Bulk Product Import')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--multiple {
            min-height: 42px;
            border-color: #cbd5e1;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #e7f1ff;
            border: 1px solid #b6d4fe;
            color: #0a58ca;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-1">Bulk Product Import</h3>
                        <p class="text-muted mb-0">Upload any Excel/CSV file - we'll auto-detect columns smartly.</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if (session('detected_columns'))
                    <div class="alert alert-info border-start border-4 border-primary">
                        <strong><i class="fas fa-magic me-2"></i>Auto-Detected Column Mapping:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach (session('detected_columns') as $col)
                                <li>{{ $col }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if (session('import_headers'))
                    <div class="alert alert-secondary">
                        <strong>Headers found in your file:</strong>
                        <div class="mt-2">
                            @foreach (session('import_headers') as $h)
                                @if(trim($h))
                                    <span class="badge bg-secondary me-1 mb-1">{{ $h }}</span>
                                @endif
                            @endforeach
                        </div>
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

                {{-- Import Products --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2 text-primary"></i>Import Products</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.products.import.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Store(s)</label>
                                <select class="form-select select2-stores" name="stores[]" multiple required>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Each row will be added to all selected stores.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Excel/CSV File</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.txt" required>
                                <small class="text-muted">Supports .xlsx and .csv - any format, any column names</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Import Products
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Remove Products --}}
                <div class="card shadow-sm mb-4 border-danger border-opacity-25">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trash-alt me-2 text-danger"></i>Remove All Products</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Remove all products from selected stores. This action cannot be undone.</p>
                        <form method="POST" action="{{ route('admin.products.remove') }}" id="removeProductsForm">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Store(s) to Clear</label>
                                <select class="form-select select2-stores-remove" name="stores[]" multiple required>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->products_count ?? $store->products()->count() }} products)</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" class="btn btn-danger" onclick="confirmRemove()">
                                <i class="fas fa-trash-alt me-2"></i>Remove All Products
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Smart Detection Info --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-magic me-2 text-primary"></i>Smart Auto-Detection</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Our importer automatically reads your Excel file and figures out what each column means. No need to rename your headers!</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-brain me-2 text-primary"></i>What it auto-detects:</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Product Name</strong> - "Product Description", "Product Name", "Item", "Menu", "Nama Produk", etc.</li>
                                        <li><strong>Price</strong> - "Price", "Harga", "Pengguna/RSP", "Retail", "Consumer", "SRP", "Selling Price", etc.</li>
                                        <li><strong>Cost Price</strong> - "Cost", "Pengedar/Distributor", "Wholesale", etc.</li>
                                        <li><strong>Sale/Trade Price</strong> - "Trade", "Peruncit", "Sale Price", "Discount", etc.</li>
                                        <li><strong>SKU/Barcode</strong> - "Barcode", "SKU", "Code", "UPC", "EAN", etc.</li>
                                        <li><strong>Brand</strong> - "Brand", "Jenama"</li>
                                        <li><strong>Flavour/Variant</strong> - "Flavour", "Flavor", "Variant", "Perisa"</li>
                                        <li><strong>Size</strong> - "Product Size", "Saiz"</li>
                                        <li><strong>Category</strong> - "Category", "Kategori", "Jenis"</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold mb-2"><i class="fas fa-cogs me-2 text-success"></i>Smart features:</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Multi-row header support</strong> - Handles Excel files where headers span 2 rows (merged cells)</li>
                                        <li><strong>Auto header detection</strong> - Finds the header row even if your file has a title row above it</li>
                                        <li><strong>Section row skipping</strong> - Automatically skips category headers like "A. DRINKS" or "B. FOOD"</li>
                                        <li><strong>Name building</strong> - Combines Product + Flavour columns into a complete product name</li>
                                        <li><strong>Multi-price support</strong> - When multiple price columns exist, picks the right one for retail, cost, and sale</li>
                                        <li><strong>Fallback detection</strong> - If headers don't match, it analyzes the actual data to guess column types</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning border mt-3 mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Tip:</strong> After importing, we'll show you exactly which columns were mapped to what fields so you can verify the detection was correct.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Remove confirmation modal --}}
    <div class="modal fade" id="confirmRemoveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Remove</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <strong>remove all products</strong> from the selected stores?</p>
                    <p class="text-danger fw-bold mb-0">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('removeProductsForm').submit()">
                        <i class="fas fa-trash-alt me-2"></i>Yes, Remove All
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.select2-stores').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select stores...',
                allowClear: true,
                width: '100%',
            });
            $('.select2-stores-remove').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search and select stores to clear...',
                allowClear: true,
                width: '100%',
            });
        });

        function confirmRemove() {
            var selected = $('.select2-stores-remove').val();
            if (!selected || selected.length === 0) {
                alert('Please select at least one store.');
                return;
            }
            var modal = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));
            modal.show();
        }
    </script>
@endpush
