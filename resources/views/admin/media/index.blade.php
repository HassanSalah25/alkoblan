@extends('admin.layouts.app')

@section('title', 'Media Library')
@section('page-title', 'Media Library')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title/filename..." style="width:220px">
        <select name="type" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach (['image','pdf','document','video','other'] as $t)
                <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="category" class="form-select form-select-sm" style="width:180px" onchange="this.form.submit()">
            <option value="">All categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
    <div>
        <a href="{{ route('admin.media-categories.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-folder"></i> Categories</a>
        @can('media.create')
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> Upload</button>
        @endcan
    </div>
</div>

<div class="row g-3">
    @forelse ($media as $item)
        <div class="col-6 col-md-3 col-lg-2">
            <div class="card h-100">
                @if ($item->type === 'image')
                    <img src="{{ $item->url }}" class="media-thumb card-img-top">
                @else
                    <div class="media-thumb d-flex align-items-center justify-content-center">
                        <i class="bi bi-file-earmark-{{ $item->type === 'pdf' ? 'pdf' : ($item->type === 'video' ? 'play' : 'text') }} fs-1 text-secondary"></i>
                    </div>
                @endif
                <div class="card-body p-2">
                    <div class="small text-truncate fw-semibold" title="{{ $item->title }}">{{ $item->title ?: $item->original_name }}</div>
                    <div class="small text-muted">{{ $item->type }} · {{ number_format($item->size / 1024, 1) }} KB</div>
                    <div class="small text-muted">{{ $item->category?->name ?? '—' }}</div>
                    <div class="small text-muted">by {{ $item->uploader?->name ?? '—' }} · {{ $item->download_count }} downloads</div>
                    <div class="d-flex gap-1 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $item->url }}')" title="Copy URL"><i class="bi bi-link-45deg"></i></button>
                        @can('media.update')
                        <a href="{{ route('admin.media.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endcan
                        @can('media.delete')
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete this media file permanently?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">No media files found.</div>
    @endforelse
</div>

<div class="mt-3">{{ $media->links('pagination::bootstrap-5') }}</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Media</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File(s)</label>
                        <input type="file" name="files[]" class="form-control" multiple required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="media_category_id" class="form-select">
                            <option value="">— General —</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
