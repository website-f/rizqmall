@extends('partials.app')

@section('title', 'My Reviews - RizqMall')

@section('content')
<div class="container py-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Reviews</h2>
            <p class="text-muted mb-0">Manage and view your product and store reviews</p>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Product Reviews</h6>
                            <h2 class="mb-0">{{ $reviews->total() }}</h2>
                        </div>
                        <i class="fas fa-box-open fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Store Reviews</h6>
                            <h2 class="mb-0">{{ $storeReviews->total() }}</h2>
                        </div>
                        <i class="fas fa-store fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4" id="reviewsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="products-tab" data-bs-toggle="pill" data-bs-target="#products"
                type="button" role="tab" aria-selected="true">
                <i class="fas fa-box me-2"></i>Product Reviews
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="stores-tab" data-bs-toggle="pill" data-bs-target="#stores"
                type="button" role="tab" aria-selected="false">
                <i class="fas fa-store me-2"></i>Store Reviews
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reviewsTabContent">
        <!-- Product Reviews -->
        <div class="tab-pane fade show active" id="products" role="tabpanel">
            @if($reviews->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    @foreach($reviews as $review)
                    <div class="list-group-item p-4">
                        <div class="row">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <div class="bg-light rounded p-2 text-center">
                                    @if($review->product)
                                    <img src="{{ $review->product->image_url }}" alt="{{ $review->product->name }}" class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: contain;">
                                    <div class="small fw-bold text-truncate">{{ $review->product->name }}</div>
                                    @else
                                    <span class="text-muted fst-italic">Product Deleted</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $review->title ?? 'Untitled Review' }}</h5>
                                        <div class="text-warning mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                                <span class="text-muted small ms-2">{{ $review->rating }}.0</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>

                                <p class="text-muted mb-3">{{ $review->comment }}</p>

                                @if($review->images)
                                <div class="d-flex gap-2 mt-3">
                                    @foreach($review->images as $image)
                                    <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $image) }}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                    </a>
                                    @endforeach
                                </div>
                                @endif

                                @if($review->verified_purchase)
                                <span class="badge bg-success bg-opacity-10 text-success mt-2">
                                    <i class="fas fa-check-circle me-1"></i>Verified Purchase
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white py-3">
                    {{ $reviews->links() }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-comment-slash fa-4x text-muted opacity-50"></i>
                </div>
                <h4>No product reviews yet</h4>
                <p class="text-muted">You haven't requested to review any products yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>Browse Products
                </a>
            </div>
            @endif
        </div>

        <!-- Store Reviews -->
        <div class="tab-pane fade" id="stores" role="tabpanel">
            @if($storeReviews->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="list-group list-group-flush">
                    @foreach($storeReviews as $review)
                    <div class="list-group-item p-4">
                        <div class="row">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <div class="bg-light rounded p-2 text-center">
                                    @if($review->store)
                                    <img src="{{ $review->store->image_url }}" alt="{{ $review->store->name }}" class="img-fluid rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="small fw-bold text-truncate">{{ $review->store->name }}</div>
                                    @else
                                    <span class="text-muted fst-italic">Store Deleted</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-1">{{ $review->title ?? 'Untitled Review' }}</h5>
                                        <div class="text-warning mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                                <span class="text-muted small ms-2">{{ $review->rating }}.0</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>

                                <p class="text-muted mb-3">{{ $review->comment }}</p>

                                @if($review->verified_purchase)
                                <span class="badge bg-success bg-opacity-10 text-success mt-2">
                                    <i class="fas fa-check-circle me-1"></i>Verified Purchase
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white py-3">
                    {{ $storeReviews->links() }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-store-slash fa-4x text-muted opacity-50"></i>
                </div>
                <h4>No store reviews yet</h4>
                <p class="text-muted">You haven't reviewed any stores yet.</p>
                <a href="{{ route('stores') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-store me-2"></i>Browse Stores
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection