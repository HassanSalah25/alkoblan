@extends('admin.layouts.app')

@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order ' . $order->order_number)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white">Customer Information</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $order->customer_name }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $order->customer_email }}</dd>

                    <dt class="col-sm-3">Phone</dt>
                    <dd class="col-sm-9">{{ $order->customer_phone ?: '—' }}</dd>

                    <dt class="col-sm-3">Company</dt>
                    <dd class="col-sm-9">{{ $order->customer_company ?: '—' }}</dd>

                    <dt class="col-sm-3">Address</dt>
                    <dd class="col-sm-9">{{ $order->customer_address ?: '—' }}</dd>

                    <dt class="col-sm-3">City</dt>
                    <dd class="col-sm-9">{{ $order->customer_city ?: '—' }}</dd>

                    <dt class="col-sm-3">Country</dt>
                    <dd class="col-sm-9">{{ $order->customer_country ?: '—' }}</dd>

                    <dt class="col-sm-3">Payment Method</dt>
                    <dd class="col-sm-9">{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '—' }}</dd>

                    <dt class="col-sm-3">Payment Status</dt>
                    <dd class="col-sm-9">{{ ucfirst($order->payment_status) }}</dd>

                    <dt class="col-sm-3">Registered User</dt>
                    <dd class="col-sm-9">
                        @if ($order->user)
                            <a href="{{ route('admin.customers.show', $order->user) }}">{{ $order->user->name }}</a>
                        @else
                            Guest checkout
                        @endif
                    </dd>

                    <dt class="col-sm-3">Notes</dt>
                    <dd class="col-sm-9">{{ $order->notes ?: '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">Order Items</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Variant</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->product_sku ?: '—' }}</td>
                                <td>{{ $item->variant_label ?: '—' }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No items.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Subtotal</th>
                            <th class="text-end">{{ number_format($order->subtotal, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Discount</th>
                            <th class="text-end">{{ number_format($order->discount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Tax</th>
                            <th class="text-end">{{ number_format($order->tax, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Shipping</th>
                            <th class="text-end">{{ number_format($order->shipping, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($order->total, 2) }} {{ $order->currency }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        @can('orders.update')
        <div class="card mb-3">
            <div class="card-header bg-white">Update Status</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select">
                            @foreach (['pending', 'confirmed', 'processing', 'completed', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            @foreach (['pending', 'paid', 'failed'] as $s)
                                <option value="{{ $s }}" @selected($order->payment_status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
        @endcan

        <div class="card mb-3">
            <div class="card-header bg-white">Summary</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Placed At</dt>
                    <dd class="col-sm-7">{{ $order->placed_at?->format('Y-m-d H:i') }}</dd>
                    <dt class="col-sm-5">Currency</dt>
                    <dd class="col-sm-7">{{ $order->currency }}</dd>
                </dl>
            </div>
        </div>

        @can('orders.delete')
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order permanently?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete Order</button>
        </form>
        @endcan
    </div>
</div>
@endsection
