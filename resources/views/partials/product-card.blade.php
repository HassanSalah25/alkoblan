@php
    $img = $product->mainImage?->media?->url ?? $product->galleryImages->first()?->media?->url ?? asset('images/pipe_product.jpg');
    $categorySlug = $product->category?->slug ?? '';
@endphp
<div class="product-card reveal" data-category="{{ $categorySlug }}" data-product-id="{{ $product->id }}">
    @if($product->is_on_sale)
        <div class="product-card-badge">{{ __('Sale') }}</div>
    @elseif($product->is_featured)
        <div class="product-card-badge green">{{ __('Featured') }}</div>
    @endif
    <div class="product-card-img">
        <img src="{{ $img }}" alt="{{ trans_field($product, 'name') }}">
        <div class="product-card-overlay">
            <a href="{{ route('products.show', $product->slug) }}" class="overlay-btn overlay-btn-primary">
                <i class="bi bi-eye"></i> {{ __('View Details') }}
            </a>
            <a href="#" class="overlay-btn overlay-btn-white" data-add-to-cart data-product-id="{{ $product->id }}">
                <i class="bi bi-cart-plus"></i>
            </a>
        </div>
    </div>
    <div class="product-card-body">
        <div class="product-card-category">{{ trans_field($product->category, 'name') }}</div>
        <h3 class="product-card-title">{{ trans_field($product, 'name') }}</h3>
        <p class="product-card-desc">{{ trans_field($product, 'short_description') }}</p>
        <div class="product-card-footer">
            <div>
                @if($product->effective_price > 0)
                    <span class="product-price">{{ number_format($product->effective_price, 0) }} <small>{{ $product->currency }}</small></span>
                    @if($product->is_on_sale)
                        <span class="product-price-label" style="text-decoration:line-through">{{ number_format($product->price, 0) }}</span>
                    @endif
                @else
                    <span class="product-price" style="font-size:0.95rem;">{{ __('Contact for Price') }}</span>
                @endif
            </div>
            <a href="#" class="cart-btn" data-add-to-cart data-product-id="{{ $product->id }}">
                <i class="bi bi-cart-plus"></i>
            </a>
        </div>
    </div>
</div>
