@extends('admin.layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-start flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/SKU..." style="width:200px">
            <select name="category_id" class="form-select form-select-sm" style="width:180px" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="featured" class="form-select form-select-sm" style="width:130px" onchange="this.form.submit()">
                <option value="">Featured?</option>
                <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
            </select>
            <select name="active" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                <option value="">Active?</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </form>
        @can('products.create')
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Product</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td><code>{{ $p->sku }}</code></td>
                        <td>{{ $p->category?->name }}</td>
                        <td>
                            @if ($p->sale_price)
                                <span class="text-decoration-line-through text-muted small">{{ number_format($p->price, 2) }}</span>
                                <span class="text-danger fw-semibold">{{ number_format($p->sale_price, 2) }}</span>
                            @else
                                {{ number_format($p->price, 2) }}
                            @endif
                            {{ $p->currency }}
                        </td>
                        <td>{{ $p->stock_quantity }} <span class="badge bg-light text-dark">{{ $p->stock_status }}</span></td>
                        <td>{!! $p->is_featured ? '<span class="badge bg-info">Yes</span>' : '' !!}</td>
                        <td>{!! $p->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-end">
                            @can('products.update')
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('products.delete')
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $products->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
