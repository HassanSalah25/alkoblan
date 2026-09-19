@extends('admin.layouts.app')

@section('title', $productCategory->exists ? 'Edit Category' : 'New Category')
@section('page-title', $productCategory->exists ? 'Edit Category' : 'New Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $productCategory->exists ? route('admin.product-categories.update', $productCategory) : route('admin.product-categories.store') }}">
            @csrf
            @if ($productCategory->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Category</label>
                    <select name="parent_id" class="form-select">
                        <option value="">&mdash; None (top level) &mdash;</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ (string) old('parent_id', $productCategory->parent_id) === (string) $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Icon (CSS class)</label>
                    <input type="text" name="icon" class="form-control" placeholder="bi bi-box" value="{{ old('icon', $productCategory->icon) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $productCategory->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $productCategory->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $productCategory->description) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description (Arabic)</label>
                    <textarea name="description_ar" class="form-control" rows="4">{{ old('description_ar', $productCategory->description_ar) }}</textarea>
                </div>
            </div>

            <x-admin.media-picker field="image_id" :value="$productCategory->image_id" :url="$productCategory->image?->url" type="image" label="Image" />

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Title</label>
                    <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $productCategory->seo_title) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SEO Description</label>
                    <textarea name="seo_description" class="form-control" rows="2">{{ old('seo_description', $productCategory->seo_description) }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $productCategory->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured', $productCategory->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $productCategory->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
