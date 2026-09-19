@extends('admin.layouts.app')

@section('title', 'Blog Tags')
@section('page-title', 'Blog Tags')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-outline-secondary btn-sm">Manage Categories</a>
            @can('blogs.create')
            <a href="{{ route('admin.blog-tags.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Tag</a>
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
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blogTags as $tag)
                    <tr>
                        <td>{{ $tag->name }}</td>
                        <td>{{ $tag->name_ar }}</td>
                        <td><code>{{ $tag->slug }}</code></td>
                        <td class="text-end">
                            @can('blogs.update')
                            <a href="{{ route('admin.blog-tags.edit', $tag) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('blogs.delete')
                            <form method="POST" action="{{ route('admin.blog-tags.destroy', $tag) }}" class="d-inline" onsubmit="return confirm('Delete this blog tag?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No blog tags found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $blogTags->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
