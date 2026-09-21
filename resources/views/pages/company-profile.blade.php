@extends('layouts.app')

@section('content')
<section class="profile-cover">
    <div class="profile-cover-content">
        <h1 class="profile-cover-title">ملف الشركة</h1>
        <p class="profile-cover-tagline">مصنع الكبلان ثيرموبايب - التميز في صناعة المواسير</p>
    </div>
</section>

<div class="profile-header">
    <div class="container">
        <ul class="profile-tabs">
            <li><a href="#overview" class="profile-tab active">نبذة عامة</a></li>
            <li><a href="#mission" class="profile-tab">الرسالة والرؤية</a></li>
            <li><a href="#products" class="profile-tab">منتجاتنا</a></li>
            <li><a href="#quality" class="profile-tab">الجودة وشهاداتنا</a></li>
            <li><a href="#branches" class="profile-tab">فروعنا</a></li>
            <li><a href="#contact" class="profile-tab">تواصل</a></li>
        </ul>
    </div>
</div>

<section id="overview" class="about-section section">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap reveal-left">
                <div class="about-image-main"><img src="{{ asset('images/factory_about.jpg') }}" alt="مصنع الكبلان"></div>
                <div class="about-image-badge"><span class="about-image-badge-number">30+</span><span class="about-image-badge-text">سنة خبرة</span></div>
            </div>
            <div class="about-content reveal-right">
                <div class="section-tag">نبذة عامة</div>
                <h2 class="section-title">شركة الكبلان ثيرموبايب <br><span class="color-primary">تاريخ من العراقة</span></h2>
                <p class="section-subtitle">نحن في مصنع الكبلان للمواسير الحرارية نفخر بكوننا الرواد في تصنيع شبكات المياه في المملكة العربية السعودية منذ عام {{ setting('founded_year','1995') }}. على مدار أكثر من ثلاثة عقود، قمنا بتزويد السوق المحلي والإقليمي بأجود أنواع المواسير والوصلات والصمامات التي تعتمد على أحدث التقنيات.</p>
                <p style="color: #666; margin-bottom: 20px; line-height: 1.8;">تتميز منتجاتنا بالتطابق التام مع المعايير الدولية والسعودية، مما يوفر لعملائنا في القطاعات السكنية والتجارية والصناعية حلولاً موثوقة ومستدامة. نلتزم دائماً بالابتكار، الجودة العالية، ورضا العملاء.</p>
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
                <span class="stat-label">سنة من التميز</span>
            </div>
            <div class="stat-item reveal delay-1">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-boxes"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="500">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">منتج مختلف</span>
            </div>
            <div class="stat-item reveal delay-2">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-people-fill"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="350">0</span>
                    <span class="stat-suffix">+</span>
                </div>
                <span class="stat-label">موظف وخبير</span>
            </div>
            <div class="stat-item reveal delay-3">
                <div class="stat-icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="stat-value">
                    <span class="stat-number" data-count="{{ $branches->count() }}">0</span>
                </div>
                <span class="stat-label">فروع رئيسية</span>
            </div>
        </div>
    </div>
</div>

<section id="mission" class="section dark-section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag" style="background: rgba(255,255,255,0.1); color:#fff;">أهدافنا</div>
            <h2 class="section-title reveal">الرسالة <span class="color-primary">والرؤية</span></h2>
        </div>
        <div class="mission-vision-grid">
            <div class="mv-card reveal delay-1">
                <i class="bi bi-bullseye mv-icon"></i>
                <h3 class="mv-title">رسالتنا</h3>
                <p>تقديم حلول شبكات مياه متكاملة ومبتكرة عالية الجودة لتلبية احتياجات عملائنا في مختلف القطاعات، مع الالتزام بأعلى معايير السلامة والاستدامة البيئية، والمساهمة في تطوير البنية التحتية للمملكة.</p>
            </div>
            <div class="mv-card reveal delay-2">
                <i class="bi bi-eye mv-icon"></i>
                <h3 class="mv-title">رؤيتنا</h3>
                <p>أن نكون الخيار الأول والشركة الرائدة في مجال صناعة المواسير الحرارية وملحقاتها في الشرق الأوسط، من خلال التميز المستمر والابتكار وبناء شراكات استراتيجية طويلة الأمد مع عملائنا وموردينا.</p>
            </div>
        </div>
    </div>
</section>

<section id="products" class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">منتجاتنا</div>
            <h2 class="section-title reveal">نظرة عامة على <span class="color-primary">المنتجات</span></h2>
        </div>
        <div class="value-cards">
            @foreach([['bi-water','مواسير المياه','مواسير PPR و HDPE متينة ومقاومة للضغط والحرارة العالية.','pipes'],['bi-puzzle-fill','الوصلات','وصلات متنوعة تضمن تدفقاً سلساً ومنعاً للتسرب.','fittings'],['bi-droplet-fill','الصمامات','صمامات تحكم دقيقة وعالية الموثوقية لعمر افتراضي طويل.','valves'],['bi-tools','الملحقات','جميع المستلزمات لتركيب ودعم شبكات المواسير باحترافية.','accessories']] as [$icon,$title,$desc,$catSlug])
                <a href="{{ route('shop.index', ['cat' => $catSlug]) }}" class="value-card reveal">
                    <i class="bi {{ $icon }}"></i>
                    <h4>{{ $title }}</h4>
                    <p class="text-muted">{{ $desc }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section id="quality" class="section">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">الجودة</div>
            <h2 class="section-title reveal">شهادات <span class="color-primary">الاعتماد</span></h2>
        </div>
        <div class="cert-grid">
            @foreach([['bi-award','ISO 9001:2015','نظام إدارة الجودة المعتمد دولياً لضمان كفاءة العمليات وتلبية متطلبات العملاء.'],['bi-check-circle-fill','شهادة SASO','مطابقة تامة للمواصفات والمقاييس السعودية المعتمدة من الهيئة العامة.'],['bi-globe-europe-africa','المعايير الأوروبية','المنتجات مصنعة وفق أحدث المواصفات الأوروبية لضمان الأداء الفائق.'],['bi-shield-check','شهادات اختبار مستقلة','اختبارات دورية من مختبرات محايدة لضمان قوة التحمل والسلامة الصحية.']] as [$icon,$title,$desc])
                <div class="cert-card reveal">
                    <i class="bi {{ $icon }} cert-icon"></i>
                    <h3 class="cert-title">{{ $title }}</h3>
                    <p class="cert-desc">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        <div class="section-header mt-50 mb-30">
            <h3 class="reveal">إمكانيات <span class="color-primary">المصنع</span></h3>
        </div>
        <div class="capabilities-grid">
            @foreach([['bi-gear-wide-connected','أحدث خطوط الإنتاج','نمتلك ماكينات ومعدات أوروبية متطورة تضمن دقة التصنيع وسرعة الإنتاج.'],['bi-search','مختبر جودة متكامل','مختبرات داخلية لفحص المواد الخام والمنتجات النهائية لضمان خلوها من العيوب.'],['bi-box-seam','طاقة إنتاجية ضخمة','قدرة على تلبية الطلبات الكبيرة للمشاريع الضخمة في أوقات قياسية.'],['bi-recycle','استدامة بيئية','عمليات تصنيع صديقة للبيئة تقليل الهدر واستهلاك الطاقة.']] as [$icon,$title,$desc])
                <div class="cap-item reveal">
                    <div class="cap-icon"><i class="bi {{ $icon }}"></i></div>
                    <div class="cap-content"><h4>{{ $title }}</h4><p>{{ $desc }}</p></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="branches" class="section" style="background: var(--light-gray);">
    <div class="container">
        <div class="section-header center">
            <div class="section-tag">تغطيتنا</div>
            <h2 class="section-title reveal">فروعنا في <span class="color-primary">المملكة</span></h2>
            <p class="section-subtitle reveal delay-1">نتواجد بالقرب منك لخدمتك بشكل أسرع وأفضل</p>
        </div>

        <div class="branch-grid">
            @foreach($branches as $branch)
                <div class="branch-card reveal">
                    <h4>{{ trans_field($branch, 'name') }}</h4>
                    <p><i class="bi bi-geo-fill"></i> <a href="{{ $branch->maps_url ?: google_maps_directions_url($branch->latitude, $branch->longitude, trans_field($branch, 'address')) }}" target="_blank" rel="noopener">{{ trans_field($branch, 'address') }}</a></p>
                    <p><i class="bi bi-telephone-fill"></i> <a href="tel:{{ $branch->phone }}" dir="ltr">{{ $branch->phone }}</a></p>
                    @if($branch->email)<p><i class="bi bi-envelope"></i> <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a></p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>

@if($catalog)
<section class="download-cta" style="background:var(--primary);color:#fff;text-align:center;padding:60px 0;">
    <div class="container">
        <h2 class="reveal" style="color:#fff;">احصل على النسخة الكاملة لملف الشركة</h2>
        <p class="reveal delay-1" style="margin:15px auto 30px;max-width:600px;opacity:0.9;">
            تصفح ملفنا التعريفي الشامل وقوائم الأسعار الرسمية لمعرفة المزيد عن منتجاتنا والتفاصيل الفنية.
        </p>
        <div style="display:flex;gap:15px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ $catalog->url }}" target="_blank" class="btn btn-outline-white btn-lg reveal delay-2" style="border-width:2px;">
                <i class="bi bi-file-earmark-pdf"></i> تحميل ملف الشركة (PDF)
            </a>
            @foreach($priceLists as $file)
                <a href="{{ $file->url }}" target="_blank" class="btn btn-outline-white btn-lg" style="border-width:2px;">
                    <i class="bi bi-receipt"></i> {{ $file->title }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section id="contact" style="padding:1px;"></section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.profile-tab');
    const sections = [...tabs].map(t => document.querySelector(t.getAttribute('href')));
    tabs.forEach(tab => tab.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelector(tab.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
    window.addEventListener('scroll', () => {
        let current = sections[0];
        sections.forEach(sec => { if (sec && window.scrollY >= sec.offsetTop - 150) current = sec; });
        tabs.forEach(t => t.classList.toggle('active', t.getAttribute('href') === '#' + current?.id));
    });
});
</script>
@endsection
