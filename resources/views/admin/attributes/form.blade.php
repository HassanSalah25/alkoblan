@extends('admin.layouts.app')

@section('title', $attribute->exists ? 'Edit Attribute' : 'New Attribute')
@section('page-title', $attribute->exists ? 'Edit Attribute' : 'New Attribute')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $attribute->exists ? route('admin.attributes.update', $attribute) : route('admin.attributes.store') }}">
            @csrf
            @if ($attribute->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $attribute->name) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Name (Arabic)</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $attribute->name_ar) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-select" required>
                        @foreach (['select' => 'Select', 'color' => 'Color', 'text' => 'Text'] as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $attribute->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>

@if ($attribute->exists)
<div class="card mt-4">
    <div class="card-header bg-white">Values</div>
    @foreach ($attribute->values as $value)
        <form method="POST" action="{{ route('admin.attribute-values.update', $value) }}" id="value-form-{{ $value->id }}">
            @csrf
            @method('PUT')
        </form>
    @endforeach
    <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>Value</th>
                    <th>Value (Arabic)</th>
                    <th style="width:110px;">Sort</th>
                    <th class="text-end" style="width:160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attribute->values as $value)
                    <tr>
                        <td><input type="text" name="value" form="value-form-{{ $value->id }}" class="form-control form-control-sm" value="{{ $value->value }}" required></td>
                        <td><input type="text" name="value_ar" form="value-form-{{ $value->id }}" class="form-control form-control-sm" value="{{ $value->value_ar }}"></td>
                        <td><input type="number" name="sort_order" form="value-form-{{ $value->id }}" class="form-control form-control-sm" value="{{ $value->sort_order }}"></td>
                        <td class="text-end">
                            <button type="submit" form="value-form-{{ $value->id }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-lg"></i> Save</button>
                            <form method="POST" action="{{ route('admin.attribute-values.destroy', $value) }}" class="d-inline" onsubmit="return confirm('Delete this value?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No values yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <form method="POST" action="{{ route('admin.attribute-values.store', $attribute) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small mb-1">Value</label>
                <input type="text" name="value" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Value (Arabic)</label>
                <input type="text" name="value_ar" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Sort Order</label>
                <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-plus"></i> Add Value</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
