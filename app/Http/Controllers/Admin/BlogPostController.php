<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $blogPosts = BlogPost::query()
            ->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->when($request->filled('blog_category_id'), fn ($q) => $q->where('blog_category_id', $request->blog_category_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog-posts.index', compact('blogPosts', 'categories'));
    }

    public function create()
    {
        return view('admin.blog-posts.form', [
            'blogPost' => new BlogPost(),
            'categories' => BlogCategory::orderBy('name')->get(),
            'tags' => BlogTag::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogPost::class, $data['title'], null, $request->input('slug'));
        $data['user_id'] = auth()->id();

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blogPost = BlogPost::create($data);
        $blogPost->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $blogPost)
    {
        return view('admin.blog-posts.form', [
            'blogPost' => $blogPost,
            'categories' => BlogCategory::orderBy('name')->get(),
            'tags' => BlogTag::all(),
        ]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(BlogPost::class, $data['title'], $blogPost->id, $request->input('slug'));

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blogPost->update($data);
        $blogPost->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', 'Blog post deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'blog_category_id' => ['nullable', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'excerpt_ar' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'content_ar' => ['nullable', 'string'],
            'featured_image_id' => ['nullable', 'exists:media,id'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:blog_tags,id'],
        ]);
    }
}
