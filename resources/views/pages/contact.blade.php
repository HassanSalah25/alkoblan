@extends('layouts.app')

@section('content')
@include('partials.page-hero', ['title' => 'تواصل معنا', 'crumbs' => ['تواصل معنا' => null]])

<section class="section">
    <div class="container">
        @if(session('success'))
            <div class="alert" style="background:#d4edda;color:#155724;padding:15px;border-radius:8px;margin-bottom:25px;">{{ session('success') }}</div>
        @endif
        <div class="contact-grid">
            <div class="contact-info reveal-left">
                <h3>معلومات التواصل</h3>
                <p style="margin-bottom: 25px; color: var(--gray);">يسعدنا تواصلكم معنا للرد على استفساراتكم وتلبية احتياجاتكم من خلال قنوات التواصل المختلفة أو بزيارة فروعنا.</p>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="contact-info-text">
                        <strong>فروعنا الرئيسية</strong>
                        <span>{{ $branches->pluck('name_ar')->implode(' | ') }}</span>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
                    <div class="contact-info-text">
                        <strong>الهاتف الموحد</strong>
                        <a href="tel:{{ setting('contact_phone') }}" dir="ltr" style="display:inline-block">{{ setting('contact_phone') }}</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-envelope"></i></div>
                    <div class="contact-info-text">
                        <strong>البريد الإلكتروني</strong>
                        <a href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="bi bi-clock"></i></div>
                    <div class="contact-info-text">
                        <strong>ساعات العمل</strong>
                        <span>{!! wrap_ltr_time_ranges(setting('working_hours_ar')) !!}</span>
                    </div>
                </div>
            </div>

            <div class="form-card reveal-right">
                <h3 style="margin-bottom: 25px;">أرسل رسالة</h3>
                <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">الاسم الكامل <span class="required" style="color:var(--primary)">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required placeholder="أدخل اسمك الكامل">
                        @error('name')<small style="color:var(--primary)">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني <span class="required" style="color:var(--primary)">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required placeholder="example@domain.com">
                            @error('email')<small style="color:var(--primary)">{{ $message }}</small>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="05xxxxxxxx">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">موضوع الرسالة</label>
                        <select name="subject" class="form-control">
                            <option value="">اختر الموضوع...</option>
                            <option value="sales">استفسار مبيعات</option>
                            <option value="support">دعم فني</option>
                            <option value="complaint">شكوى أو اقتراح</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">الرسالة <span class="required" style="color:var(--primary)">*</span></label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="اكتب رسالتك هنا...">{{ old('message') }}</textarea>
                        @error('message')<small style="color:var(--primary)">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="justify-content:center">
                        إرسال الرسالة <i class="bi bi-send-fill" style="margin-right: 8px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">شبكة الفروع</div>
            <h2 class="section-title reveal">فروعنا <span class="color-primary">في المملكة</span></h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            @foreach($branches as $branch)
                <div class="branch-card reveal" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                    <div style="font-size: 2rem; color: var(--primary); margin-bottom: 15px;"><i class="bi bi-building"></i></div>
                    <h3 style="margin-bottom: 15px;">{{ trans_field($branch, 'name') }}</h3>
                    <ul class="footer-contact-list" style="color: var(--text); list-style: none; padding: 0;">
                        <li style="margin-bottom: 10px; display: flex; gap: 10px;"><i class="bi bi-geo-alt-fill" style="color: var(--primary); margin-top: 4px;"></i> <a href="{{ $branch->maps_url ?: google_maps_directions_url($branch->latitude, $branch->longitude, trans_field($branch, 'address')) }}" target="_blank" rel="noopener">{{ trans_field($branch, 'address') }}</a></li>
                        <li style="margin-bottom: 10px; display: flex; gap: 10px;"><i class="bi bi-telephone-fill" style="color: var(--primary); margin-top: 4px;"></i> <a href="tel:{{ $branch->phone }}" dir="ltr">{{ $branch->phone }}</a></li>
                        <li style="display: flex; gap: 10px;"><i class="bi bi-clock" style="color: var(--primary); margin-top: 4px;"></i> <span>{!! wrap_ltr_time_ranges(trans_field($branch, 'working_hours')) !!}</span></li>
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

@php
    $primaryBranch = $branches->sortBy('sort_order')->first();
    $mapsEmbedUrl = google_maps_embed_url(
        setting('google_maps_embed'),
        $primaryBranch?->latitude !== null ? (float) $primaryBranch->latitude : null,
        $primaryBranch?->longitude !== null ? (float) $primaryBranch->longitude : null
    );
@endphp
<section style="height: 450px; width: 100%; display: flex;">
    <iframe src="{{ $mapsEmbedUrl }}" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('Contact') }} — Google Maps"></iframe>
</section>
@endsection
