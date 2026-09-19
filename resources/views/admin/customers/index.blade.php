@extends('admin.layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/email...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Registered</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?: '—' }}</td>
                        <td>{{ $customer->company ?: '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td>{{ $customer->orders_count }}</td>
                        <td>{{ $customer->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end">
                            @can('customers.view')
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @endcan
                            @can('customers.update')
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.customers.toggle', $customer) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $customer->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    {{ $customer->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @endcan
                            @can('customers.delete')
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="d-inline" onsubmit="return confirm('Delete this customer?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $customers->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
