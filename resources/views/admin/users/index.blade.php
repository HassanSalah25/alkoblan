@extends('admin.layouts.app')

@section('title', 'Admin Users')
@section('page-title', 'Users')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/email...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('users.create')
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Admin User</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Roles</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td>{{ $u->name }} @if ($u->id === auth()->id()) <span class="badge bg-info">You</span> @endif</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone }}</td>
                        <td>
                            @foreach ($u->roles as $role)
                                <span class="badge bg-secondary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>{!! $u->status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                        <td class="text-end">
                            @can('users.update')
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('users.delete')
                            @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Delete this admin user?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No admin users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
