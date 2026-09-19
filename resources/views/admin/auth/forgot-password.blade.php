<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — AL-KOBLAN Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg,#0f2a4a,#1a4a80); min-height: 100vh; display:flex; align-items:center; }
        .login-card { max-width: 420px; margin: 0 auto; border-radius: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card shadow-lg p-4">
        <h5 class="fw-bold text-center mb-3">Reset your password</h5>

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

        <form method="POST" action="{{ route('admin.password.email') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="{{ route('admin.login') }}" class="small">Back to login</a>
        </div>
    </div>
</div>
</body>
</html>
