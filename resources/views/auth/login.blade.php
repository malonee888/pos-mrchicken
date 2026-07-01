<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MR. CHICKEN — Login</title>
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div id="login-screen">
  <div class="login-card">
    <div class="login-brand">
      <div class="login-logo">🐔</div>
      <div>
        <div class="login-brand-name">MR. CHICKEN</div>
        <div class="login-brand-sub">POS & DISTRIBUTION SYSTEM</div>
      </div>
    </div>
    <div class="login-title">Masuk ke Akun Anda</div>
    <div class="login-sub">Silakan masukkan username dan password Anda</div>

    @if ($errors->any())
      <div class="alert alert-error" style="margin-bottom:18px;">
        <span class="alert-icon">⚠️</span>
        <div class="alert-body">
          <div class="alert-title">Login Gagal</div>
          {{ $errors->first() }}
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-input" id="l-user" name="username" type="text" placeholder="Masukkan username" value="{{ old('username') }}" autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-input" id="l-pass" name="password" type="password" placeholder="Masukkan password">
      </div>
      <button class="btn-login" type="submit">Masuk ke Sistem →</button>
    </form>
  </div>
</div>

</body>
</html>