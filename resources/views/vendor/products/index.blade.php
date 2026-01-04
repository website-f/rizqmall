@extends('partials.admin')

@section('title', 'My Products - RizqMall')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">My Products</h2>
            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Product
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 80px;">Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="ps-4">
                                        @if ($product->images && $product->images->count() > 0)
                                            <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}"
                                                class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <small class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $product->category->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>
                                        RM {{ number_format($product->price, 2) }}
                                        @if ($product->sale_price)
                                            <br>
                                            <small class="text-danger text-decoration-line-through">
                                                RM {{ number_format($product->regular_price, 2) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->manage_stock)
                                            {{ $product->stock_quantity }}
                                            @if ($product->stock_quantity <= $product->low_stock_threshold)
                                                <span class="text-warning" title="Low Stock"><i
                                                        class="fas fa-exclamation-triangle"></i></span>
                                            @endif
                                        @else
                                            <span class="text-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                            {{ $product->is_active ? 'Active' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('vendor.products.edit', $product) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vendor.products.destroy', $product) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3"></i>
                                            <p class="mb-2">No products found.</p>
                                            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">
                                                Create Your First Product
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($products->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
