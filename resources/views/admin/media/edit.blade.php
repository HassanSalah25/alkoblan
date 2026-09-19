@extends('admin.layouts.app')

@section('title', 'Edit Media')
@section('page-title', 'Edit Media')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.media.update', $media) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $media->title) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title (Arabic)</label>
                        <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $media->title_ar) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alt Text</label>
                        <input type="text" name="alt_text" class="form-control" value="{{ old('alt_text', $media->alt_text) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $media->description) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="media_category_id" class="form-select">
                            <option value="">— General —</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" {{ $media->media_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                @if ($media->type === 'image')
                    <img src="{{ $media->url }}" class="img-fluid rounded mb-2">
                @else
                    <i class="bi bi-file-earmark fs-1 text-secondary"></i>
                @endif
                <div class="small text-muted">{{ $media->original_name }}</div>
                <div class="small text-muted">{{ number_format($media->size / 1024, 1) }} KB · {{ $media->mime_type }}</div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="navigator.clipboard.writeText('{{ $media->url }}')">Copy URL</button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6>Replace File</h6>
                <p class="small text-muted">Keeps the same Media record (ID {{ $media->id }}) so existing references elsewhere are not broken.</p>
                <form method="POST" action="{{ route('admin.media.replace', $media) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" class="form-control mb-2" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Replace</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
