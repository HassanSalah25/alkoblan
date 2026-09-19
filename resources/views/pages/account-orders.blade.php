@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => 'طلباتي', 'crumbs' => ['طلباتي' => null]])

<section class="section">
    <div class="container">
        @if($orders->isEmpty())
            <p style="text-align:center;">لا توجد طلبات سابقة.</p>
        @else
            <div class="cart-table-wrap" style="background:#fff;border-radius:12px;overflow:hidden;">
                <table class="cart-table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:var(--light-gray);text-align:right;">
                            <th style="padding:15px;">رقم الطلب</th>
                            <th style="padding:15px;">التاريخ</th>
                            <th style="padding:15px;">الحالة</th>
                            <th style="padding:15px;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding:15px;">{{ $order->order_number }}</td>
                                <td style="padding:15px;">{{ $order->placed_at?->translatedFormat('d M Y') }}</td>
                                <td style="padding:15px;">{{ $order->status }}</td>
                                <td style="padding:15px;">{{ number_format($order->total, 0) }} {{ $order->currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;">{{ $orders->links() }}</div>
        @endif
    </div>
</section>
@endsection
