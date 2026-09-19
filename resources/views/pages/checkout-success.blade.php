@extends('layouts.app')

@section('content')
<section class="section" style="min-height:60vh;display:flex;align-items:center;">
    <div class="container" style="text-align:center;max-width:600px;">
        <div style="font-size:5rem;color:#27ae60;margin-bottom:20px;"><i class="bi bi-check-circle-fill"></i></div>
        <h1>تم استلام طلبك بنجاح!</h1>
        <p style="color:var(--text-light);margin:15px 0 25px;">
            رقم طلبك هو <strong>{{ $order->order_number }}</strong>. سيقوم فريقنا بالتواصل معك قريباً لتأكيد التفاصيل.
        </p>
        <div style="background:#fff;border-radius:12px;padding:25px;text-align:right;margin-bottom:25px;">
            @foreach($order->items as $item)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">
                    <span>{{ $item->product_name }} × {{ $item->quantity }}</span>
                    <span>{{ number_format($item->total, 0) }} {{ $order->currency }}</span>
                </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;padding-top:12px;font-weight:700;">
                <span>الإجمالي</span>
                <span>{{ number_format($order->total, 0) }} {{ $order->currency }}</span>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:center;">
            <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house-fill"></i> الصفحة الرئيسية</a>
            <a href="{{ route('shop.index') }}" class="btn btn-outline"><i class="bi bi-bag-fill"></i> متابعة التسوق</a>
        </div>
    </div>
</section>
@endsection
