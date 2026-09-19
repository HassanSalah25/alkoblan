@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Roles</span>
        @can('roles.create')
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Role</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Name</th><th>Permissions</th><th>Users</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>{{ $role->name }} @if ($role->name === 'Super Admin') <span class="badge bg-warning text-dark">Protected</span> @endif</td>
                        <td>{{ $role->permissions_count }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td class="text-end">
                            @can('roles.update')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('roles.delete')
                            @if ($role->name !== 'Super Admin')
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $roles->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
