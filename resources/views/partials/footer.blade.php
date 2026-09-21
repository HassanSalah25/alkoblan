<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="{{ url('/') }}" class="logo">
                    <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="logo-img logo-img-footer">
                </a>
                <p class="footer-desc">
                    {{ app()->getLocale() === 'ar' ? setting('footer_description_ar') : setting('footer_description') }}
                </p>
                <div class="footer-social">
                    @if(setting('social_facebook'))<a href="{{ setting('social_facebook') }}" class="footer-social-link" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>@endif
                    @if(setting('social_twitter'))<a href="{{ setting('social_twitter') }}" class="footer-social-link" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i></a>@endif
                    @if(setting('social_instagram'))<a href="{{ setting('social_instagram') }}" class="footer-social-link" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>@endif
                    @if(setting('social_linkedin'))<a href="{{ setting('social_linkedin') }}" class="footer-social-link" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i></a>@endif
                    @if(setting('social_youtube'))<a href="{{ setting('social_youtube') }}" class="footer-social-link" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>@endif
                </div>
            </div>
            <div class="footer-widget">
                <h5>{{ __('Quick Links') }}</h5>
                <ul class="footer-links">
                    @foreach($footerQuickLinks as $link)
                        <li><a href="{{ $link->url ?? '#' }}">{{ trans_field($link, 'title') }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="footer-widget">
                <h5>{{ __('Our Products') }}</h5>
                <ul class="footer-links">
                    @foreach($footerProductLinks as $link)
                        <li><a href="{{ $link->url ?? '#' }}">{{ trans_field($link, 'title') }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="footer-widget">
                <h5>{{ __('Contact Us') }}</h5>
                <ul class="footer-contact-list">
                    <li>
                        <div class="footer-contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <a href="{{ google_maps_directions_url() }}" target="_blank" rel="noopener" class="footer-contact-text">{{ app()->getLocale() === 'ar' ? setting('contact_address_ar') : setting('contact_address') }}</a>
                    </li>
                    <li>
                        <div class="footer-contact-icon"><i class="bi bi-telephone-fill"></i></div>
                        <div class="footer-contact-text">
                            @if(setting('contact_phone'))<a href="tel:{{ setting('contact_phone') }}" dir="ltr">{{ setting('contact_phone') }}</a>@endif
                            @if(setting('contact_phone_secondary'))<br><a href="tel:{{ setting('contact_phone_secondary') }}" dir="ltr">{{ setting('contact_phone_secondary') }}</a>@endif
                        </div>
                    </li>
                    <li>
                        <div class="footer-contact-icon"><i class="bi bi-envelope"></i></div>
                        <div class="footer-contact-text"><a href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a></div>
                    </li>
                    <li>
                        <div class="footer-contact-icon"><i class="bi bi-clock"></i></div>
                        <div class="footer-contact-text">{!! wrap_ltr_time_ranges(app()->getLocale() === 'ar' ? setting('working_hours_ar') : setting('working_hours')) !!}</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="footer-bottom">
            <div class="footer-copyright">
                {{ app()->getLocale() === 'ar' ? setting('copyright_text_ar') : setting('copyright_text') }}
            </div>
            <div class="footer-bottom-links">
                @foreach($footerBottomLinks as $link)
                    <a href="{{ $link->url ?? '#' }}">{{ trans_field($link, 'title') }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>

<div class="back-to-top" id="backToTop">
    <i class="bi bi-chevron-up"></i>
</div>

<a href="https://wa.me/{{ setting('contact_whatsapp') }}" class="whatsapp-float" target="_blank">
    <i class="bi bi-whatsapp"></i>
</a>

<div class="search-overlay" id="searchOverlay">
    <div class="search-close" id="searchClose"><i class="bi bi-x-lg"></i></div>
    <div class="search-overlay-inner">
        <form class="search-form" action="{{ route('shop.index') }}" method="GET">
            <input type="text" name="q" class="search-input" placeholder="{{ __('Search products, fittings, valves...') }}" value="{{ request('q') }}">
            <button type="submit" class="search-submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>
