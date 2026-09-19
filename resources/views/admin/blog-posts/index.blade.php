@extends('admin.layouts.app')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title...">
            <select name="blog_category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) request('blog_category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Search</button>
        </form>
        @can('blogs.create')
        <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> New Blog Post</a>
        @endcan
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($blogPosts as $blogPost)
                    <tr>
                        <td>{{ $blogPost->title }}</td>
                        <td>{{ $blogPost->category?->name }}</td>
                        <td>
                            <span class="badge {{ $blogPost->status === 'published' ? 'bg-success' : ($blogPost->status === 'scheduled' ? 'bg-info' : 'bg-secondary') }}">
                                {{ ucfirst($blogPost->status) }}
                            </span>
                        </td>
                        <td>{{ optional($blogPost->published_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @can('blogs.update')
                            <a href="{{ route('admin.blog-posts.edit', $blogPost) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            @endcan
                            @can('blogs.delete')
                            <form method="POST" action="{{ route('admin.blog-posts.destroy', $blogPost) }}" class="d-inline" onsubmit="return confirm('Delete this blog post?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No blog posts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $blogPosts->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
