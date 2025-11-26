@extends('partials.app')

@section('title', 'Shopping Cart')

@section('content')
    <style>
        .cart-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cart-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #e5e7eb;
        }

        .cart-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .cart-items {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 24px 0;
            border-bottom: 2px solid #f3f4f6;
            position: relative;
            transition: all 0.3s ease;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #e5e7eb;
            transition: transform 0.2s;
        }

        .cart-item-image:hover {
            transform: scale(1.05);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .cart-item-name:hover {
            color: #3b82f6;
        }

        .cart-item-variant {
            font-size: 14px;
            margin-bottom: 8px;
        }

        .cart-item-variant .badge {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .cart-item-store {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .cart-item-store a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.2s;
        }

        .cart-item-store a:hover {
            color: #2563eb;
        }

        .cart-item-price {
            font-size: 24px;
            font-weight: 800;
            color: #3b82f6;
            margin-top: 8px;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 12px;
        }

        .cart-qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f3f4f6;
            padding: 8px;
            border-radius: 10px;
        }

        .cart-qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            color: #6b7280;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-qty-btn:hover {
            background: #3b82f6;
            color: white;
            transform: scale(1.1);
        }

        .cart-qty-value {
            min-width: 40px;
            text-align: center;
            font-weight: 700;
            color: #1f2937;
            font-size: 16px;
        }

        .cart-remove-btn {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
            font-size: 14px;
        }

        .cart-remove-btn:hover {
            background: #fee2e2;
            transform: scale(1.05);
        }

        .cart-summary {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 20px;
            padding: 32px;
            position: sticky;
            top: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .cart-summary h3 {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 24px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 16px;
            color: #4b5563;
        }

        .summary-row.total {
            font-size: 22px;
            font-weight: 800;
            color: #1f2937;
            padding-top: 16px;
            border-top: 2px solid #3b82f6;
            margin-top: 16px;
        }

        .checkout-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.3s;
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .empty-cart-icon {
            font-size: 80px;
            color: #d1d5db;
            margin-bottom: 24px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .empty-cart h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .empty-cart p {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 32px;
        }

        .continue-shopping-btn {
            padding: 16px 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .continue-shopping-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
            color: white;
        }

        .clear-cart-btn {
            padding: 12px 24px;
            background: white;
            color: #ef4444;
            border: 2px solid #ef4444;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .clear-cart-btn:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
            }

            .cart-item-image {
                width: 100%;
                height: 200px;
            }

            .cart-item-actions {
                justify-content: space-between;
            }

            .cart-summary {
                position: static;
            }
        }
    </style>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart me-3"></i>Shopping Cart</h1>
            <p class="text-muted">{{ $cart->item_count }} {{ Str::plural('item', $cart->item_count) }} in your cart</p>
        </div>

        @if ($cart->items->count() > 0)
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-items">
                        @foreach ($cart->items as $item)
                            <div class="cart-item" data-item-id="{{ $item->id }}">
                                <div class="cart-item-image">
                                    <img src="{{ $item->product->images->first()?->thumbnail_url ?? asset('images/placeholder.png') }}"
                                        alt="{{ $item->product->name }}">
                                </div>

                                <div class="cart-item-details">
                                    <a href="{{ route('products.index', $item->product->slug) }}" class="cart-item-name">
                                        {{ $item->product->name }}
                                    </a>

                                    @if ($item->variant)
                                        <div class="cart-item-variant">
                                            @foreach ($item->variant->options as $option)
                                                <span class="badge me-1">
                                                    {{ $option->type->name }}: {{ $option->value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="cart-item-store">
                                        <i class="fas fa-store me-1"></i>
                                        <a href="{{ route('store.profile', $item->product->store->slug) }}">
                                            {{ $item->product->store->name }}
                                        </a>
                                    </div>

                                    <div class="cart-item-price">
                                        RM <span class="line-total">{{ number_format($item->line_total, 2) }}</span>
                                    </div>

                                    <div class="cart-item-actions">
                                        <div class="cart-qty-control">
                                            <button class="cart-qty-btn" onclick="updateCartQty({{ $item->id }}, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="cart-qty-value"
                                                id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                            <button class="cart-qty-btn" onclick="updateCartQty({{ $item->id }}, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>

                                        <button class="cart-remove-btn" onclick="removeCartItem({{ $item->id }})">
                                            <i class="fas fa-trash-alt me-1"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <button class="clear-cart-btn" onclick="clearCart()">
                            <i class="fas fa-trash me-2"></i> Clear Cart
                        </button>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3>Order Summary</h3>

                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span class="fw-bold" id="summary-subtotal">RM {{ number_format($cart->subtotal, 2) }}</span>
                        </div>

                        <div class="summary-row">
                            <span>Tax (6%):</span>
                            <span class="fw-bold" id="summary-tax">RM {{ number_format($cart->tax, 2) }}</span>
                        </div>

                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span class="fw-bold" id="summary-shipping">
                                @if ($cart->shipping > 0)
                                    RM {{ number_format($cart->shipping, 2) }}
                                @else
                                    <span class="text-success">FREE</span>
                                @endif
                            </span>
                        </div>

                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="summary-total">RM {{ number_format($cart->grand_total, 2) }}</span>
                        </div>

                        <button class="checkout-btn" onclick="window.location.href='/checkout'">
                            <i class="fas fa-lock me-2"></i> Proceed to Checkout
                        </button>

                        <div class="text-center mt-3">
                            <a href="/products" class="text-primary text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet</p>
                <a href="/products" class="continue-shopping-btn">
                    <i class="fas fa-shopping-bag me-2"></i> Start Shopping
                </a>
            </div>
        @endif
    </div>

    <script>
        // Update cart item quantity
        async function updateCartQty(itemId, change) {
            const qtyElement = document.getElementById(`qty-${itemId}`);
            const currentQty = parseInt(qtyElement.textContent);
            const newQty = currentQty + change;

            if (newQty < 1) {
                removeCartItem(itemId);
                return;
            }

            if (newQty > 99) {
                CartManager.showErrorModal('Maximum quantity is 99');
                return;
            }

            const data = await CartManager.updateQuantity(itemId, newQty);

            if (data) {
                qtyElement.textContent = newQty;

                const lineTotal = document.querySelector(`[data-item-id="${itemId}"] .line-total`);
                if (lineTotal) {
                    lineTotal.textContent = data.line_total;
                }

                updateSummary(data);
            }
        }

        // Remove cart item
        async function removeCartItem(itemId) {
            if (!confirm('Are you sure you want to remove this item?')) {
                return;
            }

            const data = await CartManager.removeItem(itemId);

            if (data) {
                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                if (itemElement) {
                    itemElement.style.opacity = '0';
                    itemElement.style.transform = 'translateX(-100%)';
                    setTimeout(() => itemElement.remove(), 300);
                }

                updateSummary(data);

                setTimeout(() => {
                    const remainingItems = document.querySelectorAll('.cart-item').length;
                    if (remainingItems === 0) {
                        location.reload();
                    }
                }, 400);
            }
        }

        // Clear entire cart
        async function clearCart() {
            if (!confirm('Are you sure you want to clear your entire cart?')) {
                return;
            }

            try {
                const response = await fetch('/cart/clear', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
                CartManager.showErrorModal('Failed to clear cart');
            }
        }

        // Update summary section
        function updateSummary(data) {
            document.getElementById('summary-subtotal').textContent = `RM ${data.subtotal}`;
            document.getElementById('summary-tax').textContent = `RM ${data.tax}`;

            const shippingElement = document.getElementById('summary-shipping');
            if (parseFloat(data.shipping) > 0) {
                shippingElement.innerHTML = `RM ${data.shipping}`;
            } else {
                shippingElement.innerHTML = '<span class="text-success">FREE</span>';
            }

            document.getElementById('summary-total').textContent = `RM ${data.grand_total}`;

            const headerCount = document.querySelector('.cart-header p');
            if (headerCount) {
                headerCount.textContent = `${data.cart_count} ${data.cart_count === 1 ? 'item' : 'items'} in your cart`;
            }
        }
    </script>
@endsection
