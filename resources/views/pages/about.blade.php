@extends('layouts.app')

@php
    $seoTitle = $page?->seo_title_ar ?: $page?->title_ar;
    $seoDescription = $page?->seo_description_ar;
@endphp

@section('content')
<section class="page-header" style="background: linear-gradient(135deg, rgba(26,26,46,0.9), rgba(192,57,43,0.8)), url('{{ asset('images/factory_about.jpg') }}') center/cover; padding: 100px 0; text-align: center; color: white;">
    <div class="container">
        <h1 style="font-size: 3rem; font-weight: 900; margin-bottom: 10px;color:white;">من نحن</h1>
        <p style="font-size: 1.2rem; opacity: 0.9;">عن مصنع الكبلان ثيرموبايب</p>
    </div>
</section>

<section class="about-section section">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap reveal-left">
                <div class="about-image-main"><img src="{{ asset('images/factory_about.jpg') }}" alt="مصنع الكبلان"></div>
                <div class="about-image-badge">
                    <span class="about-image-badge-number">{{ setting('founded_year', '1995') }}</span>
                    <span class="about-image-badge-text">التأسيس</span>
                </div>
            </div>
            <div class="about-content reveal-right">
                <div class="section-tag">قصتنا</div>
                <h2 class="section-title">مصنع الكبلان<br><span class="color-primary">ثيرموبايب KTP</span></h2>
                <p class="section-subtitle">
                    تأسس مصنع الكبلان للمواسير الحرارية KTP عام {{ setting('founded_year', '1995') }} كجزء من مجموعة الكبلان للتطوير والإنتاج. نفخر بكوننا رواد صناعة مواسير المياه في المملكة العربية السعودية، حيث نجمع بين الأصالة في العمل والتطور المستمر، مستخدمين أحدث التقنيات الأوروبية لنضمن لعملائنا جودة لا تُضاهى.
                </p>
                <div class="about-features">
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>الريادة في المملكة العربية السعودية</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>أعلى معايير الجودة العالمية</span></div>
                    <div class="about-feature"><i class="bi bi-check-lg"></i><span>تأسس عام {{ setting('founded_year', '1995') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">قيمنا</div>
            <h2 class="section-title reveal">القيم التي <span class="color-primary">نؤمن بها</span></h2>
        </div>
        <div class="categories-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            @foreach([['fa-trophy','الجودة'],['fa-lightbulb','الابتكار'],['fa-shield-halved','الموثوقية'],['fa-seedling','الاستدامة'],['fa-handshake','الشراكة'],['fa-star','التميز']] as [$icon, $label])
                <div class="category-card reveal" style="text-align:center; padding: 30px 15px;">
                    <div class="category-icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></div>
                    <h4>{{ $label }}</h4>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="mission" class="section" style="background: var(--dark); color: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div style="background: rgba(255,255,255,0.05); padding: 40px; border-radius: var(--radius); border-top: 4px solid var(--primary);">
                <i class="bi bi-bullseye" style="font-size: 3rem; color: var(--primary); margin-bottom: 20px;"></i>
                <h3 style="color: white; margin-bottom: 15px;">رسالتنا</h3>
                <p style="color: rgba(255,255,255,0.8); line-height: 1.8;">توفير حلول متكاملة ومبتكرة لشبكات المياه من خلال إنتاج مواسير وتجهيزات عالية الجودة، تلبي احتياجات عملائنا وتساهم في التنمية المستدامة.</p>
            </div>
            <div style="background: rgba(255,255,255,0.05); padding: 40px; border-radius: var(--radius); border-top: 4px solid var(--accent);">
                <i class="bi bi-eye" style="font-size: 3rem; color: var(--accent); margin-bottom: 20px;"></i>
                <h3 style="color: white; margin-bottom: 15px;">رؤيتنا</h3>
                <p style="color: rgba(255,255,255,0.8); line-height: 1.8;">أن نكون الخيار الأول والرائد في صناعة أنظمة الأنابيب في المملكة العربية السعودية والشرق الأوسط، وأن نضع معايير جديدة للجودة والابتكار.</p>
            </div>
        </div>
    </div>
</section>

<div class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item reveal">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="30">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">سنة في السوق</span>
            </div>
            <div class="stat-item reveal delay-1">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-boxes"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="500">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">منتج متنوع</span>
            </div>
            <div class="stat-item reveal delay-2">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-clipboard-check"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="10000">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">مشروع منجز</span>
            </div>
            <div class="stat-item reveal delay-3">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="3">0</span>
                </div>
                <span class="stat-label">فروع رئيسية</span>
            </div>
        </div>
    </div>
</div>

<section id="team" class="section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">فريقنا</div>
            <h2 class="section-title reveal">فريق <span class="color-primary">القيادة</span></h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px;">
            @foreach([['م','#c0392b','محمد الكبلان','المدير العام'],['أ','var(--accent)','أحمد عبدالله','مدير العمليات'],['خ','#2980b9','خالد عبدالرحمن','مدير الجودة'],['س','#8e44ad','سعد الدوسري','مدير المبيعات']] as [$initial, $color, $name, $role])
                <div style="text-align: center; background: var(--light-gray); padding: 30px; border-radius: var(--radius);">
                    <div style="width: 100px; height: 100px; background: {{ $color }}; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px; font-weight: 700;">{{ $initial }}</div>
                    <h4 style="margin-bottom: 5px;">{{ $name }}</h4>
                    <p style="color: var(--primary); font-weight: 600; font-size: 0.9rem;">{{ $role }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">مسيرتنا</div>
            <h2 class="section-title reveal">تاريخ من <span class="color-primary">النجاح</span></h2>
        </div>
        <div style="position: relative; max-width: 800px; margin: 0 auto; padding: 20px 0;">
            <div style="display: flex; flex-direction: column; gap: 30px;">
                @foreach([
                    ['1995','التأسيس','تأسيس مصنع الكبلان للمواسير الحرارية في مدينة الرياض.'],
                    ['2000','شهادة الآيزو','الحصول على أول شهادة ISO تقديراً لالتزامنا بمعايير الجودة العالمية.'],
                    ['2010','توسعة المصنع','افتتاح خطوط إنتاج جديدة وزيادة الطاقة الإنتاجية بأحدث المعدات الأوروبية.'],
                    ['2015','انتشار أوسع','افتتاح فروع جديدة في جدة والدمام لتغطية كافة مناطق المملكة.'],
                    ['2020','التصدير للخارج','البدء في تصدير منتجاتنا للأسواق الخليجية والعربية المجاورة.'],
                    ['2025','الاستدامة والابتكار','إطلاق منتجات جديدة صديقة للبيئة والوصول لأكثر من 500 منتج متنوع.'],
                ] as [$year, $title, $desc])
                    <div style="background: white; padding: 25px; border-radius: var(--radius); border-right: 4px solid var(--primary); box-shadow: var(--shadow);">
                        <h3 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 5px;">{{ $year }}</h3>
                        <h4 style="margin-bottom: 10px;">{{ $title }}</h4>
                        <p style="color: var(--text-muted);">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-inner">
            <h2 class="cta-title reveal">هل تحتاج إلى استشارة فنية مجانية؟</h2>
            <p class="cta-text reveal delay-1">فريقنا من المهندسين المتخصصين جاهز لمساعدتك في اختيار الحل الأمثل لشبكات المياه في مشروعك</p>
            <div class="cta-actions">
                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg"><i class="bi bi-telephone-fill"></i> تواصل الآن</a>
                <a href="{{ route('company-profile') }}" class="btn btn-outline-white btn-lg"><i class="bi bi-file-earmark-text"></i> ملف الشركة</a>
            </div>
        </div>
    </div>
</section>
@endsection
