@extends('layouts.app')

@section('content')
<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">إتمام <span>الطلب</span></h1>
        <p class="page-hero-subtitle">أكمل بيانات الشحن والدفع لإنهاء طلبك</p>
        <nav class="breadcrumb-nav">
            <a href="{{ url('/') }}">الرئيسية</a>
            <i class="bi bi-chevron-left" style="font-size:10px"></i>
            <a href="{{ route('cart.index') }}">السلة</a>
            <i class="bi bi-chevron-left" style="font-size:10px"></i>
            <span class="active">إتمام الطلب</span>
        </nav>
    </div>
</div>

<section class="section" style="background:var(--light-gray)">
    <div class="container">
        <form method="POST" action="{{ route('checkout.store') }}">
        @csrf
        <div class="checkout-layout">
            <div>
                <div class="checkout-card">
                    <div class="checkout-card-title"><span class="step-num">1</span> <i class="bi bi-person-fill"></i> معلومات التواصل</div>
                    <div class="form-row-2">
                        <div class="checkout-form-group">
                            <label class="checkout-label">الاسم الأول *</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" class="checkout-input" placeholder="محمد" required>
                        </div>
                        <div class="checkout-form-group">
                            <label class="checkout-label">اسم العائلة *</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" class="checkout-input" placeholder="الكبلان" required>
                        </div>
                    </div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">البريد الإلكتروني *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="checkout-input" placeholder="example@email.com" required>
                    </div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">رقم الجوال *</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="checkout-input" placeholder="05XXXXXXXX" required>
                    </div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">اسم الشركة (اختياري)</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="checkout-input" placeholder="اسم الشركة">
                    </div>
                </div>

                <div class="checkout-card">
                    <div class="checkout-card-title"><span class="step-num">2</span> <i class="bi bi-geo-alt-fill"></i> عنوان الشحن</div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">المدينة *</label>
                        <select name="city" class="checkout-select" required>
                            <option value="">اختر المدينة...</option>
                            @foreach(['riyadh'=>'الرياض','jeddah'=>'جدة','dammam'=>'الدمام','mecca'=>'مكة المكرمة','medina'=>'المدينة المنورة','khobar'=>'الخبر','tabuk'=>'تبوك','other'=>'مدينة أخرى'] as $val => $label)
                                <option value="{{ $val }}" {{ old('city') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row-2">
                        <div class="checkout-form-group">
                            <label class="checkout-label">الحي</label>
                            <input type="text" name="district" value="{{ old('district') }}" class="checkout-input" placeholder="الحي">
                        </div>
                        <div class="checkout-form-group">
                            <label class="checkout-label">الرمز البريدي</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="checkout-input" placeholder="12345">
                        </div>
                    </div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">العنوان التفصيلي *</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="checkout-input" placeholder="اسم الشارع، رقم المبنى..." required>
                    </div>
                    <div class="checkout-form-group">
                        <label class="checkout-label">ملاحظات للتوصيل (اختياري)</label>
                        <textarea name="notes" class="checkout-input" rows="2" placeholder="أي تعليمات خاصة للتوصيل...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="checkout-card">
                    <div class="checkout-card-title"><span class="step-num">3</span> <i class="bi bi-credit-card"></i> طريقة الدفع</div>
                    <div class="payment-options">
                        <label class="payment-option active" data-method="cash">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <span class="payment-option-icon"><i class="bi bi-cash-coin"></i></span>
                            <div><div class="payment-option-label">الدفع نقداً عند الاستلام</div><div class="payment-option-sub">دفع مباشر عند تسليم الطلب</div></div>
                        </label>
                        <label class="payment-option" data-method="bank_transfer">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <span class="payment-option-icon"><i class="bi bi-bank"></i></span>
                            <div><div class="payment-option-label">تحويل بنكي</div><div class="payment-option-sub">تحويل مباشر لحساب الشركة</div></div>
                        </label>
                    </div>
                    <div class="bank-details" id="bankDetails">
                        <p><strong>البنك:</strong> بنك الراجحي</p>
                        <p><strong>اسم الحساب:</strong> مصنع الكبلان للمواسير الحرارية</p>
                        <p><strong>رقم الحساب:</strong> 123456789012</p>
                        <p><strong>الآيبان:</strong> SA0000000000000000000000</p>
                    </div>
                </div>
            </div>

            <div class="checkout-summary-sticky">
                <div class="checkout-card">
                    <h3 style="margin-bottom:20px;">ملخص الطلب</h3>
                    <div id="checkoutOrderSummary">
                        @foreach($items as $item)
                            <div class="checkout-order-item" style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
                                <img src="{{ $item->product->mainImage?->media?->url ?? asset('images/pipe_product.jpg') }}" class="checkout-item-img" style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                                <div class="checkout-item-info" style="flex:1;">
                                    <div class="checkout-item-name">{{ trans_field($item->product, 'name') }}</div>
                                    <div class="checkout-item-qty" style="color:var(--text-light);font-size:0.85rem;">× {{ $item->quantity }}</div>
                                </div>
                                <div class="checkout-item-price">{{ number_format($item->total, 0) }} {{ setting('default_currency') }}</div>
                            </div>
                        @endforeach
                        <div class="checkout-summary-divider" style="border-top:1px solid var(--border);margin:15px 0;"></div>
                        <div class="checkout-summary-row" style="display:flex;justify-content:space-between;padding:5px 0;"><span>المجموع الفرعي</span><span>{{ number_format($subtotal, 0) }} {{ setting('default_currency') }}</span></div>
                        <div class="checkout-summary-row" style="display:flex;justify-content:space-between;padding:5px 0;"><span>ضريبة {{ $tax_rate }}%</span><span>{{ number_format($tax, 0) }} {{ setting('default_currency') }}</span></div>
                        <div class="checkout-summary-row" style="display:flex;justify-content:space-between;padding:5px 0;"><span>الشحن</span><span style="color:#27ae60">مجاني</span></div>
                        <div class="checkout-summary-total" style="display:flex;justify-content:space-between;padding:12px 0;font-weight:700;font-size:1.1rem;border-top:1px solid var(--border);margin-top:10px;"><span>الإجمالي</span><span>{{ number_format($total, 0) }} {{ setting('default_currency') }}</span></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="justify-content:center;margin-top:20px;padding:16px;"><i class="bi bi-check-circle-fill"></i> تأكيد الطلب</button>
                    <div class="checkout-secure-note"><i class="bi bi-shield-check"></i> دفع آمن وبياناتك محمية بالكامل</div>
                </div>
            </div>
        </div>
        </form>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const options = document.querySelectorAll('.payment-option');
    const bankDetails = document.getElementById('bankDetails');
    function sync() {
        options.forEach(o => o.classList.toggle('active', o.querySelector('input').checked));
        bankDetails.classList.toggle('show', document.querySelector('[name=payment_method]:checked')?.value === 'bank_transfer');
    }
    options.forEach(o => o.querySelector('input').addEventListener('change', sync));
    sync();
});
</script>
@endsection
