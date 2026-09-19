@extends('admin.layouts.app')

@section('title', $faq->exists ? 'Edit FAQ' : 'New FAQ')
@section('page-title', $faq->exists ? 'Edit FAQ' : 'New FAQ')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
            @csrf
            @if ($faq->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="faq_category_id" class="form-select">
                        <option value="">— none —</option>
                        @foreach ($faqCategories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('faq_category_id', $faq->faq_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $faq->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Question *</label>
                    <input type="text" name="question" class="form-control" value="{{ old('question', $faq->question) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Question (Arabic)</label>
                    <input type="text" name="question_ar" class="form-control" value="{{ old('question_ar', $faq->question_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Answer *</label>
                    <textarea name="answer" class="form-control" rows="5" required>{{ old('answer', $faq->answer) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Answer (Arabic)</label>
                    <textarea name="answer_ar" class="form-control" rows="5">{{ old('answer_ar', $faq->answer_ar) }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
