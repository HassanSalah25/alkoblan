@extends('layouts.app')

@section('styles')
<style>
    .large-category-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:24px; }
    @media(max-width: 991px) { .large-category-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 575px) { .large-category-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@include('partials.page-hero', ['title' => __('Categories'), 'crumbs' => [__('Categories') => null]])

<section class="section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">{{ __('Main Categories') }}</div>
            <h2 class="section-title reveal">{{ __('Our') }} <span class="color-primary">{{ __('Product Range') }}</span></h2>
            <p class="section-subtitle reveal delay-1">{{ __('Everything you need for residential, commercial and industrial water networks') }}</p>
        </div>

        <div class="large-category-grid">
            @foreach($categories as $cat)
                <a href="{{ route('shop.index', ['cat' => $cat->slug]) }}" class="large-category-card reveal">
                    <img src="{{ $cat->image?->url ?? asset('images/pipe_product.jpg') }}" alt="{{ trans_field($cat, 'name') }}">
                    <div class="large-category-badge">{{ $cat->total_products }} {{ __('products') }}</div>
                    <div class="large-category-overlay">
                        <div class="large-category-icon"><i class="bi {{ $cat->icon ?? 'bi-box-seam' }}"></i></div>
                        <h3 class="large-category-title">{{ trans_field($cat, 'name') }}</h3>
                        <p class="large-category-desc">{{ trans_field($cat, 'description') }}</p>
                        <div class="large-category-btn">{{ __('Browse Products') }} <i class="bi bi-arrow-left"></i></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<div class="features-row" style="border-top: 1px solid var(--border);">
    <div class="container-fluid">
        <div class="features-grid">
            <div class="feature-item reveal">
                <div class="feature-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="feature-content"><h4>{{ __('Certified Quality') }}</h4><p>{{ __('ISO & SASO International Standards') }}</p></div>
            </div>
            <div class="feature-item reveal delay-1">
                <div class="feature-icon" style="background:linear-gradient(135deg,var(--accent),var(--accent-hover))"><i class="bi bi-truck"></i></div>
                <div class="feature-content"><h4>{{ __('Fast Delivery') }}</h4><p>{{ __('To All Regions of the Kingdom') }}</p></div>
            </div>
            <div class="feature-item reveal delay-2">
                <div class="feature-icon" style="background:linear-gradient(135deg,#2980b9,#1a5276)"><i class="bi bi-headset"></i></div>
                <div class="feature-content"><h4>{{ __('24/7 Technical Support') }}</h4><p>{{ __('Specialized Expert Team') }}</p></div>
            </div>
            <div class="feature-item reveal delay-3">
                <div class="feature-icon" style="background:linear-gradient(135deg,#8e44ad,#6c3483)"><i class="bi bi-shield-check"></i></div>
                <div class="feature-content"><h4>{{ __('25-Year Warranty') }}</h4><p>{{ __('On All Products') }}</p></div>
            </div>
        </div>
    </div>
</div>

<section class="cta-section">
    <div class="container">
        <div class="cta-inner">
            <h2 class="cta-title reveal">{{ __('Need help choosing the right product?') }}</h2>
            <p class="cta-text reveal delay-1">{{ __('Our engineering team is ready to help you pick the right solution for your project.') }}</p>
            <div class="cta-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg"><i class="bi bi-telephone-fill"></i> {{ __('Contact Now') }}</a>
            </div>
        </div>
    </div>
</section>
@endsection
