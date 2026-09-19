@extends('admin.layouts.app')

@section('title', 'Job Openings')
@section('page-title', 'Job Openings')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title...">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('careers.create')
        <a href="{{ route('admin.job-openings.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Job Opening</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobOpenings as $jobOpening)
                    <tr>
                        <td>{{ $jobOpening->title }}</td>
                        <td>{{ $jobOpening->department }}</td>
                        <td>{{ $jobOpening->location }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $jobOpening->employment_type)) }}</td>
                        <td>
                            <span class="badge {{ $jobOpening->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($jobOpening->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.job-applications.index', ['job_opening_id' => $jobOpening->id]) }}" class="badge bg-light text-dark text-decoration-none border">
                                {{ $jobOpening->applications()->count() }}
                            </a>
                        </td>
                        <td class="text-end">
                            @can('careers.update')
                            <a href="{{ route('admin.job-openings.edit', $jobOpening) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('careers.delete')
                            <form method="POST" action="{{ route('admin.job-openings.destroy', $jobOpening) }}" class="d-inline" onsubmit="return confirm('Delete this job opening?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No job openings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $jobOpenings->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
