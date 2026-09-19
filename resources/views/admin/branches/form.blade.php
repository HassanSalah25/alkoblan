@extends('admin.layouts.app')

@section('title', $branch->exists ? 'Edit Branch' : 'New Branch')
@section('page-title', $branch->exists ? 'Edit Branch' : 'New Branch')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
            @csrf
            @if ($branch->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $branch->name_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $branch->city) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">City (Arabic)</label>
                    <input type="text" name="city_ar" class="form-control" value="{{ old('city_ar', $branch->city_ar) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address', $branch->address) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address (Arabic)</label>
                    <textarea name="address_ar" class="form-control" rows="3">{{ old('address_ar', $branch->address_ar) }}</textarea>
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Contact</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Secondary Phone</label>
                    <input type="text" name="phone_secondary" class="form-control" value="{{ old('phone_secondary', $branch->phone_secondary) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" value="{{ old('email', $branch->email) }}">
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Map</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.0000001" name="latitude" class="form-control" value="{{ old('latitude', $branch->latitude) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.0000001" name="longitude" class="form-control" value="{{ old('longitude', $branch->longitude) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Maps URL</label>
                    <input type="text" name="maps_url" class="form-control" value="{{ old('maps_url', $branch->maps_url) }}">
                </div>
            </div>

            <hr>
            <h6 class="mb-3">Working Hours</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Working Hours</label>
                    <textarea name="working_hours" class="form-control" rows="3">{{ old('working_hours', $branch->working_hours) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Working Hours (Arabic)</label>
                    <textarea name="working_hours_ar" class="form-control" rows="3">{{ old('working_hours_ar', $branch->working_hours_ar) }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $branch->sort_order ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.branches.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
