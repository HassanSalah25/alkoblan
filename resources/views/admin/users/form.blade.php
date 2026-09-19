@extends('admin.layouts.app')

@section('title', $user->exists ? 'Edit Admin User' : 'New Admin User')
@section('page-title', $user->exists ? 'Edit Admin User' : 'New Admin User')

@section('content')
@php $isSelf = $user->exists && $user->id === auth()->id(); @endphp
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password {{ $user->exists ? '(leave blank to keep current)' : '*' }}</label>
                    <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Roles</label>
                @if ($isSelf)
                    <div class="alert alert-info small">You cannot change your own roles (to prevent locking yourself out).</div>
                    @foreach ($user->roles as $role)
                        <span class="badge bg-secondary">{{ $role->name }}</span>
                    @endforeach
                @else
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($roles as $role)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="roles[]" value="{{ $role->name }}" id="role-{{ $role->id }}"
                                    {{ in_array($role->name, old('roles', $user->exists ? $user->roles->pluck('name')->all() : [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
