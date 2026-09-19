@extends('admin.layouts.app')

@section('title', $menu->exists ? 'Edit Menu Item' : 'New Menu Item')
@section('page-title', $menu->exists ? 'Edit Menu Item' : 'New Menu Item')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $menu->exists ? route('admin.menus.update', $menu) : route('admin.menus.store') }}">
            @csrf
            @if ($menu->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location *</label>
                    <select name="location" class="form-select" required>
                        @foreach ($locations as $key => $label)
                            <option value="{{ $key }}" {{ (string) old('location', $menu->location) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Item</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— none (top level) —</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" {{ (string) old('parent_id', $menu->parent_id) === (string) $parent->id ? 'selected' : '' }}>
                                {{ $locations[$parent->location] ?? $parent->location }} / {{ $parent->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $menu->title) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $menu->title_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">URL</label>
                    <input type="text" name="url" class="form-control" value="{{ old('url', $menu->url) }}" placeholder="/about-us or https://...">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Icon</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $menu->icon) }}" placeholder="bi bi-house">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $menu->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="open_new_tab" value="1" class="form-check-input" id="open_new_tab" {{ old('open_new_tab', $menu->open_new_tab ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="open_new_tab">Open in New Tab</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
