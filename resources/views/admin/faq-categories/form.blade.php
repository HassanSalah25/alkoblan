@extends('admin.layouts.app')

@section('title', $faqCategory->exists ? 'Edit FAQ Category' : 'New FAQ Category')
@section('page-title', $faqCategory->exists ? 'Edit FAQ Category' : 'New FAQ Category')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $faqCategory->exists ? route('admin.faq-categories.update', $faqCategory) : route('admin.faq-categories.store') }}">
            @csrf
            @if ($faqCategory->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $faqCategory->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $faqCategory->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $faqCategory->slug) }}" placeholder="Leave blank to auto-generate">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $faqCategory->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $faqCategory->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.faq-categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
