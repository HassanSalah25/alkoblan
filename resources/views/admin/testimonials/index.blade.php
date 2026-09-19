@extends('admin.layouts.app')

@section('title', 'Testimonials')
@section('page-title', 'Testimonials')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/company...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('testimonials.create')
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Testimonial</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Rating</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testimonials as $t)
                    <tr>
                        <td>
                            @if ($t->image)
                                <img src="{{ $t->image->url }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                            @endif
                        </td>
                        <td>{{ $t->name }}</td>
                        <td>{{ $t->company }}</td>
                        <td>{{ str_repeat('★', $t->rating) }}</td>
                        <td>{{ $t->sort_order }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.testimonials.toggle', $t) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $t->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $t->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('testimonials.update')
                            <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('testimonials.delete')
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Delete this testimonial?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No testimonials found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $testimonials->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
