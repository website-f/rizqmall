@extends('partials.app')

@section('title', 'Order Success')

@section('content')
<div class="container-small py-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            @if($order->payment_status === 'paid')
            <div class="mb-4 text-success">
                <i class="fas fa-check-circle fa-5x"></i>
            </div>
            <h2 class="mb-3">Payment Successful!</h2>
            <p class="lead text-muted mb-4">Thank you for your purchase. Your order has been confirmed.</p>
            @else
            <div class="mb-4 text-warning">
                <i class="fas fa-clock fa-5x"></i>
            </div>
            <h2 class="mb-3">Order Placed!</h2>
            <p class="lead text-muted mb-4">Your order has been placed. Payment is being processed.</p>
            @endif

            <div class="alert {{ $order->payment_status === 'paid' ? 'alert-success' : 'alert-info' }}">
                <div class="row text-start">
                    <div class="col-6">
                        <strong>Order Number:</strong><br>
                        {{ $order->order_number }}
                    </div>
                    <div class="col-6 text-end">
                        <strong>Payment Status:</strong><br>
                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
                @if($order->payment_reference)
                <hr>
                <small class="text-muted">Payment Reference: {{ $order->payment_reference }}</small>
                @endif
            </div>

            @if(isset($relatedOrders) && count($relatedOrders) > 0)
            <div class="mt-4 text-start">
                <h5>Order Details:</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Store</th>
                                <th>Items</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatedOrders as $ord)
                            <tr>
                                <td>{{ $ord->order_number }}</td>
                                <td>{{ $ord->store->name ?? 'N/A' }}</td>
                                <td>{{ $ord->items->count() }} item(s)</td>
                                <td class="text-end">RM {{ number_format($ord->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3">Grand Total</td>
                                <td class="text-end">RM {{ number_format($relatedOrders->sum('total'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            <div class="mt-5">
                <a href="{{ route('customer.orders.index') }}" class="btn btn-primary me-2">
                    <i class="fas fa-shopping-bag me-2"></i>View My Orders
                </a>
                <a href="{{ route('rizqmall.home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection