@extends('admin.layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')

@php
$statusBadge = [
    'pending' => 'secondary',
    'confirmed' => 'info',
    'processing' => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger',
];
$paymentBadge = [
    'pending' => 'secondary',
    'paid' => 'success',
    'failed' => 'danger',
];
@endphp

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <form method="GET" class="row gy-2 gx-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search order #/customer/email...">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'confirmed', 'processing', 'completed', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" title="From">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" title="To">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Placed At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                        <td><span class="badge bg-{{ $statusBadge[$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span></td>
                        <td><span class="badge bg-{{ $paymentBadge[$order->payment_status] ?? 'secondary' }}">{{ ucfirst($order->payment_status) }}</span></td>
                        <td>{{ $order->placed_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @can('orders.view')
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @endcan
                            @can('orders.delete')
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Delete this order permanently?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $orders->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
