@extends('admin.layouts.app')

@section('title', 'FAQ Categories')
@section('page-title', 'FAQ Categories')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name...">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary btn-sm">View FAQs</a>
            @can('faqs.create')
            <a href="{{ route('admin.faq-categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Category</a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Name (Arabic)</th>
                    <th>Slug</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faqCategories as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>{{ $cat->name_ar }}</td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>{{ $cat->sort_order }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.faq-categories.toggle', $cat) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $cat->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('faqs.update')
                            <a href="{{ route('admin.faq-categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('faqs.delete')
                            <form method="POST" action="{{ route('admin.faq-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this FAQ category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No FAQ categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $faqCategories->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
