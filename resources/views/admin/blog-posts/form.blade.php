@extends('admin.layouts.app')

@section('title', $blogPost->exists ? 'Edit Blog Post' : 'New Blog Post')
@section('page-title', $blogPost->exists ? 'Edit Blog Post' : 'New Blog Post')

@section('content')
<form method="POST" action="{{ $blogPost->exists ? route('admin.blog-posts.update', $blogPost) : route('admin.blog-posts.store') }}">
    @csrf
    @if ($blogPost->exists) @method('PUT') @endif

    {{-- ── Post Settings (single-column) ─────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Post Settings</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="blog_category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('blog_category_id', $blogPost->blog_category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="draft"     {{ old('status', $blogPost->status) === 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $blogPost->status) === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ old('status', $blogPost->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($blogPost->published_at)->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Defaults to now when status is Published.</div>
                </div>
                @if ($blogPost->exists)
                <div class="col-md-2">
                    <label class="form-label">Views</label>
                    <input type="text" class="form-control" value="{{ $blogPost->views_count }}" disabled>
                </div>
                @endif
                <div class="col-12">
                    <label class="form-label d-block">Tags</label>
                    @forelse ($tags as $tag)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-check-input" id="tag-{{ $tag->id }}"
                                {{ in_array($tag->id, old('tags', $blogPost->exists ? $blogPost->tags->pluck('id')->all() : [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                        </div>
                    @empty
                        <div class="text-muted small">No tags available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Post Content — 2-column EN | AR ────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header bg-white d-flex align-items-center gap-2">
            <strong>Post Details</strong>
            <small class="text-muted ms-1">— at least one language required</small>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">

                {{-- English column --}}
                <div class="col-md-6 p-4 border-end">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <span class="badge bg-primary">EN</span>
                        <span class="fw-semibold text-muted small text-uppercase">English</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $blogPost->title) }}" placeholder="English title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $blogPost->slug) }}" placeholder="Auto-generated from title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="3" placeholder="Short summary in English">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control rich-editor" rows="14">{{ old('content', $blogPost->content) }}</textarea>
                    </div>
                </div>

                {{-- Arabic column --}}
                <div class="col-md-6 p-4" dir="rtl">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <span class="badge bg-success">AR</span>
                        <span class="fw-semibold text-muted small text-uppercase">عربي</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="title_ar" class="form-control" value="{{ old('title_ar', $blogPost->title_ar) }}" placeholder="العنوان بالعربية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرابط (Slug)</label>
                        <input type="text" name="slug_ar" class="form-control" value="{{ old('slug_ar', $blogPost->slug_ar) }}" placeholder="يُولَّد تلقائياً من العنوان">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المقتطف</label>
                        <textarea name="excerpt_ar" class="form-control" rows="3" placeholder="ملخص قصير بالعربية">{{ old('excerpt_ar', $blogPost->excerpt_ar) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المحتوى</label>
                        <textarea name="content_ar" class="form-control rich-editor" rows="14">{{ old('content_ar', $blogPost->content_ar) }}</textarea>
                    </div>
                </div>

            </div>{{-- /row --}}
        </div>
    </div>

    {{-- ── Featured Image ───────────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header bg-white"><strong>Featured Image</strong></div>
        <div class="card-body">
            <x-admin.media-picker field="featured_image_id" :value="$blogPost->featured_image_id" :url="$blogPost->featuredImage?->url" type="image" label="Featured Image" />

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label"><span class="badge bg-primary me-1">EN</span> Image Alt Text</label>
                    <input type="text" name="featured_image_alt" class="form-control" value="{{ old('featured_image_alt', $blogPost->featured_image_alt) }}" placeholder="Describe the image in English">
                </div>
                <div class="col-md-6" dir="rtl">
                    <label class="form-label"><span class="badge bg-success ms-1">AR</span> النص البديل للصورة</label>
                    <input type="text" name="featured_image_alt_ar" class="form-control" value="{{ old('featured_image_alt_ar', $blogPost->featured_image_alt_ar) }}" placeholder="وصف الصورة بالعربية">
                </div>
            </div>
        </div>
    </div>

    {{-- ── SEO — 2-column EN | AR ───────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header bg-white"><strong>SEO</strong></div>
        <div class="card-body p-0">
            <div class="row g-0">

                {{-- English SEO --}}
                <div class="col-md-6 p-4 border-end">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <span class="badge bg-primary">EN</span>
                        <span class="fw-semibold text-muted small text-uppercase">English</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SEO Title</label>
                        <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $blogPost->seo_title) }}" placeholder="Page title for search engines">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SEO Keywords</label>
                        <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $blogPost->seo_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SEO Description</label>
                        <textarea name="seo_description" class="form-control" rows="3" placeholder="Meta description (150–160 chars recommended)">{{ old('seo_description', $blogPost->seo_description) }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control" value="{{ old('canonical_url', $blogPost->canonical_url) }}" placeholder="https://alkoblan.com/blog/article-slug">
                        <div class="form-text">Leave blank to use the current page URL automatically.</div>
                    </div>
                </div>

                {{-- Arabic SEO --}}
                <div class="col-md-6 p-4" dir="rtl">
                    <div class="d-flex align-items-center mb-3 gap-2">
                        <span class="badge bg-success">AR</span>
                        <span class="fw-semibold text-muted small text-uppercase">عربي</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عنوان SEO</label>
                        <input type="text" name="seo_title_ar" class="form-control" value="{{ old('seo_title_ar', $blogPost->seo_title_ar) }}" placeholder="عنوان الصفحة لمحركات البحث">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكلمات المفتاحية</label>
                        <input type="text" name="seo_keywords_ar" class="form-control" value="{{ old('seo_keywords_ar', $blogPost->seo_keywords_ar) }}" placeholder="كلمة1، كلمة2، كلمة3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">وصف SEO</label>
                        <textarea name="seo_description_ar" class="form-control" rows="3" placeholder="وصف الميتا (يُنصح بـ 150–160 حرفاً)">{{ old('seo_description_ar', $blogPost->seo_description_ar) }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">الرابط الكانونيكال</label>
                        <input type="url" name="canonical_url_ar" class="form-control" value="{{ old('canonical_url_ar', $blogPost->canonical_url_ar) }}" placeholder="https://alkoblan.com/ar/blog/عنوان-المقال">
                        <div class="form-text">اتركه فارغاً لاستخدام رابط الصفحة الحالي تلقائياً.</div>
                    </div>
                </div>

            </div>{{-- /row --}}
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary">Cancel</a>
</form>

@push('scripts')
    @include('admin.partials.rich-editor')
@endpush
@endsection
