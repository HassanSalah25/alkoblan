<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - {{ setting('site_name_ar') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body{margin:0;font-family:'Cairo',sans-serif;background:var(--dark,#1a1a2e);color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;}
        .error-icon{font-size:5rem;color:var(--primary,#c0392b);margin-bottom:10px;}
        h1{font-size:5rem;margin:0;}
        .actions{margin-top:25px;display:flex;gap:12px;justify-content:center;}
        .btn{padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;}
        .btn-primary{background:var(--primary,#c0392b);color:#fff;}
        .btn-outline{border:2px solid #fff;color:#fff;}
    </style>
</head>
<body>
    <div>
        <div class="error-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h1>404</h1>
        <p>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
        <div class="actions">
            <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house-fill"></i> الصفحة الرئيسية</a>
            <a href="{{ route('shop.index') }}" class="btn btn-outline"><i class="bi bi-bag-fill"></i> المتجر</a>
        </div>
    </div>
</body>
</html>
