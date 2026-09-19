@extends('layouts.app')

@section('content')
<section class="page-hero" style="background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('{{ $event->featuredImage?->url ?? asset('images/factory_about.jpg') }}') center/cover; padding: 100px 0 60px; color: white; text-align: center;">
    <div class="container">
        <h1 style="font-size: 2.4rem; margin:15px 0;color:#fff;">{{ trans_field($event, 'title') }}</h1>
        <div style="display:flex;justify-content:center;gap:20px;opacity:0.9;font-size:0.9rem;flex-wrap:wrap;">
            <span><i class="bi bi-calendar3"></i> {{ $event->event_date?->translatedFormat('d M Y') }}</span>
            @if($event->location)<span><i class="bi bi-geo-alt-fill"></i> {{ trans_field($event, 'location') }}</span>@endif
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @if($event->images->count())
        <div class="event-gallery" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
            @foreach($event->images as $image)
                <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 220px;">
                    <img src="{{ $image->media?->url }}" alt="{{ trans_field($event, 'title') }}" style="width:100%;height:100%;object-fit:cover;">
                </div>
            @endforeach
        </div>
        @endif

        <article style="background:#fff;border-radius:12px;padding:35px;box-shadow:0 5px 20px rgba(0,0,0,0.05);max-width:900px;margin:0 auto;">
            <div style="line-height:1.9;color:#333;">
                {{ trans_field($event, 'description') }}
            </div>
        </article>

        @if($related->count())
        <div class="related-products-section" style="margin-top:60px;">
            <h2 class="related-header">{{ app()->getLocale() === 'ar' ? 'فعاليات أخرى' : 'Other Events' }}</h2>
            <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
                @foreach($related as $re)
                    <div class="blog-card" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.05);">
                        <div class="blog-card-img" style="height:180px;"><img src="{{ $re->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($re, 'title') }}" style="width:100%;height:100%;object-fit:cover;"></div>
                        <div class="blog-card-body" style="padding:20px;">
                            <h3 class="blog-card-title" style="font-size:1.1rem;"><a href="{{ route('events.show', $re->slug) }}" style="color:inherit;text-decoration:none;">{{ trans_field($re, 'title') }}</a></h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
