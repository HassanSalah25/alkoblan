<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Event;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::published()->with(['category', 'author']);
        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")->orWhere('title_ar', 'like', "%{$q}%");
            });
        }

        return view('pages.blog', [
            'posts' => $query->latest('published_at')->paginate(5)->withQueryString(),
            'recentPosts' => BlogPost::published()->latest('published_at')->limit(3)->get(),
            'categories' => BlogCategory::withCount('posts')->get(),
            'tags' => BlogTag::limit(8)->get(),
            'events' => Event::published()->orderBy('event_date')->limit(6)->get(),
        ]);
    }

    public function show(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->published()->with(['category', 'author', 'tags'])->firstOrFail();
        $post->increment('views_count');

        $related = BlogPost::published()->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)->limit(3)->get();

        return view('pages.blog-detail', [
            'post' => $post,
            'related' => $related,
            'recentPosts' => BlogPost::published()->latest('published_at')->limit(3)->get(),
            'categories' => BlogCategory::withCount('posts')->get(),
        ]);
    }
}
