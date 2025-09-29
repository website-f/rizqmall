@extends('partials.admin')

@section('title', 'Add New Product')
    
@section('content')
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
            <li class="breadcrumb-item active">Add Product</li>
        </ol>
    </nav>
    
    {{-- Alerts for success/error messages --}}
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

    {{-- Form setup for POST submission --}}
    <form class="mb-9" action="{{ route('store.products.store', ['store' => $store->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3 flex-between-end mb-5">
            <div class="col-auto">
                <h2 class="mb-2">Add a product</h2>
                <h5 class="text-body-tertiary fw-semibold">Listing products for your store: **{{ Auth::user()->store->name ?? 'N/A' }}**</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-phoenix-secondary me-2 mb-2 mb-sm-0" type="button">Discard</button>
                <button class="btn btn-phoenix-primary me-2 mb-2 mb-sm-0" type="button">Save draft</button>
                <button class="btn btn-primary mb-2 mb-sm-0" type="submit">Publish product</button>
            </div>
        </div>
        
        <div class="row g-5">
            <div class="col-12 col-xl-8">
                
                {{-- Product Title --}}
                <h4 class="mb-3">Product Title</h4>
                <input class="form-control mb-5 @error('name') is-invalid @enderror" type="text" name="name" placeholder="Write title here..." value="{{ old('name') }}" required />
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                
                {{-- Product Description --}}
                <div class="mb-6">
                    <h4 class="mb-3"> Product Description</h4>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="8" placeholder="Write a detailed description here...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                {{-- Display Images (Placeholder for Dropzone) --}}
                <h4 class="mb-3">Display images</h4>
                <div class="dropzone dropzone-multiple p-0 mb-5 border-dashed border-2 border-translucent" id="my-awesome-dropzone" data-dropzone="data-dropzone">
                    <div class="fallback">
                        <input name="images[]" type="file" multiple="multiple" accept="image/*" />
                    </div>
                    <div class="dz-message text-body-tertiary text-opacity-85" data-dz-message="data-dz-message">
                        Drag your photo(s) here<span class="text-body-secondary px-1">or</span>
                        <button class="btn btn-link p-0" type="button">Browse from device</button><br />
                        <img class="mt-3 me-2" src="https://placehold.co/40x40/E8E8FF/3B5998?text=IMG" width="40" alt="" />
                    </div>
                </div>
                
                {{-- Inventory Tabs --}}
                <h4 class="mb-3">Inventory</h4>
                <div class="row g-0 border-top border-bottom">
                    <div class="col-sm-4">
                        <div class="nav flex-sm-column border-bottom border-bottom-sm-0 border-end-sm fs-9 vertical-tab h-100 justify-content-between" role="tablist" aria-orientation="vertical">
                            <a class="nav-link border-end border-end-sm-0 border-bottom-sm text-center text-sm-start cursor-pointer outline-none d-sm-flex align-items-sm-center active" id="pricingTab" data-bs-toggle="tab" data-bs-target="#pricingTabContent" role="tab" aria-controls="pricingTabContent" aria-selected="true"> <span class="me-sm-2 fs-4 nav-icons" data-feather="tag"></span><span class="d-none d-sm-inline">Pricing & Stock</span></a>
                            <a class="nav-link border-end border-end-sm-0 border-bottom-sm text-center text-sm-start cursor-pointer outline-none d-sm-flex align-items-sm-center" id="attributesTab" data-bs-toggle="tab" data-bs-target="#attributesTabContent" role="tab" aria-controls="attributesTabContent" aria-selected="false"> <span class="me-sm-2 fs-4 nav-icons" data-feather="sliders"></span><span class="d-none d-sm-inline">Attributes</span></a>
                            <a class="nav-link text-center text-sm-start cursor-pointer outline-none d-sm-flex align-items-sm-center" id="advancedTab" data-bs-toggle="tab" data-bs-target="#advancedTabContent" role="tab" aria-controls="advancedTabContent" aria-selected="false"> <span class="me-sm-2 fs-4 nav-icons" data-feather="lock"></span><span class="d-none d-sm-inline">Advanced IDs</span></a>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="tab-content py-3 ps-sm-4 h-100">
                            
                            {{-- Pricing & Stock Tab --}}
                            <div class="tab-pane fade show active" id="pricingTabContent" role="tabpanel">
                                <h4 class="mb-3 d-sm-none">Pricing & Stock</h4>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Regular price ($)</h5>
                                        <input class="form-control @error('regular_price') is-invalid @enderror" type="number" step="0.01" name="regular_price" placeholder="10.00" value="{{ old('regular_price') }}" required />
                                        @error('regular_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Sale price ($)</h5>
                                        <input class="form-control @error('sale_price') is-invalid @enderror" type="number" step="0.01" name="sale_price" placeholder="8.99 (Optional)" value="{{ old('sale_price') }}" />
                                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Initial Stock Quantity</h5>
                                        <input class="form-control @error('stock_quantity') is-invalid @enderror" type="number" name="stock_quantity" placeholder="100" value="{{ old('stock_quantity', 0) }}" required />
                                        @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Attributes Tab (Product Flags) --}}
                            <div class="tab-pane fade" id="attributesTabContent" role="tabpanel" aria-labelledby="attributesTab">
                                <h5 class="mb-3 text-body-highlight">Attributes</h5>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="fragileCheck" type="checkbox" name="is_fragile" value="1" {{ old('is_fragile') ? 'checked' : '' }} />
                                    <label class="form-check-label text-body fs-8" for="fragileCheck">Fragile Product</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" id="biodegradableCheck" type="checkbox" name="is_biodegradable" value="1" {{ old('is_biodegradable') ? 'checked' : '' }} />
                                    <label class="form-check-label text-body fs-8" for="biodegradableCheck">Biodegradable</label>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" id="frozenCheck" type="checkbox" name="is_frozen" value="1" {{ old('is_frozen') ? 'checked' : '' }} />
                                        <label class="form-check-label text-body fs-8" for="frozenCheck">Frozen Product</label>
                                    </div>
                                    <input class="form-control mt-2 @error('max_temperature') is-invalid @enderror" type="text" name="max_temperature" placeholder="Max. allowed Temperature (e.g., -18°C)" style="max-width: 350px;" value="{{ old('max_temperature') }}" />
                                    @error('max_temperature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" id="expiryCheck" type="checkbox" name="has_expiry" value="1" {{ old('has_expiry') ? 'checked' : '' }} />
                                    <label class="form-check-label text-body fs-8" for="expiryCheck">Expiry Date of Product</label>
                                    <input class="form-control inventory-attributes datetimepicker mt-2 @error('expiry_date') is-invalid @enderror" id="expiryDate" type="date" name="expiry_date" style="max-width: 350px;" value="{{ old('expiry_date') }}" />
                                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            
                            {{-- Advanced IDs Tab --}}
                            <div class="tab-pane fade" id="advancedTabContent" role="tabpanel" aria-labelledby="advancedTab">
                                <h5 class="mb-3 text-body-highlight">Advanced</h5>
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Product ID Type</h5>
                                        <select class="form-select" name="product_id_type" aria-label="Product ID Type">
                                            <option value="" selected disabled>Select Type</option>
                                            @foreach(['ISBN', 'UPC', 'EAN', 'JAN', 'SKU'] as $type)
                                                <option value="{{ $type }}" {{ old('product_id_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <h5 class="mb-2 text-body-highlight">Product ID</h5>
                                        <input class="form-control @error('product_id_value') is-invalid @enderror" type="text" name="product_id_value" placeholder="Enter ID Number" value="{{ old('product_id_value') }}" />
                                        @error('product_id_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Right Sidebar: Organization and Variants --}}
            <div class="col-12 col-xl-4">
                <div class="row g-2">
                    
                    {{-- Organization Card --}}
                    <div class="col-12 col-xl-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Organize</h4>
                                <div class="row gx-3">
                                    {{-- Category --}}
                                    <div class="col-12 col-sm-6 col-xl-12">
                                        <div class="mb-4">
                                            <div class="d-flex flex-wrap mb-2">
                                                <h5 class="mb-0 text-body-highlight me-2">Category</h5>
                                                <a class="fw-bold fs-9" href="#!">Add new category</a>
                                            </div>
                                            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" aria-label="category">
                                                <option value="" selected>Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    
                                    {{-- Tags --}}
                                    <div class="col-12 col-sm-6 col-xl-12">
                                        <div class="mb-4">
                                            <div class="d-flex flex-wrap mb-2">
                                                <h5 class="mb-0 text-body-highlight me-2">Tags</h5>
                                                <a class="fw-bold fs-9 lh-sm" href="#!">View all tags</a>
                                            </div>
                                            {{-- Use multiple select for tags --}}
                                            <select class="form-select" name="tags[]" multiple aria-label="Tags">
                                                <option value="" disabled>Select Tags</option>
                                                @foreach($tags as $tag)
                                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    {{-- Collection (Kept as simple text input placeholder) --}}
                                    <div class="col-12 col-xl-12">
                                         <div class="mb-4">
                                             <h5 class="mb-2 text-body-highlight">Collection (Manual Entry)</h5>
                                             <input class="form-control mb-xl-3" type="text" placeholder="Summer 2024 Collection" />
                                         </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Variants Card (Simple Example for Saving) --}}
                    <div class="col-12 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Variants (Simple Example)</h4>
                                <p class="text-body-tertiary fs-9">This simple input demonstrates the database variant saving structure.</p>

                                <div class="border border-translucent p-3 rounded mb-3">
                                    <h5 class="text-body-highlight fs-8">Example Variant</h5>
                                    <div class="mb-2">
                                        <label for="variantName" class="form-label fs-9">Variant Name (e.g., Color - Blue)</label>
                                        <input class="form-control" type="text" name="variants[0][name]" placeholder="Blue - 8GB" value="{{ old('variants.0.name') }}" required />
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label for="variantPrice" class="form-label fs-9">Price Override ($)</label>
                                            <input class="form-control" type="number" step="0.01" name="variants[0][price]" placeholder="13.99" value="{{ old('variants.0.price') }}" />
                                        </div>
                                        <div class="col-6">
                                            <label for="variantStock" class="form-label fs-9">Stock</label>
                                            <input class="form-control" type="number" name="variants[0][stock_quantity]" placeholder="50" value="{{ old('variants.0.stock_quantity') }}" required />
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-phoenix-primary w-100" type="button">Add another option (Requires JS)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
