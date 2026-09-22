@extends('admin.layouts.app')

@section('title', $page->exists ? 'Edit Page' : 'New Page')
@section('page-title', $page->exists ? 'Edit Page' : 'New Page')

@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
    @csrf
    @if ($page->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Page Details</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $page->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $page->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $page->slug) }}" placeholder="Auto-generated from title if left blank">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $page->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $page->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $page->sort_order ?? 0) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Leave blank — if status is Published this will default to now.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt', $page->excerpt) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Excerpt (Arabic)</label>
                    <textarea name="excerpt_ar" class="form-control" rows="3">{{ old('excerpt_ar', $page->excerpt_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control rich-editor" rows="12">{{ old('content', $page->content) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content (Arabic)</label>
                    <textarea name="content_ar" class="form-control rich-editor" rows="12">{{ old('content_ar', $page->content_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="featured_image_id" :value="$page->featured_image_id" :url="$page->featuredImage?->url" type="image" label="Featured Image" />
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>SEO</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $page->seo_title) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Title (Arabic)</label>
                    <input type="text" name="seo_title_ar" class="form-control" value="{{ old('seo_title_ar', $page->seo_title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $page->seo_description) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Description (Arabic)</label>
                    <textarea name="seo_description_ar" class="form-control" rows="3">{{ old('seo_description_ar', $page->seo_description_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Keywords</label>
                    <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $page->seo_keywords) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Canonical URL</label>
                    <input type="text" name="canonical_url" class="form-control" value="{{ old('canonical_url', $page->canonical_url) }}">
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>

@push('scripts')
    @include('admin.partials.rich-editor')
@endpush
@endsection
