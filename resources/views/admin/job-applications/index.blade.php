@extends('admin.layouts.app')

@section('title', 'Career Applications')
@section('page-title', 'Career Applications')

@php
$statusBadge = [
    'new' => 'primary',
    'reviewed' => 'info',
    'shortlisted' => 'warning',
    'rejected' => 'danger',
    'hired' => 'success',
];
@endphp

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <form method="GET" class="row gy-2 gx-2 align-items-center">
            <div class="col-auto">
                <select name="job_opening_id" class="form-select form-select-sm">
                    <option value="">All Job Openings</option>
                    @foreach ($jobOpenings as $opening)
                        <option value="{{ $opening->id }}" @selected((string) request('job_opening_id') === (string) $opening->id)>{{ $opening->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['new', 'reviewed', 'shortlisted', 'rejected', 'hired'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('admin.job-applications.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Applied At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobApplications as $application)
                    <tr>
                        <td>{{ $application->name }}</td>
                        <td>{{ $application->email }}</td>
                        <td>{{ $application->phone ?: '—' }}</td>
                        <td>{{ $application->job->title ?? '—' }}</td>
                        <td><span class="badge bg-{{ $statusBadge[$application->status] ?? 'secondary' }}">{{ ucfirst($application->status) }}</span></td>
                        <td>{{ $application->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @can('careers.view')
                            <a href="{{ route('admin.job-applications.show', $application) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @endcan
                            @can('careers.delete')
                            <form method="POST" action="{{ route('admin.job-applications.destroy', $application) }}" class="d-inline" onsubmit="return confirm('Delete this application?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $jobApplications->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
