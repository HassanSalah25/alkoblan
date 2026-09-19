@extends('admin.layouts.app')

@section('title', $heroSlide->exists ? 'Edit Hero Slide' : 'New Hero Slide')
@section('page-title', $heroSlide->exists ? 'Edit Hero Slide' : 'New Hero Slide')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $heroSlide->exists ? route('admin.hero-slides.update', $heroSlide) : route('admin.hero-slides.store') }}">
            @csrf
            @if ($heroSlide->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tag</label>
                    <input type="text" name="tag" class="form-control" value="{{ old('tag', $heroSlide->tag) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tag (Arabic)</label>
                    <input type="text" name="tag_ar" class="form-control" value="{{ old('tag_ar', $heroSlide->tag_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $heroSlide->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $heroSlide->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea name="subtitle" class="form-control" rows="3">{{ old('subtitle', $heroSlide->subtitle) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle_ar" class="form-control" rows="3">{{ old('subtitle_ar', $heroSlide->subtitle_ar) }}</textarea>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Primary Button</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $heroSlide->button_text) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button Text (Arabic)</label>
                    <input type="text" name="button_text_ar" class="form-control" value="{{ old('button_text_ar', $heroSlide->button_text_ar) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button URL</label>
                    <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $heroSlide->button_url) }}">
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Secondary Button</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button 2 Text</label>
                    <input type="text" name="button2_text" class="form-control" value="{{ old('button2_text', $heroSlide->button2_text) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button 2 Text (Arabic)</label>
                    <input type="text" name="button2_text_ar" class="form-control" value="{{ old('button2_text_ar', $heroSlide->button2_text_ar) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Button 2 URL</label>
                    <input type="text" name="button2_url" class="form-control" value="{{ old('button2_url', $heroSlide->button2_url) }}">
                </div>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-6">
                    <x-admin.media-picker field="image_desktop_id" :value="$heroSlide->image_desktop_id" :url="$heroSlide->imageDesktop?->url" type="image" label="Desktop Image" />
                </div>
                <div class="col-md-6">
                    <x-admin.media-picker field="image_mobile_id" :value="$heroSlide->image_mobile_id" :url="$heroSlide->imageMobile?->url" type="image" label="Mobile Image" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $heroSlide->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $heroSlide->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.hero-slides.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
