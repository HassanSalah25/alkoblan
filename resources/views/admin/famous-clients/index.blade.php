@extends('admin.layouts.app')

@section('title', 'Famous Clients')
@section('page-title', 'Famous Clients')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('homepage.create')
        <a href="{{ route('admin.famous-clients.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Client</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($famousClients as $client)
                    <tr>
                        <td>
                            @if ($client->logo)
                                <img src="{{ $client->logo->url }}" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                            @endif
                        </td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->url }}</td>
                        <td>{{ $client->sort_order }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.famous-clients.toggle', $client) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $client->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $client->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('homepage.update')
                            <a href="{{ route('admin.famous-clients.edit', $client) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('homepage.delete')
                            <form method="POST" action="{{ route('admin.famous-clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('Delete this client?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No famous clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $famousClients->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
