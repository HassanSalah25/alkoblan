@extends('admin.layouts.app')

@section('title', 'Content Blocks')
@section('page-title', 'Content Blocks')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <strong>Homepage Content Blocks</strong>
        <div class="text-muted small">These are fixed sections seeded for the homepage; they cannot be created or removed, only edited.</div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Title</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contentBlocks as $contentBlock)
                    <tr>
                        <td><code>{{ $contentBlock->key }}</code></td>
                        <td>{{ $contentBlock->title }}</td>
                        <td>
                            <span class="badge {{ $contentBlock->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $contentBlock->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @can('homepage.update')
                            <a href="{{ route('admin.content-blocks.edit', $contentBlock) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No content blocks found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
