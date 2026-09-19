@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => 'سلة المشتريات', 'crumbs' => ['السلة' => null]])

<section class="section" style="background:var(--light-gray)">
    <div class="container">
        <div class="cart-layout" style="display:grid;grid-template-columns:1fr 350px;gap:30px;align-items:flex-start;">
            <div id="cartItemsContainer">
                @if($items->isEmpty())
                    <div class="empty-cart" style="text-align:center;background:#fff;padding:60px 20px;border-radius:12px;">
                        <div class="empty-cart-icon" style="font-size:4rem;color:var(--border);margin-bottom:20px;"><i class="bi bi-cart3"></i></div>
                        <h2>سلتك فارغة!</h2>
                        <p>لم تقم بإضافة أي منتجات بعد، تصفح متجرنا واختر ما يناسبك.</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary"><i class="bi bi-bag-fill"></i> تصفح المنتجات</a>
                    </div>
                @else
                    <div class="cart-table-wrap" style="background:#fff;border-radius:12px;overflow:hidden;">
                        <table class="cart-table" style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:var(--light-gray);text-align:right;">
                                    <th style="padding:15px;">المنتج</th>
                                    <th style="padding:15px;">السعر</th>
                                    <th style="padding:15px;">الكمية</th>
                                    <th style="padding:15px;">الإجمالي</th>
                                    <th style="padding:15px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    @php $img = $item->product->mainImage?->media?->url ?? asset('images/pipe_product.jpg'); @endphp
                                    <tr style="border-top:1px solid var(--border);">
                                        <td class="cart-product-cell" style="padding:15px;display:flex;gap:12px;align-items:center;">
                                            <img src="{{ $img }}" alt="{{ trans_field($item->product, 'name') }}" class="cart-item-thumb" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                            <div>
                                                <div class="cart-item-name-big" style="font-weight:600;">{{ trans_field($item->product, 'name') }}</div>
                                                @if($item->variant)<div class="cart-item-cat" style="color:var(--text-light);font-size:0.85rem;">{{ $item->variant->label }}</div>@endif
                                            </div>
                                        </td>
                                        <td class="cart-price-cell" style="padding:15px;">{{ number_format($item->unit_price, 0) }} {{ setting('default_currency') }}</td>
                                        <td class="cart-qty-cell" style="padding:15px;">
                                            <form method="POST" action="{{ route('cart.update', $item->id) }}" style="display:flex;align-items:center;gap:6px;">
                                                @csrf @method('PATCH')
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="cart-qty-input" style="width:60px;padding:6px;border:1px solid var(--border);border-radius:6px;" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="cart-total-cell" style="padding:15px;font-weight:700;">{{ number_format($item->total, 0) }} {{ setting('default_currency') }}</td>
                                        <td class="cart-remove-cell" style="padding:15px;">
                                            <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="cart-remove-btn" style="background:none;border:none;color:var(--primary);cursor:pointer;font-size:1.1rem;"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($items->isNotEmpty())
            <div id="cartSummary">
                <div class="cart-summary-card" style="background:#fff;border-radius:12px;padding:25px;">
                    <h3 class="cart-summary-title">ملخص الطلب</h3>
                    <div class="cart-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;"><span>المجموع الفرعي</span><span>{{ number_format($subtotal, 0) }} {{ setting('default_currency') }}</span></div>
                    <div class="cart-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;"><span>ضريبة القيمة المضافة ({{ $tax_rate }}%)</span><span>{{ number_format($tax, 0) }} {{ setting('default_currency') }}</span></div>
                    <div class="cart-summary-row" style="display:flex;justify-content:space-between;padding:8px 0;"><span>الشحن</span><span class="text-success" style="color:#27ae60">مجاني</span></div>
                    <div class="cart-summary-total" style="display:flex;justify-content:space-between;padding:12px 0;border-top:1px solid var(--border);font-weight:700;font-size:1.1rem;"><span>الإجمالي</span><span>{{ number_format($total, 0) }} {{ setting('default_currency') }}</span></div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100" style="justify-content:center;margin-top:20px;padding:16px"><i class="bi bi-credit-card"></i> إتمام الطلب</a>
                    <a href="{{ route('shop.index') }}" class="btn btn-outline w-100" style="justify-content:center;margin-top:10px"><i class="bi bi-arrow-right"></i> متابعة التسوق</a>
                    <form method="POST" action="{{ route('cart.clear') }}" style="margin-top:10px;">
                        @csrf @method('DELETE')
                        <button type="submit" class="cart-clear-btn" style="width:100%;background:none;border:1px dashed var(--border);border-radius:8px;padding:10px;cursor:pointer;color:var(--text-light);"><i class="bi bi-trash3"></i> إفراغ السلة</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
