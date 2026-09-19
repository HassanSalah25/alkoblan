<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\MediaCategory;
use Illuminate\Http\Request;

class MediaCategoryController extends Controller
{
    use GeneratesSlugs;

    public function index()
    {
        $mediaCategories = MediaCategory::withCount('media')->orderBy('name')->paginate(20);

        return view('admin.media.categories', compact('mediaCategories'));
    }

    public function create()
    {
        return view('admin.media.category-form', ['mediaCategory' => new MediaCategory()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $data['slug'] = $this->uniqueSlug(MediaCategory::class, $data['name']);

        MediaCategory::create($data);

        return redirect()->route('admin.media-categories.index')->with('success', 'Media category created.');
    }

    public function edit(MediaCategory $mediaCategory)
    {
        return view('admin.media.category-form', compact('mediaCategory'));
    }

    public function update(Request $request, MediaCategory $mediaCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ]);
        $data['slug'] = $this->uniqueSlug(MediaCategory::class, $data['name'], $mediaCategory->id, $request->input('slug'));

        $mediaCategory->update($data);

        return redirect()->route('admin.media-categories.index')->with('success', 'Media category updated.');
    }

    public function destroy(MediaCategory $mediaCategory)
    {
        if ($mediaCategory->media()->exists()) {
            return back()->with('error', 'Cannot delete: this category still has media files assigned.');
        }

        $mediaCategory->delete();

        return redirect()->route('admin.media-categories.index')->with('success', 'Media category deleted.');
    }
}
