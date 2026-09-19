@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => 'الأسئلة الشائعة', 'crumbs' => ['الأسئلة الشائعة' => null]])

<section class="section">
    <div class="container" style="max-width:850px;">
        @foreach($categories as $cat)
            <h3 style="margin:30px 0 15px;color:var(--primary);">{{ trans_field($cat, 'name') }}</h3>
            @foreach($cat->faqs as $faq)
                <details style="background:#fff;border-radius:10px;margin-bottom:12px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                    <summary style="cursor:pointer;font-weight:700;">{{ trans_field($faq, 'question') }}</summary>
                    <p style="margin-top:12px;color:var(--text-light);line-height:1.8;">{{ trans_field($faq, 'answer') }}</p>
                </details>
            @endforeach
        @endforeach
    </div>
</section>
@endsection
