@extends('layouts.app')

@section('content')
@include('partials.page-hero', [
    'title' => app()->getLocale() === 'ar' ? 'الفعاليات' : 'Events',
    'crumbs' => [(app()->getLocale() === 'ar' ? 'الفعاليات' : 'Events') => null],
])

<section class="section">
    <div class="container">
        @forelse($events as $event)
        @if($loop->first)
        <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        @endif
            <div class="blog-card" style="background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                <a href="{{ route('events.show', $event->slug) }}" style="text-decoration:none;color:inherit;">
                    <div class="blog-card-img" style="position: relative; height: 220px;">
                        <img src="{{ $event->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($event, 'title') }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @if($event->is_featured)<span class="blog-card-category" style="position: absolute; top: 15px; {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 15px; background: var(--primary); color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">{{ __('Featured') }}</span>@endif
                    </div>
                    <div class="blog-card-body" style="padding: 25px;">
                        <div class="blog-meta" style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 15px; display: flex; gap: 15px;">
                            <span><i class="bi bi-calendar3"></i> {{ $event->event_date?->translatedFormat('d M Y') }}</span>
                            @if($event->location)<span><i class="bi bi-geo-alt-fill"></i> {{ trans_field($event, 'location') }}</span>@endif
                        </div>
                        <h3 class="blog-card-title" style="margin: 0 0 10px 0; font-size: 1.25rem;">{{ trans_field($event, 'title') }}</h3>
                        <p class="blog-card-excerpt" style="color: #666; margin-bottom: 0; line-height: 1.6; font-size: 0.95rem;">{{ \Illuminate\Support\Str::limit(trans_field($event, 'description'), 120) }}</p>
                    </div>
                </a>
            </div>
        @if($loop->last)
        </div>
        @endif
        @empty
            <p>{{ app()->getLocale() === 'ar' ? 'لا توجد فعاليات حالياً.' : 'No events yet.' }}</p>
        @endforelse

        @if($events->hasPages())
        <div class="pagination" style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
