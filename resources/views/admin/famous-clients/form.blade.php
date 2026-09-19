@extends('admin.layouts.app')

@section('title', $famousClient->exists ? 'Edit Famous Client' : 'New Famous Client')
@section('page-title', $famousClient->exists ? 'Edit Famous Client' : 'New Famous Client')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $famousClient->exists ? route('admin.famous-clients.update', $famousClient) : route('admin.famous-clients.store') }}">
            @csrf
            @if ($famousClient->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $famousClient->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $famousClient->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">URL</label>
                    <input type="text" name="url" class="form-control" value="{{ old('url', $famousClient->url) }}" placeholder="https://example.com">
                </div>
            </div>

            <x-admin.media-picker field="logo_id" :value="$famousClient->logo_id" :url="$famousClient->logo?->url" type="image" label="Logo" />

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $famousClient->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $famousClient->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.famous-clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
