@extends('admin.layouts.app')

@section('title', 'Events')
@section('page-title', 'Events')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title...">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('events.create')
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Event</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Event Date</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td>{{ $event->title }}</td>
                        <td>{{ optional($event->event_date)->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge {{ $event->is_featured ? 'bg-success' : 'bg-secondary' }}">
                                {{ $event->is_featured ? 'Featured' : 'No' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $event->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('events.update')
                            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('events.delete')
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="d-inline" onsubmit="return confirm('Delete this event?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No events found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $events->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
