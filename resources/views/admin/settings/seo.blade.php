@extends('admin.layouts.app')

@section('title', 'SEO Settings')
@section('page-title', 'Settings')

@section('content')
@include('admin.settings._tabs')

<form method="POST" action="{{ route('admin.settings.seo.update') }}">
    @csrf
    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Default SEO</div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Default SEO Title</label>
                <input type="text" name="seo_default_title" class="form-control" value="{{ old('seo_default_title', $values['seo_default_title']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Default SEO Title (Arabic)</label>
                <input type="text" name="seo_default_title_ar" class="form-control" value="{{ old('seo_default_title_ar', $values['seo_default_title_ar']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Default SEO Description</label>
                <textarea name="seo_default_description" class="form-control" rows="3">{{ old('seo_default_description', $values['seo_default_description']) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Default SEO Description (Arabic)</label>
                <textarea name="seo_default_description_ar" class="form-control" rows="3">{{ old('seo_default_description_ar', $values['seo_default_description_ar']) }}</textarea>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Default SEO Keywords</label>
                <input type="text" name="seo_default_keywords" class="form-control" value="{{ old('seo_default_keywords', $values['seo_default_keywords']) }}">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Analytics</div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" class="form-control" value="{{ old('google_analytics_id', $values['google_analytics_id']) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Google Tag Manager ID</label>
                <input type="text" name="google_tag_manager_id" class="form-control" value="{{ old('google_tag_manager_id', $values['google_tag_manager_id']) }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
@endsection
