@extends('admin.layouts.app')

@section('title', 'Pages')
@section('page-title', 'Pages')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title...">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('pages.create')
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Page</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Sort</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td>
                            <span class="badge {{ $page->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($page->status) }}
                            </span>
                        </td>
                        <td>{{ $page->sort_order }}</td>
                        <td class="text-end">
                            @can('pages.update')
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('pages.delete')
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="d-inline" onsubmit="return confirm('Delete this page?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No pages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $pages->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
