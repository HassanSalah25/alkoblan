<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;

class BlogTagController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $blogTags = BlogTag::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('name_ar', 'like', "%{$request->q}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog-tags.index', compact('blogTags'));
    }

    public function create()
    {
        return view('admin.blog-tags.form', ['blogTag' => new BlogTag()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogTag::class, $data['name'], null, $request->input('slug'));
        BlogTag::create($data);

        return redirect()->route('admin.blog-tags.index')->with('success', 'Blog tag created.');
    }

    public function edit(BlogTag $blogTag)
    {
        return view('admin.blog-tags.form', ['blogTag' => $blogTag]);
    }

    public function update(Request $request, BlogTag $blogTag)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogTag::class, $data['name'], $blogTag->id, $request->input('slug'));
        $blogTag->update($data);

        return redirect()->route('admin.blog-tags.index')->with('success', 'Blog tag updated.');
    }

    public function destroy(BlogTag $blogTag)
    {
        $blogTag->delete();

        return redirect()->route('admin.blog-tags.index')->with('success', 'Blog tag deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
