{{-- expects: $title (string), $crumbs (array label=>url|null), optional $image (background photo URL) --}}
@php($crumbs = $crumbs ?? [])
@php($image = $image ?? asset('images/factory_about.jpg'))
<section class="page-hero" style="background: linear-gradient(135deg, rgba(26,26,46,0.92) 0%, rgba(192,57,43,0.55) 100%), url('{{ $image }}'); background-size:cover; background-position:center; padding:90px 0 50px; text-align:center; color:#fff;">
    <div class="container">
        <h1 style="color:#fff; margin-bottom:12px;">{{ $title }}</h1>
        <nav class="breadcrumb-nav" style="display:flex;justify-content:center;gap:8px;color:rgba(255,255,255,0.8);font-size:0.9rem">
            <a href="{{ url('/') }}" style="color:rgba(255,255,255,0.8)">{{ __('Home') }}</a>
            @foreach($crumbs as $label => $url)
                <span>/</span>
                @if($url)
                    <a href="{{ $url }}" style="color:rgba(255,255,255,0.8)">{{ $label }}</a>
                @else
                    <span style="color:#fff">{{ $label }}</span>
                @endif
            @endforeach
        </nav>
    </div>
</section>
