@extends('admin.layouts.app')

@section('title', $blogTag->exists ? 'Edit Blog Tag' : 'New Blog Tag')
@section('page-title', $blogTag->exists ? 'Edit Blog Tag' : 'New Blog Tag')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $blogTag->exists ? route('admin.blog-tags.update', $blogTag) : route('admin.blog-tags.store') }}">
            @csrf
            @if ($blogTag->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $blogTag->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $blogTag->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $blogTag->slug) }}" placeholder="Leave blank to auto-generate">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.blog-tags.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
