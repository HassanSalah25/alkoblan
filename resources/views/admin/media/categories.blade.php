@extends('admin.layouts.app')

@section('title', 'Media Categories')
@section('page-title', 'Media Categories')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Media</a>
        @can('media.create')
        <a href="{{ route('admin.media-categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Category</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Name</th><th>Name (AR)</th><th>Slug</th><th>Files</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($mediaCategories as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->name_ar }}</td>
                        <td><code>{{ $c->slug }}</code></td>
                        <td>{{ $c->media_count }}</td>
                        <td class="text-end">
                            @can('media.update')
                            <a href="{{ route('admin.media-categories.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('media.delete')
                            <form method="POST" action="{{ route('admin.media-categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No categories.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $mediaCategories->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
