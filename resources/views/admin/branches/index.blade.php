@extends('admin.layouts.app')

@section('title', 'Branches')
@section('page-title', 'Branches')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/city...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('branches.create')
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Branch</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>City</th>
                    <th>Phone</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($branches as $branch)
                    <tr>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->city }}</td>
                        <td>{{ $branch->phone }}</td>
                        <td>{{ $branch->sort_order }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.branches.toggle', $branch) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $branch->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('branches.update')
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('branches.delete')
                            <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="d-inline" onsubmit="return confirm('Delete this branch?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No branches found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $branches->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
