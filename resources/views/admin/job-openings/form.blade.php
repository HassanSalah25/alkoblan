@extends('admin.layouts.app')

@section('title', $jobOpening->exists ? 'Edit Job Opening' : 'New Job Opening')
@section('page-title', $jobOpening->exists ? 'Edit Job Opening' : 'New Job Opening')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $jobOpening->exists ? route('admin.job-openings.update', $jobOpening) : route('admin.job-openings.store') }}">
            @csrf
            @if ($jobOpening->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $jobOpening->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $jobOpening->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $jobOpening->slug) }}" placeholder="Auto-generated from title if left blank">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $jobOpening->department) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $jobOpening->location) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Employment Type *</label>
                    <select name="employment_type" class="form-select" required>
                        @php $types = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship']; @endphp
                        @foreach ($types as $value => $labelText)
                            <option value="{{ $value }}" {{ old('employment_type', $jobOpening->employment_type) === $value ? 'selected' : '' }}>{{ $labelText }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="open" {{ old('status', $jobOpening->status) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ old('status', $jobOpening->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control" value="{{ old('deadline', optional($jobOpening->deadline)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="6">{{ old('description', $jobOpening->description) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-control" rows="6">{{ old('description_ar', $jobOpening->description_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Requirements</label>
                    <textarea name="requirements" class="form-control" rows="6">{{ old('requirements', $jobOpening->requirements) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Requirements (Arabic)</label>
                    <textarea name="requirements_ar" class="form-control" rows="6">{{ old('requirements_ar', $jobOpening->requirements_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Benefits</label>
                    <textarea name="benefits" class="form-control" rows="6">{{ old('benefits', $jobOpening->benefits) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Benefits (Arabic)</label>
                    <textarea name="benefits_ar" class="form-control" rows="6">{{ old('benefits_ar', $jobOpening->benefits_ar) }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.job-openings.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
