<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="{{ app()->getLocale() === 'en' ? 'en' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <meta name="description" content="{{ $seoDescription ?? (app()->getLocale() === 'ar' ? setting('seo_default_description_ar') : setting('seo_default_description')) }}">
    <meta name="keywords" content="{{ $seoKeywords ?? setting('seo_default_keywords') }}">
    @if(!empty($canonicalUrl)) <link rel="canonical" href="{{ $canonicalUrl }}"> @endif

    <meta property="og:title" content="{{ $seoTitle ?? (app()->getLocale() === 'ar' ? setting('seo_default_title_ar') : setting('seo_default_title')) }}">
    <meta property="og:description" content="{{ $seoDescription ?? (app()->getLocale() === 'ar' ? setting('seo_default_description_ar') : setting('seo_default_description')) }}">
    <meta property="og:type" content="website">
    @if(!empty($ogImage)) <meta property="og:image" content="{{ $ogImage }}"> @endif
    <meta name="twitter:card" content="summary_large_image">

    <title>{{ $seoTitle ?? (app()->getLocale() === 'ar' ? setting('seo_default_title_ar') : setting('seo_default_title')) }}</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    @yield('styles')
</head>
<body>

@include('partials.header')
@include('partials.mobile-menu')

@yield('content')

@include('partials.footer')

<script src="{{ asset('js/main.js') }}"></script>
<script src="{{ asset('js/cart.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
@yield('scripts')
</body>
</html>
