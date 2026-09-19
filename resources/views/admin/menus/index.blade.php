@extends('admin.layouts.app')

@section('title', 'Menus')
@section('page-title', 'Menus')

@section('content')

<div class="d-flex justify-content-end mb-3">
    @can('menus.create')
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Menu Item</a>
    @endcan
</div>

@foreach ($groups as $key => $group)
    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>{{ $group['label'] }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Icon</th>
                        <th>New Tab</th>
                        <th>Sort</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($group['items'] as $item)
                        <tr>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->url }}</td>
                            <td>{{ $item->icon }}</td>
                            <td>{{ $item->open_new_tab ? 'Yes' : 'No' }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.menus.toggle', $item) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $item->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                @can('menus.update')
                                <a href="{{ route('admin.menus.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('menus.delete')
                                <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this menu item? Its children will also be affected.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @foreach ($item->children as $child)
                            <tr>
                                <td class="ps-4 text-muted"><i class="bi bi-arrow-return-right me-1"></i>{{ $child->title }}</td>
                                <td>{{ $child->url }}</td>
                                <td>{{ $child->icon }}</td>
                                <td>{{ $child->open_new_tab ? 'Yes' : 'No' }}</td>
                                <td>{{ $child->sort_order }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.menus.toggle', $child) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $child->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    @can('menus.update')
                                    <a href="{{ route('admin.menus.edit', $child) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('menus.delete')
                                    <form method="POST" action="{{ route('admin.menus.destroy', $child) }}" class="d-inline" onsubmit="return confirm('Delete this menu item?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No menu items for this location.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@endsection
