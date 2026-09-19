@extends('admin.layouts.app')

@section('title', 'Customer: ' . $customer->name)
@section('page-title', 'Customer: ' . $customer->name)

@php
$statusBadge = [
    'pending' => 'secondary',
    'confirmed' => 'info',
    'processing' => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger',
];
@endphp

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Customer Details</span>
                @can('customers.update')
                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                @endcan
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $customer->name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $customer->email }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $customer->phone ?: '—' }}</dd>

                    <dt class="col-sm-4">Company</dt>
                    <dd class="col-sm-8">{{ $customer->company ?: '—' }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($customer->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Registered</dt>
                    <dd class="col-sm-8">{{ $customer->created_at?->format('Y-m-d H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">Orders</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Placed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td class="text-end">{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                                <td><span class="badge bg-{{ $statusBadge[$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span></td>
                                <td>{{ $order->placed_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
