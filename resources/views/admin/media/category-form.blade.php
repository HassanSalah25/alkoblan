@extends('admin.layouts.app')

@section('title', $mediaCategory->exists ? 'Edit Category' : 'New Category')
@section('page-title', $mediaCategory->exists ? 'Edit Category' : 'New Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $mediaCategory->exists ? route('admin.media-categories.update', $mediaCategory) : route('admin.media-categories.store') }}">
            @csrf
            @if ($mediaCategory->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $mediaCategory->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Name (Arabic)</label>
                <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $mediaCategory->name_ar) }}">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.media-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
