@extends('admin.layouts.app')

@section('title', 'Product Categories')
@section('page-title', 'Product Categories')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('categories.create')
        <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Category</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Sort</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            @if ($category->image)
                                <img src="{{ $category->image->url }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                            @endif
                        </td>
                        <td>
                            @if ($category->parent_id)
                                <span class="text-muted ps-4">&mdash; {{ $category->name }}</span>
                            @else
                                <strong>{{ $category->name }}</strong>
                            @endif
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            @if ($category->is_featured)
                                <span class="badge bg-primary">Featured</span>
                            @else
                                <span class="badge bg-light text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            @if ($category->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @can('categories.update')
                            <a href="{{ route('admin.product-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('categories.delete')
                            <form method="POST" action="{{ route('admin.product-categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $categories->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
