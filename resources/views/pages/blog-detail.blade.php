@extends('layouts.app')

@section('content')
<section class="page-hero" style="background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('{{ $post->featuredImage?->url ?? asset('images/factory_about.jpg') }}') center/cover; padding: 100px 0 60px; color: white; text-align: center;">
    <div class="container">
        @if($post->category)<span style="background: var(--primary); padding: 5px 14px; border-radius: 4px; font-size: 0.85rem;">{{ trans_field($post->category, 'name') }}</span>@endif
        <h1 style="font-size: 2.4rem; margin:15px 0;color:#fff;">{{ trans_field($post, 'title') }}</h1>
        <div style="display:flex;justify-content:center;gap:20px;opacity:0.9;font-size:0.9rem;">
            @if($post->author)<span><i class="bi bi-person-fill"></i> {{ $post->author->name }}</span>@endif
            <span><i class="bi bi-calendar3"></i> {{ $post->published_at?->translatedFormat('d M Y') }}</span>
            <span><i class="bi bi-eye"></i> {{ $post->views_count }}</span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-layout" style="display: grid; grid-template-columns: 1fr 320px; gap: 40px;">
            <article class="blog-main" style="background:#fff;border-radius:12px;padding:35px;box-shadow:0 5px 20px rgba(0,0,0,0.05);">
                <div style="line-height:1.9;color:#333;">
                    {!! trans_field($post, 'content') !!}
                </div>

                <div style="display:flex;gap:12px;margin-top:30px;padding-top:20px;border-top:1px solid #eee;">
                    <a href="https://wa.me/?text={{ urlencode(trans_field($post, 'title').' - '.url()->current()) }}" target="_blank" class="btn btn-sm btn-outline"><i class="bi bi-whatsapp"></i></a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-sm btn-outline"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-sm btn-outline"><i class="bi bi-linkedin"></i></a>
                </div>

                @if($post->author)
                <div style="display:flex;gap:15px;align-items:center;margin-top:25px;background:var(--light-gray);padding:20px;border-radius:10px;">
                    <div style="width:60px;height:60px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;">{{ mb_substr($post->author->name, 0, 1) }}</div>
                    <div>
                        <h5 style="margin:0">{{ $post->author->name }}</h5>
                        <p style="margin:0;color:#777;font-size:0.9rem;">{{ __('Author') }}</p>
                    </div>
                </div>
                @endif
            </article>

            @include('partials.blog-sidebar', ['recentPosts' => $recentPosts, 'categories' => $categories])
        </div>

        @if($related->count())
        <div class="related-products-section" style="margin-top:60px;">
            <h2 class="related-header">مقالات ذات صلة</h2>
            <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
                @foreach($related as $rp)
                    <div class="blog-card" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.05);">
                        <div class="blog-card-img"><img src="{{ $rp->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($rp, 'title') }}"></div>
                        <div class="blog-card-body" style="padding:20px;">
                            <h3 class="blog-card-title" style="font-size:1.1rem;"><a href="{{ route('blog.show', $rp->slug) }}" style="color:inherit;text-decoration:none;">{{ trans_field($rp, 'title') }}</a></h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
