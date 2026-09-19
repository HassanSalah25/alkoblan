@extends('admin.layouts.app')

@section('title', $blogPost->exists ? 'Edit Blog Post' : 'New Blog Post')
@section('page-title', $blogPost->exists ? 'Edit Blog Post' : 'New Blog Post')

@section('content')
<form method="POST" action="{{ $blogPost->exists ? route('admin.blog-posts.update', $blogPost) : route('admin.blog-posts.store') }}">
    @csrf
    @if ($blogPost->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Post Details</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $blogPost->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $blogPost->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $blogPost->slug) }}" placeholder="Auto-generated from title if left blank">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="blog_category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('blog_category_id', $blogPost->blog_category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="draft" {{ old('status', $blogPost->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $blogPost->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ old('status', $blogPost->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($blogPost->published_at)->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Leave blank — if status is Published this will default to now.</div>
                </div>
                @if ($blogPost->exists)
                <div class="col-md-4 mb-3">
                    <label class="form-label">Views Count</label>
                    <input type="text" class="form-control" value="{{ $blogPost->views_count }}" disabled>
                </div>
                @endif
                <div class="col-md-6 mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Excerpt (Arabic)</label>
                    <textarea name="excerpt_ar" class="form-control" rows="3">{{ old('excerpt_ar', $blogPost->excerpt_ar) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="12">{{ old('content', $blogPost->content) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Content (Arabic)</label>
                    <textarea name="content_ar" class="form-control" rows="12">{{ old('content_ar', $blogPost->content_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="featured_image_id" :value="$blogPost->featured_image_id" :url="$blogPost->featuredImage?->url" type="image" label="Featured Image" />

            <div class="mb-3">
                <label class="form-label d-block">Tags</label>
                @forelse ($tags as $tag)
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-check-input" id="tag-{{ $tag->id }}"
                            {{ in_array($tag->id, old('tags', $blogPost->exists ? $blogPost->tags->pluck('id')->all() : [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                    </div>
                @empty
                    <div class="text-muted small">No tags available.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><strong>SEO</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $blogPost->seo_title) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Keywords</label>
                    <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $blogPost->seo_keywords) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" class="form-control" rows="3">{{ old('seo_description', $blogPost->seo_description) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>
@endsection
