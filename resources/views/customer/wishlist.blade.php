@extends('partials.app')

@section('title', 'My Wishlist - RizqMall')

@section('content')
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">My Wishlist</h2>
                <p class="text-muted mb-0">{{ $wishlistItems->count() ?? 0 }} items saved</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (isset($wishlistItems) && $wishlistItems->count() > 0)
            <div class="row g-4">
                @foreach ($wishlistItems as $item)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm h-100 product-card">
                            <div class="position-relative">
                                <a href="{{ route('product.show', $item->product->slug) }}">
                                    <img src="{{ $item->product->image_url }}" class="card-img-top"
                                        alt="{{ $item->product->name }}" style="height: 250px; object-fit: cover;">
                                </a>

                                <!-- Remove from Wishlist Button -->
                                <form action="{{ route('customer.wishlist.remove', $item) }}" method="POST"
                                    class="position-absolute top-0 end-0 m-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-circle"
                                        title="Remove from wishlist">
                                        <i class="fas fa-times text-danger"></i>
                                    </button>
                                </form>

                                @if ($item->product->sale_price)
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                        -{{ round((($item->product->regular_price - $item->product->sale_price) / $item->product->regular_price) * 100) }}%
                                    </span>
                                @endif

                                @if ($item->product->stock_quantity <= 0)
                                    <span class="badge bg-secondary position-absolute bottom-0 start-0 m-2">
                                        Out of Stock
                                    </span>
                                @elseif($item->product->stock_quantity < 10)
                                    <span class="badge bg-warning position-absolute bottom-0 start-0 m-2">
                                        Only {{ $item->product->stock_quantity }} left
                                    </span>
                                @endif
                            </div>

                            <div class="card-body">
                                <h6 class="card-title mb-2">
                                    <a href="{{ route('product.show', $item->product->slug) }}"
                                        class="text-decoration-none text-dark">
                                        {{ Str::limit($item->product->name, 50) }}
                                    </a>
                                </h6>

                                <p class="text-muted small mb-2">
                                    <i class="fas fa-store me-1"></i>{{ $item->product->store->name }}
                                </p>

                                <div class="mb-3">
                                    @if ($item->product->sale_price)
                                        <span class="h5 text-primary mb-0">RM
                                            {{ number_format($item->product->sale_price, 2) }}</span>
                                        <span class="text-muted text-decoration-line-through ms-2">RM
                                            {{ number_format($item->product->regular_price, 2) }}</span>
                                    @else
                                        <span class="h5 text-primary mb-0">RM
                                            {{ number_format($item->product->regular_price, 2) }}</span>
                                    @endif
                                </div>

                                @if ($item->product->rating)
                                    <div class="mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fas fa-star {{ $i <= $item->product->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                        <span
                                            class="text-muted small ms-1">({{ $item->product->reviews_count ?? 0 }})</span>
                                    </div>
                                @endif

                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product->id }}">
                                    <input type="hidden" name="quantity" value="1">

                                    @if ($item->product->stock_quantity > 0)
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary w-100" disabled>
                                            Out of Stock
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($wishlistItems->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $wishlistItems->links() }}
                </div>
            @endif
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Your wishlist is empty</h5>
                    <p class="text-muted mb-3">Save items you love for later</p>
                    <a href="{{ route('rizqmall.home') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        @endif

        <!-- Wishlist Actions -->
        @if (isset($wishlistItems) && $wishlistItems->count() > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0">Wishlist Actions</h6>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <form action="{{ route('customer.wishlist.add-all-to-cart') }}" method="POST"
                                class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-cart-plus me-2"></i>Add All to Cart
                                </button>
                            </form>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#shareWishlistModal">
                                <i class="fas fa-share-alt me-2"></i>Share Wishlist
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Share Wishlist Modal -->
    <div class="modal fade" id="shareWishlistModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Your Wishlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Share your wishlist with friends and family!</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="wishlistUrl"
                            value="{{ url('/wishlist/share/' . auth()->id()) }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWishlistUrl()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="https://wa.me/?text={{ urlencode('Check out my wishlist: ' . url('/wishlist/share/' . auth()->id())) }}"
                            class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Share on WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/wishlist/share/' . auth()->id())) }}"
                            class="btn btn-primary" target="_blank">
                            <i class="fab fa-facebook me-2"></i>Share on Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('/wishlist/share/' . auth()->id())) }}&text={{ urlencode('Check out my wishlist!') }}"
                            class="btn btn-info text-white" target="_blank">
                            <i class="fab fa-twitter me-2"></i>Share on Twitter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function copyWishlistUrl() {
            const urlInput = document.getElementById('wishlistUrl');
            urlInput.select();
            document.execCommand('copy');

            // Show feedback
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    </script>
@endpush

@push('styles')
    <style>
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endpush
