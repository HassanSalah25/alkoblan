@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => __('Shop'), 'crumbs' => [__('Shop') => null]])

<section class="section" style="background:var(--light-gray)">
    <div class="container">
        <button class="mobile-filter-toggle" id="filterToggle" onclick="document.getElementById('shopSidebar').classList.toggle('open')">
            <i class="bi bi-sliders"></i> {{ __('Filter Products') }}
        </button>
        <div class="shop-layout">
            <aside class="shop-sidebar" id="shopSidebar">
                <div class="sidebar-widget">
                    <h4 class="sidebar-widget-title">{{ __('Categories') }}</h4>
                    <ul class="category-list">
                        <li><a href="{{ route('shop.index') }}" class="{{ !$activeCategory ? 'active' : '' }}">{{ __('All') }} <span>{{ $categories->sum('products_count') }}</span></a></li>
                        @foreach($categories as $cat)
                            <li><a href="{{ route('shop.index', ['cat' => $cat->slug]) }}" class="{{ $activeCategory?->id === $cat->id ? 'active' : '' }}">{{ trans_field($cat, 'name') }} <span>{{ $cat->products_count }}</span></a></li>
                        @endforeach
                    </ul>
                </div>

                <form method="GET" action="{{ route('shop.index') }}">
                    @if($activeCategory)<input type="hidden" name="cat" value="{{ $activeCategory->slug }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif

                    <div class="sidebar-widget">
                        <h4 class="sidebar-widget-title">{{ __('Price Range') }}</h4>
                        <div class="price-range">
                            <input type="range" class="range-slider" name="max_price" min="0" max="1000" value="{{ $maxPrice }}" data-currency="{{ setting('default_currency') }}">
                            <div class="price-display">
                                <span>0 {{ setting('default_currency') }}</span>
                                <span class="price-max-display">{{ $maxPrice }} {{ setting('default_currency') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="sidebar-widget-title">{{ __('Size') }}</h4>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($diameters as $d)
                                <label style="cursor:pointer;padding:6px 14px;border:2px solid var(--border);border-radius:50px;font-size:0.82rem;transition:0.3s;{{ in_array($d->id, $selectedAttributes) ? 'border-color:var(--primary);color:var(--primary);' : '' }}">
                                    <input type="checkbox" name="attribute[]" value="{{ $d->id }}" {{ in_array($d->id, $selectedAttributes) ? 'checked' : '' }} style="display:none;">
                                    {{ $d->value }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="sidebar-widget-title">{{ __('Pressure') }}</h4>
                        <ul style="display:flex;flex-direction:column;gap:8px">
                            @foreach($pressures as $p)
                                <li><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem"><input type="checkbox" name="attribute[]" value="{{ $p->id }}" {{ in_array($p->id, $selectedAttributes) ? 'checked' : '' }}> {{ $p->value }}</label></li>
                            @endforeach
                        </ul>
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-10" style="justify-content:center">{{ __('Apply Filter') }}</button>
                        @if($activeCategory || $maxPrice < 1000 || count($selectedAttributes))
                            <a href="{{ route('shop.index', $activeCategory ? ['cat' => $activeCategory->slug] : []) }}" class="btn btn-outline btn-sm w-100 mt-10" style="justify-content:center">{{ __('Reset Filters') }}</a>
                        @endif
                    </div>
                </form>

                <div class="sidebar-widget" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;">
                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                        <i class="bi bi-telephone-fill" style="font-size:1.4rem;color:#fff"></i>
                    </div>
                    <h4 style="color:#fff;margin-bottom:8px">{{ __('Need Help?') }}</h4>
                    <p style="font-size:0.85rem;opacity:0.9;margin-bottom:16px">{{ __('Our sales team is ready to help') }}</p>
                    <a href="tel:{{ setting('contact_phone') }}" class="btn btn-outline-white btn-sm" style="justify-content:center;width:100%">
                        <i class="bi bi-telephone-fill"></i> {{ __('Call Us') }}
                    </a>
                </div>
            </aside>

            <div class="shop-content">
                <div class="shop-toolbar">
                    <div class="shop-result-count">
                        {{ __('Showing') }} <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> {{ __('of') }} <strong>{{ $products->total() }}</strong> {{ __('products') }}
                    </div>
                    <div class="shop-toolbar-right">
                        <div class="shop-sort">
                            <form method="GET" id="sortForm">
                                @if($activeCategory)<input type="hidden" name="cat" value="{{ $activeCategory->slug }}">@endif
                                <select name="sort" onchange="document.getElementById('sortForm').submit()">
                                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low to High') }}</option>
                                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('Price: High to Low') }}</option>
                                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('Name') }}</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    @forelse($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @empty
                        <p>{{ __('No products found.') }}</p>
                    @endforelse
                </div>

                @if($products->hasPages())
                <div class="pagination">
                    @if($products->onFirstPage())
                        <div class="page-num disabled"><i class="bi bi-chevron-right"></i></div>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="page-num"><i class="bi bi-chevron-right"></i></a>
                    @endif
                    @for($p = 1; $p <= $products->lastPage(); $p++)
                        <a href="{{ $products->url($p) }}" class="page-num {{ $p === $products->currentPage() ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="page-num"><i class="bi bi-chevron-left"></i></a>
                    @else
                        <div class="page-num disabled"><i class="bi bi-chevron-left"></i></div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
