@extends('admin.layouts.app')

@section('title', 'Attributes')
@section('page-title', 'Attributes')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('attributes.create')
        <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Attribute</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Name (Arabic)</th>
                    <th>Type</th>
                    <th>Values</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attributes as $attribute)
                    <tr>
                        <td>{{ $attribute->name }}</td>
                        <td>{{ $attribute->name_ar }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $attribute->type }}</span></td>
                        <td>{{ $attribute->values_count }}</td>
                        <td class="text-end">
                            @can('attributes.update')
                            <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('attributes.delete')
                            <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" class="d-inline" onsubmit="return confirm('Delete this attribute?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No attributes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $attributes->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
