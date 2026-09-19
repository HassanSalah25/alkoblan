@extends('admin.layouts.app')

@section('title', 'Edit Customer')
@section('page-title', 'Edit Customer: ' . $customer->name)

@section('content')
<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" value="{{ $customer->email }}" class="form-control" disabled>
                <div class="form-text">Email cannot be changed here.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Company</label>
                <input type="text" value="{{ $customer->company }}" class="form-control" disabled>
                <div class="form-text">Company cannot be changed here.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="active" @selected(old('status', $customer->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Inactive</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
