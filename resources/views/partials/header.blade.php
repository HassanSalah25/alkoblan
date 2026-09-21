<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-inner">
            <div class="top-bar-contacts">
                <a href="tel:{{ setting('contact_phone') }}" class="top-bar-contact-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span dir="ltr">{{ setting('contact_phone') }}</span>
                </a>
                <a href="mailto:{{ setting('contact_email') }}" class="top-bar-contact-item">
                    <i class="bi bi-envelope"></i>
                    <span>{{ setting('contact_email') }}</span>
                </a>
                <a href="{{ google_maps_directions_url() }}" target="_blank" rel="noopener" class="top-bar-contact-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>{{ app()->getLocale() === 'ar' ? setting('contact_address_ar') : setting('contact_address') }}</span>
                </a>
            </div>
            <div class="top-bar-right">
                <div class="social-links">
                    @if(setting('social_facebook'))<a href="{{ setting('social_facebook') }}" class="social-link" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>@endif
                    @if(setting('social_twitter'))<a href="{{ setting('social_twitter') }}" class="social-link" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i></a>@endif
                    @if(setting('social_linkedin'))<a href="{{ setting('social_linkedin') }}" class="social-link" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i></a>@endif
                    @if(setting('social_youtube'))<a href="{{ setting('social_youtube') }}" class="social-link" target="_blank" rel="noopener"><i class="bi bi-youtube"></i></a>@endif
                </div>
                <div class="lang-switcher">
                    <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn {{ app()->getLocale() === 'ar' ? 'active' : '' }}">عربي</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container">
        <div class="navbar-inner">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="logo-img">
            </a>

            <ul class="nav-menu">
                @foreach($mainMenu as $item)
                    <li class="nav-item">
                        <a href="{{ $item->url ?? '#' }}" class="nav-link {{ request()->is(trim($item->url ?? '', '/')) || (request()->is('/') && $item->url === '/') ? 'active' : '' }}">
                            {{ trans_field($item, 'title') }}
                            @if($item->children->count()) <i class="bi bi-chevron-down arrow" style="font-size:10px"></i> @endif
                        </a>
                        @if($item->children->count())
                            <div class="dropdown">
                                @foreach($item->children as $child)
                                    <a href="{{ $child->url ?? '#' }}" class="dropdown-item">
                                        @if($child->icon)<i class="bi {{ $child->icon }}"></i>@endif
                                        {{ trans_field($child, 'title') }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="nav-actions">
                <div class="nav-search-btn" id="searchBtn">
                    <i class="bi bi-search"></i>
                </div>
                <div class="nav-item nav-cart">
                    <div class="nav-search-btn" style="cursor:pointer" onclick="location.href='{{ route('cart.index') }}'">
                        <i class="bi bi-cart3"></i>
                        <span class="nav-cart-count">{{ $cartCount }}</span>
                    </div>
                </div>
                @auth
                    <a href="{{ route('account.orders') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-fill"></i> <span class="nav-user-name">{{ auth()->user()->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-fill"></i> {{ __('Login') }}
                    </a>
                @endauth
                <div class="hamburger" id="hamburger">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </div>
</nav>
