<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /** FK columns across the app that may reference a media row, used for a
     *  simple "is this still in use" check before hard-deleting a file. */
    private const REFERENCING_COLUMNS = [
        'pages' => ['featured_image_id'],
        'hero_slides' => ['image_desktop_id', 'image_mobile_id'],
        'content_blocks' => ['image_id'],
        'testimonials' => ['image_id'],
        'famous_clients' => ['logo_id'],
        'product_categories' => ['image_id'],
        'product_images' => ['media_id'],
        'product_files' => ['media_id'],
        'blog_posts' => ['featured_image_id'],
        'events' => ['featured_image_id'],
        'event_images' => ['media_id'],
        'job_applications' => ['cv_media_id'],
    ];

    public function index(Request $request)
    {
        $media = Media::query()
            ->with('category', 'uploader')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('title', 'like', "%{$request->q}%")
                    ->orWhere('original_name', 'like', "%{$request->q}%");
            }))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->where('media_category_id', $request->category))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $categories = MediaCategory::orderBy('name')->get();

        return view('admin.media.index', compact('media', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:20480'],
            'media_category_id' => ['nullable', 'exists:media_categories,id'],
        ]);

        foreach ($request->file('files') as $file) {
            $this->storeUploadedFile($file, $request->input('media_category_id'));
        }

        return redirect()->route('admin.media.index')->with('success', 'File(s) uploaded.');
    }

    public function edit(Media $medium)
    {
        $categories = MediaCategory::orderBy('name')->get();

        return view('admin.media.edit', ['media' => $medium, 'categories' => $categories]);
    }

    public function update(Request $request, Media $medium)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_category_id' => ['nullable', 'exists:media_categories,id'],
        ]);

        $medium->update($data);

        return redirect()->route('admin.media.index')->with('success', 'Media updated.');
    }

    public function replace(Request $request, Media $medium)
    {
        $request->validate(['file' => ['required', 'file', 'max:20480']]);

        $file = $request->file('file');
        $oldDisk = $medium->disk;
        $oldPath = $medium->path;

        $categorySlug = $medium->category?->slug ?: 'general';
        $filename = Str::random(20).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/'.$categorySlug, $filename, 'public');

        $medium->update([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'type' => Media::typeFromMime($file->getMimeType()),
            'size' => $file->getSize(),
        ]);

        if ($oldPath && Storage::disk($oldDisk)->exists($oldPath)) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return redirect()->route('admin.media.index')->with('success', 'File replaced (references elsewhere remain intact).');
    }

    public function destroy(Media $medium)
    {
        $usage = $this->findUsage($medium->id);

        if ($usage) {
            return back()->with('error', "Cannot delete: this media file is still referenced by {$usage}.");
        }

        if ($medium->path && Storage::disk($medium->disk)->exists($medium->path)) {
            Storage::disk($medium->disk)->delete($medium->path);
        }

        $medium->delete();

        return redirect()->route('admin.media.index')->with('success', 'Media deleted.');
    }

    /** JSON feed of all media, used by the reusable media-picker modal. */
    public function pickerData()
    {
        $items = Media::query()
            ->latest()
            ->get(['id', 'title', 'original_name', 'type', 'path', 'disk'])
            ->map(fn (Media $m) => [
                'id' => $m->id,
                'title' => $m->title,
                'original_name' => $m->original_name,
                'type' => $m->type,
                'url' => $m->url,
            ]);

        return response()->json(['items' => $items]);
    }

    /** Inline upload used from within the media-picker modal on any form. */
    public function quickUpload(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'max:20480']]);

        $media = $this->storeUploadedFile($request->file('file'), null, $request->input('title'));

        return response()->json([
            'item' => [
                'id' => $media->id,
                'title' => $media->title,
                'original_name' => $media->original_name,
                'type' => $media->type,
                'url' => $media->url,
            ],
        ]);
    }

    private function storeUploadedFile($file, ?int $categoryId, ?string $title = null): Media
    {
        $category = $categoryId ? MediaCategory::find($categoryId) : null;
        $categorySlug = $category?->slug ?: 'general';

        $filename = Str::random(20).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media/'.$categorySlug, $filename, 'public');
        $mime = $file->getMimeType();

        return Media::create([
            'media_category_id' => $categoryId,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'type' => Media::typeFromMime($mime),
            'size' => $file->getSize(),
            'title' => $title ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'visibility' => 'public',
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function findUsage(int $mediaId): ?string
    {
        foreach (self::REFERENCING_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $exists = DB::table($table)->where($column, $mediaId)->exists();
                if ($exists) {
                    return "\"{$table}\" ({$column})";
                }
            }
        }

        return null;
    }
}
