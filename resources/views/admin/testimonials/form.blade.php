@extends('admin.layouts.app')

@section('title', $testimonial->exists ? 'Edit Testimonial' : 'New Testimonial')
@section('page-title', $testimonial->exists ? 'Edit Testimonial' : 'New Testimonial')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
            @csrf
            @if ($testimonial->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $testimonial->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $testimonial->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $testimonial->position) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Position (Arabic)</label>
                    <input type="text" name="position_ar" class="form-control" value="{{ old('position_ar', $testimonial->position_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company', $testimonial->company) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Company (Arabic)</label>
                    <input type="text" name="company_ar" class="form-control" value="{{ old('company_ar', $testimonial->company_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="4" required>{{ old('content', $testimonial->content) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content (Arabic)</label>
                    <textarea name="content_ar" class="form-control" rows="4">{{ old('content_ar', $testimonial->content_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="image_id" :value="$testimonial->image_id" :url="$testimonial->image?->url" type="image" label="Photo" />

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" class="form-control" value="{{ old('rating', $testimonial->rating ?? 5) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $testimonial->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
