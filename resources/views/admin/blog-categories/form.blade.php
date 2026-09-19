@extends('admin.layouts.app')

@section('title', $blogCategory->exists ? 'Edit Blog Category' : 'New Blog Category')
@section('page-title', $blogCategory->exists ? 'Edit Blog Category' : 'New Blog Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $blogCategory->exists ? route('admin.blog-categories.update', $blogCategory) : route('admin.blog-categories.store') }}">
            @csrf
            @if ($blogCategory->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $blogCategory->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $blogCategory->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $blogCategory->slug) }}" placeholder="Leave blank to auto-generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $blogCategory->sort_order ?? 0) }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
