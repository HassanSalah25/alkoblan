@extends('admin.layouts.app')

@section('title', 'Hero Slides')
@section('page-title', 'Hero Slides')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title/tag...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('homepage.create')
        <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Slide</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Tag</th>
                    <th>Title</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($heroSlides as $slide)
                    <tr>
                        <td>
                            @if ($slide->imageDesktop)
                                <img src="{{ $slide->imageDesktop->url }}" class="rounded" style="width:60px;height:40px;object-fit:cover;">
                            @endif
                        </td>
                        <td>{{ $slide->tag }}</td>
                        <td>{{ $slide->title }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.hero-slides.order', $slide) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="number" name="sort_order" value="{{ $slide->sort_order }}" min="0" class="form-control form-control-sm" style="width:75px;" onchange="this.form.submit()">
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.hero-slides.toggle', $slide) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $slide->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $slide->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('homepage.update')
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('homepage.delete')
                            <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" class="d-inline" onsubmit="return confirm('Delete this slide?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hero slides found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $heroSlides->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
