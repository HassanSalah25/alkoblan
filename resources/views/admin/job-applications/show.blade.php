@extends('admin.layouts.app')

@section('title', 'Job Application')
@section('page-title', 'Job Application')

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
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Applicant Details</span>
                <span class="badge bg-{{ $statusBadge[$jobApplication->status] ?? 'secondary' }}">{{ ucfirst($jobApplication->status) }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $jobApplication->name }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $jobApplication->email }}</dd>

                    <dt class="col-sm-3">Phone</dt>
                    <dd class="col-sm-9">{{ $jobApplication->phone ?: '—' }}</dd>

                    <dt class="col-sm-3">Applied For</dt>
                    <dd class="col-sm-9">{{ $jobApplication->job->title ?? '—' }}</dd>

                    <dt class="col-sm-3">Applied At</dt>
                    <dd class="col-sm-9">{{ $jobApplication->created_at?->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-3">Cover Letter</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $jobApplication->cover_letter ?: '—' }}</dd>

                    <dt class="col-sm-3">CV</dt>
                    <dd class="col-sm-9">
                        @if ($jobApplication->cv_media_id && $jobApplication->cv)
                            <a href="{{ $jobApplication->cv->url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download CV</a>
                        @else
                            <span class="text-muted">No CV uploaded</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @can('careers.update')
        <div class="card mb-4">
            <div class="card-header bg-white">Update Status</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.job-applications.update', $jobApplication) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['new', 'reviewed', 'shortlisted', 'rejected', 'hired'] as $s)
                                <option value="{{ $s }}" @selected($jobApplication->status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Status</button>
                </form>
            </div>
        </div>
        @endcan

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.job-applications.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
            @can('careers.delete')
            <form method="POST" action="{{ route('admin.job-applications.destroy', $jobApplication) }}" onsubmit="return confirm('Delete this application?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
