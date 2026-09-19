@extends('admin.layouts.app')

@section('title', 'Edit Content Block')
@section('page-title', 'Edit Content Block: ' . $contentBlock->key)

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.content-blocks.update', $contentBlock) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Key</label>
                <input type="text" class="form-control" value="{{ $contentBlock->key }}" disabled>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $contentBlock->title) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $contentBlock->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $contentBlock->subtitle) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <input type="text" name="subtitle_ar" class="form-control" value="{{ old('subtitle_ar', $contentBlock->subtitle_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="6">{{ old('content', $contentBlock->content) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content (Arabic)</label>
                    <textarea name="content_ar" class="form-control" rows="6">{{ old('content_ar', $contentBlock->content_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="image_id" :value="$contentBlock->image_id" :url="$contentBlock->image?->url" type="image" label="Image" />

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $contentBlock->button_text) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button Text (Arabic)</label>
                    <input type="text" name="button_text_ar" class="form-control" value="{{ old('button_text_ar', $contentBlock->button_text_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Button URL</label>
                    <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $contentBlock->button_url) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Extra (JSON)</label>
                <textarea name="extra" class="form-control" rows="6" spellcheck="false">{{ old('extra', json_encode($contentBlock->extra, JSON_PRETTY_PRINT)) }}</textarea>
                <div class="form-text">Must be valid JSON. If left blank it will be cleared; if invalid, the previous value is kept.</div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $contentBlock->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.content-blocks.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
