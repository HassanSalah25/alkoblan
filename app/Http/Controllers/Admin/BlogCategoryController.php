<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $blogCategories = BlogCategory::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('name_ar', 'like', "%{$request->q}%");
            }))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog-categories.index', compact('blogCategories'));
    }

    public function create()
    {
        return view('admin.blog-categories.form', ['blogCategory' => new BlogCategory()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogCategory::class, $data['name'], null, $request->input('slug'));
        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created.');
    }

    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.form', ['blogCategory' => $blogCategory]);
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogCategory::class, $data['name'], $blogCategory->id, $request->input('slug'));
        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
