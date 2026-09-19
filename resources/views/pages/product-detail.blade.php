@extends('layouts.app')

@php
    $mainImg = $product->mainImage?->media?->url ?? $product->images->first()?->media?->url ?? asset('images/pipe_product.jpg');
    $gallery = $product->images->count() ? $product->images : collect();
@endphp

@section('content')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": {!! json_encode(trans_field($product, 'name')) !!},
    "sku": {!! json_encode($product->sku) !!},
    "description": {!! json_encode(trans_field($product, 'short_description')) !!},
    "image": {!! json_encode($mainImg) !!},
    "offers": {
        "@type": "Offer",
        "price": {{ $product->effective_price }},
        "priceCurrency": {!! json_encode($product->currency) !!},
        "availability": "https://schema.org/{{ $product->stock_status === 'in_stock' ? 'InStock' : 'OutOfStock' }}"
    }
}
</script>
<div class="container" style="padding-top:20px;">
    <div class="breadcrumb-nav">
        <a href="{{ url('/') }}">{{ __('Home') }}</a>
        <span class="breadcrumb-sep"><i class="bi bi-chevron-left"></i></span>
        <a href="{{ route('shop.index', ['cat' => $product->category?->slug]) }}">{{ trans_field($product->category, 'name') }}</a>
        <span class="breadcrumb-sep"><i class="bi bi-chevron-left"></i></span>
        <span class="breadcrumb-current">{{ trans_field($product, 'name') }}</span>
    </div>
</div>

<div class="container">
    <div class="product-detail-wrapper">
        <div class="product-gallery">
            <div class="gallery-main">
                <img src="{{ $mainImg }}" alt="{{ trans_field($product, 'name') }}" id="mainGalleryImg">
            </div>
            @if($gallery->count() > 1)
            <div class="gallery-thumbs">
                @foreach($gallery as $img)
                    <div class="gallery-thumb {{ $loop->first ? 'active' : '' }}">
                        <img src="{{ $img->media->url }}" alt="{{ trans_field($product, 'name') }} {{ $loop->iteration }}">
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="product-detail-info">
            <div class="category-badge">{{ trans_field($product->category, 'name') }}</div>
            <h1 class="product-title">{{ trans_field($product, 'name') }}</h1>

            <div class="product-price product-price-main">
                @if($product->effective_price > 0)
                    {{ number_format($product->effective_price, 0) }} {{ $product->currency }}
                    @if($product->is_on_sale)
                        <small style="text-decoration:line-through;color:var(--text-light);font-weight:normal;">{{ number_format($product->price, 0) }}</small>
                    @endif
                @else
                    <span style="font-size:1.3rem;">{{ __('Contact for Price') }}</span>
                @endif
            </div>

            <p class="product-desc">{{ trans_field($product, 'short_description') }}</p>

            @if($product->specifications)
            <div class="product-specs-grid">
                @foreach($product->specifications as $spec)
                    <div class="spec-item">
                        <i class="bi bi-info-circle"></i>
                        <span class="spec-label">{{ app()->getLocale() === 'ar' ? ($spec['label_ar'] ?? $spec['label']) : $spec['label'] }}:</span>
                        <span class="spec-value">{{ $spec['value'] }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            @if($product->variants->count())
            <div class="sidebar-widget" style="margin-bottom:20px;">
                <h4 class="sidebar-widget-title">{{ __('Choose Size / Variant') }}</h4>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($product->variants as $variant)
                        <label style="cursor:pointer;padding:6px 14px;border:2px solid var(--border);border-radius:50px;font-size:0.82rem;">
                            <input type="radio" name="variant_id" value="{{ $variant->id }}" {{ $loop->first ? 'checked' : '' }} style="margin-left:4px;">
                            {{ $variant->label }} - {{ number_format($variant->effective_price, 0) }} {{ $product->currency }}
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="product-actions-wrapper">
                <div class="qty-wrapper">
                    <button class="qty-btn minus" id="btnMinus"><i class="bi bi-dash-lg"></i></button>
                    <input type="number" class="qty-input" value="1" min="{{ $product->min_order_qty }}" id="qtyInput">
                    <button class="qty-btn plus" id="btnPlus"><i class="bi bi-plus-lg"></i></button>
                </div>

                <button class="btn btn-primary btn-add-cart" id="mainAddToCart" data-product-id="{{ $product->id }}">
                    <i class="bi bi-cart3"></i> {{ __('Add to Cart') }}
                </button>
                <a href="https://wa.me/{{ setting('contact_whatsapp') }}?text={{ urlencode(trans_field($product, 'name')) }}" target="_blank" class="btn btn-add-cart" style="background:var(--accent);color:#fff;border:none;">
                    <i class="bi bi-whatsapp"></i> {{ __('Direct Inquiry') }}
                </a>
            </div>

            <div class="product-meta">
                @if($product->sku)
                <div class="product-meta-item">
                    <span class="product-meta-label">{{ __('SKU') }}:</span>
                    <span class="product-meta-value">{{ $product->sku }}</span>
                </div>
                @endif
                <div class="product-meta-item">
                    <span class="product-meta-label">{{ __('Category') }}:</span>
                    <span class="product-meta-value"><a href="{{ route('shop.index', ['cat' => $product->category?->slug]) }}">{{ trans_field($product->category, 'name') }}</a></span>
                </div>
                @if($product->attributeValues->count())
                <div class="product-meta-item">
                    <span class="product-meta-label">{{ __('Available Sizes') }}:</span>
                    <span class="product-meta-value">{{ $product->attributeValues->pluck('value')->implode('، ') }}</span>
                </div>
                @endif
                <div class="product-meta-item">
                    <span class="product-meta-label">{{ __('Availability') }}:</span>
                    <span class="product-meta-value status-in-stock">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ $product->stock_status === 'in_stock' ? __('In Stock') : __('Out of Stock') }}
                    </span>
                </div>
            </div>

            @if($product->files->count())
            <div class="product-meta" style="margin-top:15px;">
                @foreach($product->files as $file)
                    <div class="product-meta-item">
                        <a href="{{ $file->media->url }}" target="_blank" class="btn btn-outline btn-sm">
                            <i class="bi bi-file-earmark-arrow-down"></i> {{ trans_field($file, 'title') ?: $file->media->original_name }}
                        </a>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="product-tabs-section">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="tab-details">{{ __('Product Details') }}</button>
            @if($product->technical_specifications)<button class="tab-btn" data-tab="tab-specs">{{ __('Technical Specifications') }}</button>@endif
        </div>

        <div class="tab-content active" id="tab-details">
            <div class="tab-pane-content">
                {!! trans_field($product, 'description') !!}
            </div>
        </div>

        @if($product->technical_specifications)
        <div class="tab-content" id="tab-specs">
            <div class="tab-pane-content">
                <table class="tech-specs-table">
                    <tbody>
                        @foreach($product->technical_specifications as $spec)
                            <tr>
                                <th>{{ app()->getLocale() === 'ar' ? ($spec['label_ar'] ?? $spec['label']) : $spec['label'] }}</th>
                                <td>{{ $spec['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    @if($related->count())
    <div class="related-products-section">
        <h2 class="related-header">{{ __('Related Products') }}</h2>
        <div class="products-grid">
            @foreach($related as $rp)
                @include('partials.product-card', ['product' => $rp])
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Quantity +/- is handled globally by main.js (.qty-btn.plus / .qty-btn.minus).
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab)?.classList.add('active');
        });
    });
});
</script>
@endsection
