<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — KopiPontianak</title>
    @vite('resources/css/app.css')
    <style>
        body{min-height:100vh;background:#f5f6fb;display:grid;place-items:center;padding:24px;color:#292c46}
        .login-card{width:min(420px,100%);background:#fff;border:1px solid #e9eaf1;border-radius:16px;padding:34px;box-shadow:0 18px 60px #30345b12}
        .login-brand{display:flex;align-items:center;gap:11px;margin-bottom:34px}.login-mark{width:40px;height:40px;border-radius:12px;background:#eceeff;color:#565bd5;display:grid;place-items:center;font-size:21px}.login-brand strong{font:800 20px Manrope,sans-serif;letter-spacing:-.7px}.login-brand small{display:block;font-size:8px;letter-spacing:1.5px;color:#a0a3b2;font-weight:700;margin-top:2px}
        .login-eyebrow{font-size:9px;letter-spacing:1.1px;color:#906245;font-weight:700}.login-card h1{font:700 25px Manrope,sans-serif;letter-spacing:-.7px;margin:8px 0}.login-intro{font-size:12px;color:#9194a4;line-height:1.6;margin:0 0 24px}.login-field{display:block;margin:15px 0}.login-field span{display:block;font-size:10px;font-weight:600;color:#5f6377;margin-bottom:7px}.login-field input{width:100%;height:42px;border:1px solid #e4e6ed;border-radius:7px;padding:0 11px;font:12px 'DM Sans',sans-serif;outline:0}.login-field input:focus{border-color:#a97756;box-shadow:0 0 0 3px #a977561c}.remember-row{display:flex;align-items:center;gap:7px;font-size:10px;color:#777b8d;margin:13px 0 18px}.login-submit{height:42px;width:100%;border:0;border-radius:7px;background:#79513a;color:white;font:600 11px 'DM Sans',sans-serif;cursor:pointer}.login-error{padding:10px 11px;border:1px solid #f0d7d7;border-radius:7px;background:#fff8f8;color:#ae5555;font-size:10px;margin-bottom:14px}.demo-note{margin-top:20px;padding:12px;border:1px solid #eceef4;border-radius:8px;background:#faf8f6;font-size:10px;color:#777b8e;line-height:1.7}.demo-note strong{display:block;color:#555970;margin-bottom:3px}.login-foot{text-align:center;margin:22px 0 0;font-size:9px;color:#a0a3b1}
    </style>
</head>
<body>
    <main class="login-card">
        <div class="login-brand"><span class="login-mark">☕</span><span><strong>Kopi<span style="color:#79513a">Pontianak</span></strong><small>COFFEE SHOP WEBGIS</small></span></div>
        <span class="login-eyebrow">KOTA PONTIANAK</span>
        <h1>Selamat datang</h1>
        <p class="login-intro">Masuk untuk mengelola data kafe dan warung kopi di Kota Pontianak.</p>
        @if ($errors->any())
            <div class="login-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label class="login-field"><span>Email</span><input name="email" type="email" autocomplete="username" value="{{ old('email') }}" placeholder="nama@contoh.id" required autofocus></label>
            <label class="login-field"><span>Kata sandi</span><input name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required></label>
            <label class="remember-row"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
            <button class="login-submit" type="submit">Masuk ke KopiPontianak</button>
        </form>
    </main>
</body>
</html>
