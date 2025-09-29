@extends('partials.admin')

@section('title', 'Add New Product')

@section('content')
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
            <li class="breadcrumb-item active">Add Product</li>
        </ol>
    </nav>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 d-flex align-items-center mb-4" role="alert">
            <span class="fas fa-check-circle text-success me-2"></span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 d-flex align-items-center mb-4" role="alert">
            <span class="fas fa-exclamation-triangle text-danger me-2"></span>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form class="mb-9" action="{{ route('store.products.store', ['store' => $store->id]) }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf
        <div class="row g-3 flex-between-end mb-5">
            <div class="col-auto">
                <h2 class="mb-2">Add a Product</h2>
                <h5 class="text-body-tertiary fw-semibold">
                    Listing products for your store: <strong>{{ Auth::user()->store->name ?? 'N/A' }}</strong>
                </h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-phoenix-secondary me-2 mb-2 mb-sm-0" type="button">Discard</button>
                <button class="btn btn-phoenix-primary me-2 mb-2 mb-sm-0" type="button">Save draft</button>
                <button class="btn btn-primary mb-2 mb-sm-0" type="submit">Publish product</button>
            </div>
        </div>

        <div class="row g-5">
            {{-- Left column --}}
            <div class="col-12 col-xl-8">

                {{-- Product Title --}}
                <h4 class="mb-3">Product Title</h4>
                <input class="form-control mb-5 @error('name') is-invalid @enderror"
                       type="text" name="name" placeholder="Write title here..."
                       value="{{ old('name') }}" required />
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror

                {{-- Product Description --}}
                <div class="mb-6">
                    <h4 class="mb-3">Product Description</h4>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              name="description" rows="8"
                              placeholder="Write a detailed description here...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Images (file input + preview) --}}
                <h4 class="mb-3">Display images</h4>
                <div id="dzContainer" class="dropzone dropzone-multiple p-0 mb-3 border-dashed border-2 border-translucent"
                     style="min-height:120px; padding:16px;">
                    <div class="fallback d-none">
                        <input id="imagesInput" name="images[]" type="file" multiple accept="image/*" />
                    </div>

                    <div id="dzPreview" class="d-flex flex-wrap gap-2 mt-3"></div>

                    <div class="dz-instructions mt-2 text-body-tertiary">
                        Drag your photo(s) here <span class="text-body-secondary px-1">or</span>
                        <button class="btn btn-link p-0" type="button" id="browseFilesBtn">Browse from device</button>
                        <div class="form-text">You can add multiple images. Click the thumbnail's × to remove before upload.</div>
                    </div>
                </div>

                {{-- Inventory (tabs) --}}
                <h4 class="mb-3">Inventory</h4>
                <div class="row g-0 border-top border-bottom">
                    <div class="col-sm-4">
                        <div class="nav flex-sm-column border-bottom border-end-sm fs-9 vertical-tab h-100 justify-content-between" role="tablist">
                            <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#pricingTabContent" role="tab">
                                <span class="me-sm-2 fs-4 nav-icons" data-feather="tag"></span>
                                <span class="d-none d-sm-inline">Pricing & Stock</span>
                            </a>
                            <a class="nav-link" data-bs-toggle="tab" data-bs-target="#attributesTabContent" role="tab">
                                <span class="me-sm-2 fs-4 nav-icons" data-feather="sliders"></span>
                                <span class="d-none d-sm-inline">Attributes</span>
                            </a>
                            <a class="nav-link" data-bs-toggle="tab" data-bs-target="#advancedTabContent" role="tab">
                                <span class="me-sm-2 fs-4 nav-icons" data-feather="lock"></span>
                                <span class="d-none d-sm-inline">Advanced IDs</span>
                            </a>
                        </div>
                    </div>

                    <div class="col-sm-8">
                        <div class="tab-content py-3 ps-sm-4 h-100">

                            {{-- Pricing & Stock --}}
                            <div class="tab-pane fade show active" id="pricingTabContent" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Regular price (RM)</h5>
                                        <input class="form-control @error('regular_price') is-invalid @enderror"
                                               type="number" step="0.01" name="regular_price"
                                               placeholder="10.00" value="{{ old('regular_price') }}" required />
                                        @error('regular_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Sale price (RM)</h5>
                                        <input class="form-control @error('sale_price') is-invalid @enderror"
                                               type="number" step="0.01" name="sale_price"
                                               placeholder="8.99 (Optional)" value="{{ old('sale_price') }}" />
                                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Initial Stock Quantity</h5>
                                        <input class="form-control @error('stock_quantity') is-invalid @enderror"
                                               type="number" name="stock_quantity" placeholder="100"
                                               value="{{ old('stock_quantity', 0) }}" required />
                                        @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Attributes --}}
                            <div class="tab-pane fade" id="attributesTabContent" role="tabpanel">
                                <h5 class="mb-3 text-body-highlight">Attributes</h5>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="fragileCheck" type="checkbox" name="is_fragile" value="1" {{ old('is_fragile') ? 'checked' : '' }} />
                                    <label class="form-check-label fs-8" for="fragileCheck">Fragile Product</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="biodegradableCheck" type="checkbox" name="is_biodegradable" value="1" {{ old('is_biodegradable') ? 'checked' : '' }} />
                                    <label class="form-check-label fs-8" for="biodegradableCheck">Biodegradable</label>
                                </div>

                                <div class="mb-3 mt-3">
                                    <h5 class="mb-2 text-body-highlight">Category</h5>
                                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" aria-label="category">
                                        <option value="" selected>Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <h5 class="mb-2 text-body-highlight">Tags</h5>
                                    <select class="form-select" name="tags[]" multiple aria-label="Tags">
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Hold CTRL (Windows) / CMD (Mac) to select multiple.</div>
                                </div>
                            </div>

                            {{-- Advanced --}}
                            <div class="tab-pane fade" id="advancedTabContent" role="tabpanel">
                                <h5 class="mb-3 text-body-highlight">Advanced</h5>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Product ID Type</h5>
                                        <select class="form-select" name="product_id_type">
                                            <option value="" selected disabled>Select Type</option>
                                            @foreach(['ISBN','UPC','EAN','JAN','SKU'] as $type)
                                                <option value="{{ $type }}" {{ old('product_id_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Product ID</h5>
                                        <input class="form-control" type="text" name="product_id_value" placeholder="Enter ID Number" value="{{ old('product_id_value') }}" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div> {{-- end left column --}}

            {{-- Right column / sidebar --}}
            <div class="col-12 col-xl-4">
                <div class="row g-2">
                    

                    {{-- Variants --}}
                    <div class="col-12 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Variants</h4>
                                <div id="variantContainer">
                                    {{-- First Variant Template --}}
                                    <div class="variant-item border border-translucent p-3 rounded mb-3" data-index="0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="text-body-highlight fs-8">Variant #1</h5>
                                            <button type="button" class="btn btn-sm btn-danger remove-variant" style="display:none;">Remove</button>
                                        </div>

                                        <div class="mb-2">
                                            <label class="form-label fs-9">Variant Name</label>
                                            <input class="form-control" type="text" name="variants[0][name]" placeholder="Blue - 8GB" required />
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fs-9">Price Override (RM)</label>
                                                <input class="form-control" type="number" step="0.01" name="variants[0][price]" placeholder="13.99" />
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fs-9">Stock</label>
                                                <input class="form-control" type="number" name="variants[0][stock_quantity]" placeholder="50" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-phoenix-primary w-100" type="button" id="addVariantBtn">
                                    ➕ Add another variant
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div> {{-- end right column --}}
        </div>
    </form>
@endsection

@push('scripts')
<style>
    /* pointer cursor for the tabs / vertical tab anchors */
    .nav-link { cursor: pointer; }
    .variant-item .remove-variant { display: inline-block; }
    /* image preview styles */
    #dzPreview .thumb {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        position: relative;
    }
    #dzPreview .thumb-wrap { position: relative; display:inline-block; margin-right:8px; }
    #dzPreview .thumb-remove { position:absolute; top:-6px; right:-6px; background:#ff5b5b; color:#fff; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:12px; cursor:pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ----------------------------
     * Variants — dynamic add/remove
     * ---------------------------- */
    let variantIndex = 1;
    const container = document.getElementById('variantContainer');
    const addBtn = document.getElementById('addVariantBtn');

    addBtn.addEventListener('click', () => {
        const idx = variantIndex;
        const block = document.createElement('div');
        block.className = 'variant-item border border-translucent p-3 rounded mb-3';
        block.dataset.index = idx;
        block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="text-body-highlight fs-8">Variant #${idx + 1}</h5>
                <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
            </div>
            <div class="mb-2">
                <label class="form-label fs-9">Variant Name</label>
                <input class="form-control" type="text" name="variants[${idx}][name]" placeholder="e.g., Red - 16GB" required />
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label fs-9">Price Override (RM)</label>
                    <input class="form-control" type="number" step="0.01" name="variants[${idx}][price]" placeholder="14.99" />
                </div>
                <div class="col-6">
                    <label class="form-label fs-9">Stock</label>
                    <input class="form-control" type="number" name="variants[${idx}][stock_quantity]" placeholder="50" required />
                </div>
            </div>
        `;
        container.appendChild(block);

        block.querySelector('.remove-variant').addEventListener('click', () => {
            block.remove();
            // we do not reindex names (Laravel accepts sparse arrays)
        });

        variantIndex++;
    });

    // Optional: allow remove on initial variant if you want (hidden by default)
    // document.querySelectorAll('.remove-variant').forEach(btn => ...);

    /* -----------------------------------
     * Images: multiple select + previews
     * Use the fallback input (imagesInput)
     * and keep input.files in sync via DataTransfer
     * ----------------------------------- */
    const imagesInput = document.getElementById('imagesInput');
    const previewArea = document.getElementById('dzPreview');
    const browseBtn = document.getElementById('browseFilesBtn');

    // file store (keeps the current selection)
    let fileStore = [];

    browseBtn.addEventListener('click', () => imagesInput.click());

    imagesInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files || []);
        // append new files (avoid duplicates by name+size)
        files.forEach(f => {
            const duplicate = fileStore.some(existing => existing.name === f.name && existing.size === f.size && existing.lastModified === f.lastModified);
            if (!duplicate) fileStore.push(f);
        });
        rebuildPreviewAndInput();
    });

    function rebuildPreviewAndInput() {
        // rebuild preview area
        previewArea.innerHTML = '';
        fileStore.forEach((file, idx) => {
            const wrap = document.createElement('div');
            wrap.className = 'thumb-wrap';

            const img = document.createElement('img');
            img.className = 'thumb';
            img.alt = file.name;

            // read file
            const reader = new FileReader();
            reader.onload = function(ev) { img.src = ev.target.result; };
            reader.readAsDataURL(file);

            const removeBtn = document.createElement('div');
            removeBtn.className = 'thumb-remove';
            removeBtn.title = 'Remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', () => {
                fileStore.splice(idx, 1);
                rebuildPreviewAndInput();
            });

            wrap.appendChild(img);
            wrap.appendChild(removeBtn);
            previewArea.appendChild(wrap);
        });

        // rebuild DataTransfer -> set to images input so normal form submit works
        try {
            const dt = new DataTransfer();
            fileStore.forEach(f => dt.items.add(f));
            imagesInput.files = dt.files;
        } catch (err) {
            // fallback: if DataTransfer not supported, we leave input as-is
            console.warn('DataTransfer not supported in this browser; file preview still shown but form submit may behave differently.', err);
        }
    }

    // If files preloaded via old() (server-side repop): we won't reconstruct them here.
    // You may add server-side preview logic if needed.

    /* ---------------------------
     * Form submit: let native submit proceed
     * (the imagesInput.files already kept in sync above)
     * --------------------------- */
    // no AJAX here — fall back to normal multipart submit

});
</script>
@endpush
