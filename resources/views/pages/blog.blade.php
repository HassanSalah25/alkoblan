@extends('layouts.app')

@section('content')
<section class="page-hero" style="background-image: url('{{ asset('images/hero_banner.jpg') }}'); position: relative; padding: 100px 0; color: white; text-align: center;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6);"></div>
    <div class="container" style="position: relative; z-index: 1;">
        <h1 style="font-size: 3rem; margin-bottom: 15px;">المدونة والأخبار</h1>
        <p style="font-size: 1.2rem; max-width: 600px; margin: 0 auto;">تابع أحدث المقالات والإرشادات والأخبار في عالم السباكة وأنظمة المياه</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-layout" style="display: grid; grid-template-columns: 1fr 320px; gap: 40px;">
            <div class="blog-main">
                @forelse($posts as $post)
                    <article class="blog-list-card" style="display: flex; gap: 20px; margin-bottom: 30px; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                        <div class="blog-list-img" style="width: 40%; flex-shrink: 0;">
                            <img src="{{ $post->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($post, 'title') }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="blog-list-content" style="padding: 25px; display: flex; flex-direction: column; justify-content: center;">
                            @if($post->category)
                            <div class="blog-list-tags" style="margin-bottom: 10px;">
                                <span style="background: var(--primary); color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">{{ trans_field($post->category, 'name') }}</span>
                            </div>
                            @endif
                            <h3 style="margin: 0 0 10px 0; font-size: 1.4rem; color: #2c3e50;"><a href="{{ route('blog.show', $post->slug) }}" style="color: inherit; text-decoration: none;">{{ trans_field($post, 'title') }}</a></h3>
                            <div class="blog-meta" style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 15px; display: flex; gap: 15px;">
                                <span><i class="bi bi-calendar3"></i> {{ $post->published_at?->translatedFormat('d M Y') }}</span>
                                @if($post->author)<span><i class="bi bi-person-fill"></i> {{ $post->author->name }}</span>@endif
                            </div>
                            <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">{{ trans_field($post, 'excerpt') }}</p>
                            <div><a href="{{ route('blog.show', $post->slug) }}" class="btn btn-primary btn-sm">اقرأ المزيد <i class="bi bi-arrow-left"></i></a></div>
                        </div>
                    </article>
                @empty
                    <p>لا توجد مقالات حالياً.</p>
                @endforelse

                @if($posts->hasPages())
                <div class="pagination" style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>

            @include('partials.blog-sidebar', ['recentPosts' => $recentPosts, 'categories' => $categories])
        </div>
    </div>
</section>

@if($events->count())
<section id="events" class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">الفعاليات والأحداث</div>
            <h2 class="section-title">أحدث <span class="color-primary">الفعاليات</span></h2>
            <p class="section-subtitle">تغطية خاصة لمشاركاتنا في المعارض والفعاليات المحلية والدولية</p>
        </div>
        <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            @foreach($events as $event)
                <div class="blog-card" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                    <div class="blog-card-img" style="position: relative; height: 220px;">
                        <img src="{{ $event->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($event, 'title') }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @if($event->is_featured)<span class="blog-card-category" style="position: absolute; top: 15px; right: 15px; background: var(--primary); color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">مميز</span>@endif
                    </div>
                    <div class="blog-card-body" style="padding: 25px;">
                        <div class="blog-meta" style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 15px; display: flex; gap: 15px;">
                            <span><i class="bi bi-calendar3"></i> {{ $event->event_date?->translatedFormat('d M Y') }}</span>
                            @if($event->location)<span><i class="bi bi-geo-alt-fill"></i> {{ trans_field($event, 'location') }}</span>@endif
                        </div>
                        <h3 class="blog-card-title" style="margin: 0 0 10px 0; font-size: 1.25rem;">{{ trans_field($event, 'title') }}</h3>
                        <p class="blog-card-excerpt" style="color: #666; margin-bottom: 20px; line-height: 1.6; font-size: 0.95rem;">{{ trans_field($event, 'description') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
