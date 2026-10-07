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
        $post = BlogPost::published()
            ->where(fn ($q) => $q->where('slug', $slug)->orWhere('slug_ar', $slug))
            ->with(['category', 'author', 'tags', 'featuredImage'])
            ->firstOrFail();

        $post->increment('views_count');

        $locale = app()->getLocale();
        $isAr   = $locale === 'ar';

        $seoTitle       = ($isAr ? $post->seo_title_ar : $post->seo_title)
                        ?: ($isAr ? $post->title_ar    : $post->title)
                        ?: ($post->title_ar ?: $post->title);

        $seoDescription = ($isAr ? $post->seo_description_ar : $post->seo_description)
                        ?: ($isAr ? $post->excerpt_ar        : $post->excerpt)
                        ?: ($post->excerpt_ar ?: $post->excerpt);

        $seoKeywords    = ($isAr ? $post->seo_keywords_ar : $post->seo_keywords);

        // Admin-defined canonical takes priority; fall back to current URL
        $canonicalUrl   = ($isAr ? $post->canonical_url_ar : $post->canonical_url) ?: url()->current();

        $ogImage        = $post->featuredImage?->url;
        $ogImageAlt     = ($isAr ? $post->featured_image_alt_ar : $post->featured_image_alt)
                        ?: $seoTitle;

        $related = BlogPost::published()->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)->limit(3)->get();

        return view('pages.blog-detail', [
            'post'          => $post,
            'related'       => $related,
            'recentPosts'   => BlogPost::published()->latest('published_at')->limit(3)->get(),
            'categories'    => BlogCategory::withCount('posts')->get(),
            'seoTitle'      => $seoTitle,
            'seoDescription'=> $seoDescription,
            'seoKeywords'   => $seoKeywords,
            'canonicalUrl'  => $canonicalUrl,
            'ogImage'       => $ogImage,
            'ogImageAlt'    => $ogImageAlt,
        ]);
    }
}
