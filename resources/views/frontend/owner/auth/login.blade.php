<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Owner Login — BigWein</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}"/>
<style>
body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);padding:20px;}
.login-wrap{width:100%;max-width:440px;}
.login-card{background:#fff;border-radius:var(--r-xl);padding:40px 36px;box-shadow:var(--shadow-lg);}
.login-logo{text-align:center;margin-bottom:28px;}
.login-logo .logo-box{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px;}
.login-logo h2{font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:900;color:var(--navy);}
.login-logo p{font-size:13px;color:var(--gray);}
.pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray2);cursor:pointer;font-size:14px;}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-box">
        <img src="{{ url('images/Logo.jpeg') }}" alt="Bigwein" style="height:60px;object-fit:contain;"/>
      </div>
      <h2>Owner Login</h2>
      <p>Access your property dashboard</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success" style="margin-bottom:16px;"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-error" style="margin-bottom:16px;"><i class="fa-solid fa-circle-xmark"></i> @foreach($errors->all() as $e){{ $e }} @endforeach</div>
    @endif

    <form method="POST" action="{{ url('/owner/login') }}">
      @csrf
      <div class="f-group" style="margin-bottom:16px;">
        <label class="f-label">Email Address</label>
        <div class="f-wrap"><i class="fa-solid fa-envelope"></i>
        <input class="f-input" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus/>
        </div>
      </div>
      <div class="f-group" style="margin-bottom:22px;">
        <label class="f-label">Password</label>
        <div class="f-wrap" style="position:relative;"><i class="fa-solid fa-lock"></i>
        <input class="f-input" type="password" name="password" placeholder="Your password" required id="loginPass"/>
        <button type="button" class="pw-toggle" onclick="togglePw()"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>
      <button type="submit" class="btn btn-red" style="width:100%;justify-content:center;padding:12px;">
        <i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard
      </button>
    </form>
    <div style="text-align:center;margin-top:20px;font-size:13px;color:var(--gray);">
      New owner? <a href="{{ url('/owner/register') }}" style="color:var(--red);font-weight:700;">Create an account</a>
    </div>
    <div style="text-align:center;margin-top:8px;font-size:12px;color:var(--gray2);">
      <a href="{{ url('/user/login') }}" style="color:var(--gray2);">Buyer/User Login →</a>
    </div>
  </div>
</div>
<script>
function togglePw() {
    const i = document.getElementById('loginPass');
    i.type = i.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
