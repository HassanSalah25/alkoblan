<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — AL-KOBLAN</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg,#0f2a4a,#1a4a80); min-height: 100vh; display:flex; align-items:center; }
        .login-card { max-width: 420px; margin: 0 auto; border-radius: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card shadow-lg p-4">
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-0">AL-KOBLAN Admin</h4>
            <div class="text-muted small">Thermopipe Factory Administration Panel</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success small">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger small">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="{{ route('admin.password.request') }}" class="small">Forgot your password?</a>
        </div>
    </div>
</div>
</body>
</html>
