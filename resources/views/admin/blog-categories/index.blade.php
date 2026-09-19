@extends('admin.layouts.app')

@section('title', 'Blog Categories')
@section('page-title', 'Blog Categories')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-tags.index') }}" class="btn btn-outline-secondary btn-sm">Manage Tags</a>
            @can('blogs.create')
            <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Category</a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Name (Arabic)</th>
                    <th>Slug</th>
                    <th>Sort</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blogCategories as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>{{ $cat->name_ar }}</td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>{{ $cat->sort_order }}</td>
                        <td class="text-end">
                            @can('blogs.update')
                            <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('blogs.delete')
                            <form method="POST" action="{{ route('admin.blog-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this blog category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No blog categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $blogCategories->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
