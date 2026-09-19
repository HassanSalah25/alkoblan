@extends('admin.layouts.app')

@section('title', $event->exists ? 'Edit Event' : 'New Event')
@section('page-title', $event->exists ? 'Edit Event' : 'New Event')

@section('content')
<form method="POST" action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
    @csrf
    @if ($event->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Event Details</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $event->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $event->slug) }}" placeholder="Auto-generated from title if left blank">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured', $event->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Event Date</label>
                    <input type="datetime-local" name="event_date" class="form-control" value="{{ old('event_date', optional($event->event_date)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date', optional($event->end_date)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location', $event->location) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location (Arabic)</label>
                    <input type="text" name="location_ar" class="form-control" value="{{ old('location_ar', $event->location_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $event->description) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-control" rows="3">{{ old('description_ar', $event->description_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="10">{{ old('content', $event->content) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content (Arabic)</label>
                    <textarea name="content_ar" class="form-control" rows="10">{{ old('content_ar', $event->content_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="featured_image_id" :value="$event->featured_image_id" :url="$event->featuredImage?->url" type="image" label="Featured Image" />
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>

@if ($event->exists)
<div class="card mt-3">
    <div class="card-header bg-white"><strong>Gallery Images</strong></div>
    <div class="card-body">
        <div class="row mb-3">
            @forelse ($event->images as $image)
                <div class="col-md-2 col-4 mb-3 text-center">
                    <img src="{{ $image->media?->url }}" class="media-thumb mb-2" alt="">
                    @can('events.update')
                    <form method="POST" action="{{ route('admin.event-images.destroy', $image) }}" onsubmit="return confirm('Remove this image?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i></button>
                    </form>
                    @endcan
                </div>
            @empty
                <div class="col-12 text-muted">No gallery images yet.</div>
            @endforelse
        </div>

        @can('events.update')
        <form method="POST" action="{{ route('admin.events.images.store', $event) }}">
            @csrf
            <x-admin.media-picker field="new_media_id" type="image" label="Add gallery image" />
            <button type="submit" class="btn btn-outline-primary btn-sm">Add Image</button>
        </form>
        @endcan
    </div>
</div>
@endif
@endsection
