@extends('layouts.auth')

@section('title', $activeTab === 'register' ? 'إنشاء حساب' : 'تسجيل الدخول')

@section('content')
<div class="auth-section">
    <div class="auth-card">
        <div class="auth-card-header">
            <a href="{{ url('/') }}" style="text-decoration:none;display:inline-block;margin-bottom:15px;">
                <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}" style="height:42px;max-width:220px;object-fit:contain;">
            </a>
            <h2>مرحباً بك</h2>
            <p>سجل دخولك للوصول إلى حسابك وإدارة طلباتك</p>
        </div>

        <div class="auth-card-body">
            @if($errors->any())
                <div style="background:#fdecea;color:#c0392b;padding:12px;border-radius:8px;margin-bottom:15px;">
                    <ul style="margin:0;padding-right:18px;">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="auth-tabs">
                <div class="auth-tab {{ $activeTab !== 'register' ? 'active' : '' }}" data-tab="loginForm">تسجيل الدخول</div>
                <div class="auth-tab {{ $activeTab === 'register' ? 'active' : '' }}" data-tab="registerForm">إنشاء حساب</div>
            </div>

            <form id="loginForm" class="auth-form {{ $activeTab !== 'register' ? 'active' : '' }}" method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="أدخل بريدك الإلكتروني" required>
                </div>
                <div class="form-group">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" placeholder="أدخل كلمة المرور" required>
                </div>
                <div class="remember-row">
                    <label class="remember-check">
                        <input type="checkbox" name="remember" checked>
                        <span>تذكرني</span>
                    </label>
                </div>
                <button type="submit" class="btn-primary w-100">تسجيل الدخول</button>
            </form>

            <form id="registerForm" class="auth-form {{ $activeTab === 'register' ? 'active' : '' }}" method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">الاسم الأول</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control" placeholder="الاسم الأول" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الاسم الأخير</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-control" placeholder="الاسم الأخير" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="أدخل بريدك الإلكتروني" required>
                </div>
                <div class="form-group">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="رقم الجوال" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" placeholder="كلمة المرور" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="تأكيد كلمة المرور" required>
                    </div>
                </div>
                <div class="remember-row" style="margin-bottom: 25px;">
                    <label class="remember-check">
                        <input type="checkbox" required>
                        <span>أوافق على الشروط والأحكام وسياسة الخصوصية</span>
                    </label>
                </div>
                <button type="submit" class="btn-primary w-100">إنشاء حساب</button>
            </form>
        </div>
    </div>
</div>

<div class="minimal-footer">
    <p>{{ setting('copyright_text_ar') }}</p>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.auth-tab');
    const forms = document.querySelectorAll('.auth-form');
    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        forms.forEach(f => f.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab)?.classList.add('active');
    }));
});
</script>
@endsection
