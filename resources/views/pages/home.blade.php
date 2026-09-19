@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/14.2.0/swiper-bundle.min.css">
<style>
    @foreach($heroSlides as $i => $slide)
        .hero-slide-bg-{{ $i + 1 }} { background-image: url('{{ $slide->imageDesktop?->url ?? asset('images/hero_banner.jpg') }}'); }
    @endforeach
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="hero">
    <div class="hero-slider" id="heroSlider">
        @foreach($heroSlides as $i => $slide)
            <div class="hero-slide {{ $i === 0 ? 'active' : '' }}" id="slide-{{ $i }}">
                <div class="hero-slide-bg hero-slide-bg-{{ $i + 1 }}"></div>
                <div class="hero-overlay"></div>
                <div class="container" style="position:relative;z-index:2;height:100%;display:flex;align-items:center;padding: 0 20px;">
                    <div class="hero-content">
                        @if($slide->tag)
                            <div class="hero-tag"><i class="bi bi-award"></i> {{ trans_field($slide, 'tag') }}</div>
                        @endif
                        <h1 class="hero-title">{{ trans_field($slide, 'title') }}</h1>
                        @if($slide->subtitle)
                            <p class="hero-text">{{ trans_field($slide, 'subtitle') }}</p>
                        @endif
                        <div class="hero-actions">
                            @if($slide->button_text)
                                <a href="{{ $slide->button_url ?? '#' }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-bag-fill"></i> {{ trans_field($slide, 'button_text') }}
                                </a>
                            @endif
                            @if($slide->button2_text)
                                <a href="{{ $slide->button2_url ?? '#' }}" class="btn btn-outline-white btn-lg">
                                    <i class="bi bi-file-earmark-text"></i> {{ trans_field($slide, 'button2_text') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="hero-dots">
            @foreach($heroSlides as $i => $slide)
                <div class="hero-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></div>
            @endforeach
        </div>
        <div class="hero-arrows">
            <div class="hero-arrow" id="heroPrev"><i class="bi bi-chevron-right"></i></div>
            <div class="hero-arrow" id="heroNext"><i class="bi bi-chevron-left"></i></div>
        </div>
    </div>

    <div class="hero-stats">
        <div class="hero-stat"><span class="hero-stat-number" data-count="30">0</span><span class="hero-stat-label">{{ __('Years of Experience') }}</span></div>
        <div class="hero-stat"><span class="hero-stat-number" data-count="500">0</span><span class="hero-stat-label">{{ __('Diverse Products') }}</span></div>
        <div class="hero-stat"><span class="hero-stat-number" data-count="10000">0</span><span class="hero-stat-label">{{ __('Happy Clients') }}</span></div>
        <div class="hero-stat"><span class="hero-stat-number" data-count="3">0</span><span class="hero-stat-label">{{ __('Main Cities') }}</span></div>
    </div>
</section>

<!-- Ticker -->
<div class="ticker-wrap">
    <div class="ticker">
        @for($i = 0; $i < 2; $i++)
            <span class="ticker-item"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ __('Internationally Certified PPR Pipes') }}</span>
            <span class="ticker-item"><i class="fas fa-industry" aria-hidden="true"></i> {{ __('Authentic Saudi Manufacturing') }}</span>
            <span class="ticker-item"><i class="fas fa-shield-halved" aria-hidden="true"></i> {{ __('25-Year Warranty on All Products') }}</span>
            <span class="ticker-item"><i class="fas fa-truck" aria-hidden="true"></i> {{ __('Free Delivery on Orders Over 500 SAR') }}</span>
            <span class="ticker-item"><i class="fas fa-headset" aria-hidden="true"></i> {{ __('24/7 Technical Support') }}</span>
            <span class="ticker-item"><i class="fas fa-award" aria-hidden="true"></i> {{ __('ISO 9001:2015 Certified') }}</span>
        @endfor
    </div>
</div>

<!-- Features Row -->
<div class="features-row">
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

<!-- Categories Section -->
<section class="categories-section section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">{{ __('Our Categories') }}</div>
            <h2 class="section-title reveal">{{ __('Browse') }} <span class="color-primary">{{ __('Categories') }}</span></h2>
            <p class="section-subtitle reveal delay-1">{{ __('Discover our comprehensive range of water pipes and fittings manufactured to the highest quality standards') }}</p>
        </div>
        <div class="categories-grid">
            @foreach($featuredCategories as $cat)
                <a href="{{ route('shop.index', ['cat' => $cat->slug]) }}" class="category-card reveal">
                    <div class="category-icon"><i class="bi {{ $cat->icon ?? 'bi-box-seam' }}"></i></div>
                    <h4>{{ trans_field($cat, 'name') }}</h4>
                    <p>{{ trans_field($cat, 'description') }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="products-section section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">{{ __('Our Products') }}</div>
            <h2 class="section-title reveal">{{ __('Top Rated') }} <span class="color-primary">{{ __('Products') }}</span></h2>
            <p class="section-subtitle reveal delay-1">{{ __('Our product line covers all types of thermal pipes and fittings used in plumbing networks, manufactured with the latest European technology') }}</p>
        </div>

        <div class="products-filter">
            <button class="filter-btn active" data-filter="all">{{ __('All') }}</button>
            @foreach($featuredCategories as $cat)
                <button class="filter-btn" data-filter="{{ $cat->slug }}">{{ trans_field($cat, 'name') }}</button>
            @endforeach
        </div>

        <div class="products-grid" id="productsGrid">
            @foreach($featuredProducts as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="text-center mt-50">
            <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg"><i class="bi bi-shop"></i> {{ __('View All Products') }}</a>
        </div>
    </div>
</section>

<!-- About Section -->
@if($aboutBlock)
<section class="about-section section">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap reveal-left">
                <div class="about-image-main">
                    <img src="{{ $aboutBlock->image?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($aboutBlock, 'title') }}">
                </div>
                <div class="about-image-badge">
                    <span class="about-image-badge-number">{{ $aboutBlock->extra['badge_number'] ?? '30+' }}</span>
                    <span class="about-image-badge-text">{{ app()->getLocale() === 'ar' ? ($aboutBlock->extra['badge_label_ar'] ?? '') : ($aboutBlock->extra['badge_label'] ?? '') }}</span>
                </div>
            </div>
            <div class="about-content reveal-right">
                <div class="section-tag">{{ __('About Us') }}</div>
                <h2 class="section-title">{{ trans_field($aboutBlock, 'title') }}</h2>
                <p class="section-subtitle">{{ trans_field($aboutBlock, 'content') }}</p>
                <div class="about-features">
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('Premium Raw Materials') }}</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('Modern European Technology') }}</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('International Standards Compliance') }}</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('Specialized Engineering Team') }}</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('ISO & SASO Certificates') }}</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>{{ __('After-Sales Service') }}</span></div>
                </div>
                <div class="about-actions">
                    <a href="{{ route('about') }}" class="btn btn-primary"><i class="bi bi-info-circle"></i> {{ __('Learn More') }}</a>
                    @if($aboutBlock->button_text)
                        <a href="{{ $aboutBlock->button_url ?? route('company-profile') }}" class="btn btn-outline-white"><i class="bi bi-file-earmark-text"></i> {{ trans_field($aboutBlock, 'button_text') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Stats Section -->
<div class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item reveal">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="30">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">{{ __('Years in Market') }}</span>
            </div>
            <div class="stat-item reveal delay-1">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-boxes"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="500">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">{{ __('Diverse Products') }}</span>
            </div>
            <div class="stat-item reveal delay-2">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="10000">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">{{ __('Completed Projects') }}</span>
            </div>
            <div class="stat-item reveal delay-3">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="3">0</span>
                </div>
                <span class="stat-label">{{ __('Main Branches in Saudi Arabia') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Blog Section -->
@if($latestPosts->count())
<section class="blog-section section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">{{ __('Blog') }}</div>
            <h2 class="section-title reveal">{{ __('Latest') }} <span class="color-primary">{{ __('Articles') }}</span></h2>
            <p class="section-subtitle reveal delay-1">{{ __('We share the latest news and information related to plumbing and water pipes') }}</p>
        </div>
        <div class="blog-grid">
            @foreach($latestPosts as $post)
                <div class="blog-card reveal">
                    <div class="blog-card-img">
                        <img src="{{ $post->featuredImage?->url ?? asset('images/factory_about.jpg') }}" alt="{{ trans_field($post, 'title') }}">
                        @if($post->category)<span class="blog-card-category">{{ trans_field($post->category, 'name') }}</span>@endif
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <span><i class="bi bi-calendar3"></i> {{ $post->published_at?->translatedFormat('d M Y') }}</span>
                        </div>
                        <h3 class="blog-card-title">{{ trans_field($post, 'title') }}</h3>
                        <p class="blog-card-excerpt">{{ trans_field($post, 'excerpt') }}</p>
                        <a href="{{ route('blog.show', $post->slug) }}" class="blog-card-link">{{ __('Read More') }} <i class="bi bi-arrow-left"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-50">
            <a href="{{ route('blog.index') }}" class="btn btn-outline"><i class="bi bi-newspaper"></i> {{ __('View All Articles') }}</a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials -->
@if($testimonials->count())
<section class="testimonials-section section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">{{ __('Testimonials') }}</div>
            <h2 class="section-title reveal">{{ __('What Our') }} <span class="color-primary">{{ __('Clients Say') }}</span></h2>
        </div>
        <div class="testimonials-slider-wrap">
            <div class="testimonials-track" id="testimonialsTrack">
                @foreach($testimonials as $t)
                    <div class="testimonial-card">
                        <div class="testimonial-quote">"</div>
                        <div class="testimonial-stars">{{ str_repeat('★', $t->rating) }}</div>
                        <p class="testimonial-text">{{ trans_field($t, 'content') }}</p>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">{{ mb_substr(trans_field($t, 'name'), 0, 1) }}</div>
                            <div>
                                <div class="testimonial-name">{{ trans_field($t, 'name') }}</div>
                                <div class="testimonial-role">{{ trans_field($t, 'position') }}@if($t->company) - {{ trans_field($t, 'company') }}@endif</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="testimonials-controls">
            <div class="testimonial-arrow" id="testPrev"><i class="bi bi-chevron-right"></i></div>
            <div class="testimonial-dots">
                @foreach($testimonials as $i => $t)
                    <div class="testimonial-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></div>
                @endforeach
            </div>
            <div class="testimonial-arrow" id="testNext"><i class="bi bi-chevron-left"></i></div>
        </div>
    </div>
</section>
@endif

<!-- Brands/Certifications -->
<div class="brands-section">
    <div class="container-fluid">
        <div class="brands-track">
            <div class="brands-slider">
                @for($i = 0; $i < 2; $i++)
                    <div class="brand-item"><i class="fas fa-trophy" aria-hidden="true"></i> ISO 9001:2015</div>
                    <div class="brand-item"><i class="fas fa-circle-check" aria-hidden="true"></i> SASO</div>
                    <div class="brand-item"><i class="fas fa-earth-europe" aria-hidden="true"></i> {{ __('European Standard') }}</div>
                    <div class="brand-item"><i class="fas fa-flask" aria-hidden="true"></i> {{ __('Certified Testing') }}</div>
                    <div class="brand-item"><i class="fas fa-industry" aria-hidden="true"></i> {{ __('Made in Saudi Arabia') }}</div>
                    <div class="brand-item"><i class="fas fa-shield-halved" aria-hidden="true"></i> {{ __('25-Year Warranty') }}</div>
                @endfor
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
@if($ctaBlock)
<section class="cta-section">
    <div class="container">
        <div class="cta-inner">
            <div class="section-tag" style="background:rgba(255,255,255,0.15);color:#fff;">{{ __('Contact Us') }}</div>
            <h2 class="cta-title reveal">{{ trans_field($ctaBlock, 'title') }}</h2>
            <p class="cta-text reveal delay-1">{{ trans_field($ctaBlock, 'content') }}</p>
            <div class="cta-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg"><i class="bi bi-telephone-fill"></i> {{ __('Contact Now') }}</a>
                <a href="https://wa.me/{{ setting('contact_whatsapp') }}" class="btn btn-outline-white btn-lg" target="_blank"><i class="bi bi-whatsapp"></i> {{ __('WhatsApp') }}</a>
            </div>
        </div>
    </div>
</section>
@endif

@if($famousClients->count())
<section class="famous-clients-section section">
    <div class="container">
        <div class="section-header center">
            <h2 class="section-title reveal">{{ __('Famous') }} <span class="color-gold">{{ __('Clients') }}</span></h2>
        </div>
        <div class="famous-clients-swiper swiper">
            <div class="swiper-wrapper">
                @foreach($famousClients as $client)
                    @continue(! $client->logo)
                    <div class="swiper-slide famous-client-item">
                        @if($client->url)
                            <a href="{{ $client->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ trans_field($client, 'name') }}">
                        @endif
                        <div class="famous-client-logo">
                            <img src="{{ $client->logo->url }}" alt="{{ trans_field($client, 'name') }}" loading="lazy" width="120" height="120">
                        </div>
                        @if($client->url)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="famous-clients-controls">
            <div class="famous-clients-arrow" id="clientsPrev"><i class="bi bi-chevron-right"></i></div>
            <div class="famous-clients-arrow" id="clientsNext"><i class="bi bi-chevron-left"></i></div>
        </div>
    </div>
</section>
@endif

<section class="premium-parallax">
    <div class="parallax-bg" style="background-image:url('{{ asset('images/factory_about.jpg') }}')"></div>
    <div class="parallax-overlay"></div>
    <div class="parallax-content reveal">
        <h2 class="parallax-title">{{ __('Quality') }} <span>{{ __('That Stands the Test of Time') }}</span></h2>
        <p class="parallax-desc">{{ __('We use the latest German technology to ensure leak-free pipes that last for decades.') }}</p>
    </div>
</section>

<section class="premium-cta">
    <div class="container">
        <div class="cta-vip-card reveal">
            <h2 class="cta-vip-title">{{ __('Join Our Newsletter') }}</h2>
            <p style="color:rgba(255,255,255,0.7); margin-bottom:20px;">{{ __('Get the latest news and factory offers straight to your inbox') }}</p>
            <form class="cta-form" method="POST" action="{{ route('newsletter.subscribe') }}">
                @csrf
                <input type="email" name="email" class="cta-vip-input" placeholder="{{ __('Enter your email...') }}" required>
                <button type="submit" class="btn btn-primary">{{ __('Subscribe Now') }}</button>
            </form>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/14.2.0/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var clientsSwiperEl = document.querySelector('.famous-clients-swiper');
    if (clientsSwiperEl && window.Swiper) {
        new Swiper(clientsSwiperEl, {
            slidesPerView: 3,
            spaceBetween: 24,
            loop: true,
            autoplay: { delay: 2200, disableOnInteraction: false },
            speed: 700,
            navigation: {
                prevEl: '#clientsPrev',
                nextEl: '#clientsNext'
            },
            breakpoints: {
                480: { slidesPerView: 3, spaceBetween: 20 },
                768: { slidesPerView: 4, spaceBetween: 28 },
                1024: { slidesPerView: 6, spaceBetween: 36 }
            }
        });
    }
});
</script>
@endsection
