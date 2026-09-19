<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <a href="{{ url('/') }}" class="logo">
            <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="logo-img logo-img-mobile">
        </a>
        <div class="mobile-close" id="mobileClose">&times;</div>
    </div>
    <div class="mobile-nav">
        @foreach($mainMenu as $item)
            <div class="mobile-nav-item">
                @if($item->children->count())
                    <div class="mobile-nav-link" onclick="toggleMobileDropdown('mob-{{ $item->id }}')">
                        {{ trans_field($item, 'title') }} <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="mobile-dropdown" id="mob-{{ $item->id }}">
                        @foreach($item->children as $child)
                            <a href="{{ $child->url ?? '#' }}">{{ trans_field($child, 'title') }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $item->url ?? '#' }}" class="mobile-nav-link">{{ trans_field($item, 'title') }}</a>
                @endif
            </div>
        @endforeach
        <div style="padding: 20px 0; display:flex; flex-direction:column; gap:12px;">
            @auth
                <a href="{{ route('account.orders') }}" class="btn btn-primary" style="justify-content:center">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}"><button type="submit" class="btn btn-outline" style="justify-content:center;width:100%">{{ __('Logout') }}</button>@csrf</form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" style="justify-content:center">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="btn btn-outline" style="justify-content:center">{{ __('Create Account') }}</a>
            @endauth
        </div>
    </div>
</div>
