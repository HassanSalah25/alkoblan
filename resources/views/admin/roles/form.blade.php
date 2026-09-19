@extends('admin.layouts.app')

@section('title', $role->exists ? 'Edit Role' : 'New Role')
@section('page-title', $role->exists ? 'Edit Role' : 'New Role')

@section('content')
@php
    $isProtectedSuperAdmin = $role->exists && $role->name === 'Super Admin' && auth()->user()->hasRole('Super Admin');
    $currentPermissions = $role->exists ? $role->permissions->pluck('name')->all() : [];
@endphp

<div class="card">
    <div class="card-body">
        @if ($isProtectedSuperAdmin)
            <div class="alert alert-warning">The Super Admin role's permissions cannot be edited from this screen, to prevent you from locking yourself out.</div>
        @endif

        <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
            @csrf
            @if ($role->exists) @method('PUT') @endif

            <div class="mb-3 col-md-4">
                <label class="form-label">Role Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required {{ $isProtectedSuperAdmin ? 'readonly' : '' }}>
            </div>

            <h6 class="mt-4">Permissions</h6>
            <div class="row">
                @foreach ($permissionGroups as $group => $permissions)
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-semibold small text-uppercase">{{ str_replace('_', ' ', $group) }}</span>
                                @unless ($isProtectedSuperAdmin)
                                <div>
                                    <button type="button" class="btn btn-xs btn-link p-0 small" onclick="setGroup('{{ $group }}', true)">All</button> /
                                    <button type="button" class="btn btn-xs btn-link p-0 small" onclick="setGroup('{{ $group }}', false)">None</button>
                                </div>
                                @endunless
                            </div>
                            <div class="card-body py-2" data-group="{{ $group }}">
                                @foreach ($permissions as $permission)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $permission->name }}"
                                            id="perm-{{ $permission->id }}" {{ in_array($permission->name, $currentPermissions) ? 'checked' : '' }}
                                            {{ $isProtectedSuperAdmin ? 'disabled' : '' }}>
                                        <label class="form-check-label small" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @unless ($isProtectedSuperAdmin)
            <button type="submit" class="btn btn-primary">Save Role</button>
            @endunless
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Back</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function setGroup(group, checked) {
        document.querySelectorAll('[data-group="' + group + '"] input[type=checkbox]').forEach(function (cb) {
            cb.checked = checked;
        });
    }
</script>
@endpush
@endsection
