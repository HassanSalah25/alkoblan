@extends('admin.layouts.app')

@section('title', 'FAQs')
@section('page-title', 'FAQs')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search question...">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach ($faqCategories as $cat)
                    <option value="{{ $cat->id }}" {{ (string) request('category') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.faq-categories.index') }}" class="btn btn-outline-secondary btn-sm">Manage Categories</a>
            @can('faqs.create')
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New FAQ</a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Sort</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faqs as $faq)
                    <tr>
                        <td>{{ $faq->question }}</td>
                        <td>{{ $faq->category?->name ?? '—' }}</td>
                        <td>{{ $faq->sort_order }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.faqs.toggle', $faq) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm {{ $faq->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                    {{ $faq->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            @can('faqs.update')
                            <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('faqs.delete')
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="d-inline" onsubmit="return confirm('Delete this FAQ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No FAQs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $faqs->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
