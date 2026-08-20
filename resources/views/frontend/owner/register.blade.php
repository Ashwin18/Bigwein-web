<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Register as Property Owner — BigWein</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --red:#E5343A;--red-d:#C4272D;--red-light:rgba(229,52,58,.08);
  --navy:#0F172A;--navy2:#1E293B;--gray:#64748B;--gray2:#94A3B8;
  --border:#E2E8F0;--bg:#F8FAFC;--white:#fff;
  --shadow:0 4px 24px rgba(15,23,42,.08);--shadow-lg:0 16px 48px rgba(15,23,42,.14);
  --r:12px;--r-lg:18px;--r-xl:24px;
}
html{scroll-behavior:smooth;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--navy);min-height:100vh;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* TOPBAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--shadow);}
.logo{font-family:'Plus Jakarta Sans',sans-serif;font-size:24px;font-weight:800;}
.logo .big{color:var(--red);}
.logo .wein{color:var(--navy);}
.logo-tag{font-size:10px;color:var(--gray2);font-weight:400;display:block;letter-spacing:.3px;margin-top:-4px;}
.topbar-right{font-size:13px;color:var(--gray);}
.topbar-right a{color:var(--red);font-weight:600;}

/* PROGRESS */
.progress-wrap{background:var(--white);border-bottom:1px solid var(--border);padding:20px 32px;}
.progress-steps{display:flex;align-items:center;max-width:720px;margin:0 auto;position:relative;}
.prog-line{flex:1;height:2px;background:var(--border);position:relative;transition:background .4s;}
.prog-line.done{background:var(--red);}
.prog-step{display:flex;flex-direction:column;align-items:center;gap:6px;flex-shrink:0;position:relative;z-index:1;}
.prog-dot{width:34px;height:34px;border-radius:50%;border:2px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--gray2);transition:all .35s;}
.prog-step.active .prog-dot{border-color:var(--red);background:var(--red);color:#fff;box-shadow:0 0 0 4px rgba(229,52,58,.15);}
.prog-step.done .prog-dot{border-color:var(--red);background:var(--red);color:#fff;}
.prog-label{font-size:11px;font-weight:600;color:var(--gray2);white-space:nowrap;transition:color .35s;}
.prog-step.active .prog-label{color:var(--red);}
.prog-step.done .prog-label{color:var(--gray);}

/* WRAPPER */
.page-wrap{max-width:960px;margin:0 auto;padding:40px 24px 80px;}

/* STEP CARD */
.step{display:none;animation:fadeUp .4s ease;}
.step.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}

/* STEP HEADER */
.step-header{text-align:center;margin-bottom:36px;}
.step-badge{display:inline-flex;align-items:center;gap:6px;background:var(--red-light);color:var(--red);padding:6px 16px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:14px;}
.step-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:30px;font-weight:800;color:var(--navy);margin-bottom:8px;line-height:1.2;}
.step-title span{color:var(--red);}
.step-sub{font-size:15px;color:var(--gray);line-height:1.6;}

/* OWNER TYPE CARDS */
.owner-types{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px;}
.owner-card{border:2px solid var(--border);border-radius:var(--r-lg);padding:24px 20px;cursor:pointer;transition:all .25s;position:relative;background:var(--white);text-align:center;}
.owner-card:hover{border-color:var(--red);box-shadow:0 4px 20px rgba(229,52,58,.12);}
.owner-card.selected{border-color:var(--red);background:var(--red-light);box-shadow:0 4px 20px rgba(229,52,58,.15);}
.owner-card .oc-check{position:absolute;top:12px;right:12px;width:22px;height:22px;border-radius:50%;border:2px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.owner-card.selected .oc-check{background:var(--red);border-color:var(--red);}
.owner-card.selected .oc-check::after{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;font-size:10px;color:#fff;}
.oc-icon{width:60px;height:60px;border-radius:16px;background:rgba(229,52,58,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;transition:background .25s;}
.owner-card.selected .oc-icon{background:rgba(229,52,58,.18);}
.oc-icon i{font-size:26px;color:var(--red);}
.oc-title{font-size:16px;font-weight:700;color:var(--navy);margin-bottom:4px;}
.oc-desc{font-size:12px;color:var(--gray);line-height:1.5;}

/* FORM */
.reg-form{background:var(--white);border-radius:var(--r-xl);padding:32px;box-shadow:var(--shadow);}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group.full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;}
.form-required{color:var(--red);}
.form-input-wrap{position:relative;}
.form-input-wrap>i:first-child{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray2);font-size:14px;pointer-events:none;}
.eye-toggle{position:absolute!important;left:auto!important;right:14px!important;cursor:pointer!important;transition:color .2s;transform:translateY(-50%)!important;top:50%!important;}
.eye-toggle:hover{color:var(--red)!important;}
.form-input{width:100%;padding:12px 14px 12px 40px;border:1.5px solid var(--border);border-radius:var(--r);font-size:14px;font-family:'Poppins',sans-serif;color:var(--navy);outline:none;transition:border-color .2s,box-shadow .2s;background:var(--white);}
.form-input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(229,52,58,.1);}
.form-input.no-icon{padding-left:14px;}
.form-input::placeholder{color:var(--gray2);}
.phone-wrap{display:flex;gap:8px;}
.phone-code{width:90px;flex-shrink:0;}
.form-hint{font-size:11px;color:var(--gray2);margin-top:2px;}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 28px;border-radius:var(--r);font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;cursor:pointer;border:none;transition:all .22s;text-decoration:none;}
.btn-primary{background:var(--red);color:#fff;box-shadow:0 4px 16px rgba(229,52,58,.3);}
.btn-primary:hover{background:var(--red-d);transform:translateY(-1px);}
.btn-outline{background:var(--white);color:var(--red);border:2px solid var(--red);}
.btn-outline:hover{background:var(--red-light);}
.btn-full{width:100%;}
.btn-lg{padding:16px 32px;font-size:16px;}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none!important;}
.terms-note{font-size:12px;color:var(--gray);text-align:center;margin-top:16px;line-height:1.7;}
.terms-note a{color:var(--red);font-weight:600;}

/* OTP */
.otp-card{background:var(--white);border-radius:var(--r-xl);padding:40px;box-shadow:var(--shadow);max-width:440px;margin:0 auto;text-align:center;}
.otp-icon{width:80px;height:80px;background:var(--red-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;}
.otp-icon i{font-size:32px;color:var(--red);}
.otp-email-highlight{display:inline-flex;align-items:center;gap:6px;background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:8px 16px;font-size:13px;font-weight:600;color:#166534;margin:12px 0 24px;}
.otp-inputs{display:flex;gap:10px;justify-content:center;margin:20px 0 8px;}
.otp-box{width:52px;height:60px;border:2px solid var(--border);border-radius:var(--r);text-align:center;font-size:22px;font-weight:800;color:var(--navy);font-family:'Plus Jakarta Sans',sans-serif;outline:none;transition:all .2s;background:var(--bg);}
.otp-box:focus{border-color:var(--red);background:var(--white);box-shadow:0 0 0 3px rgba(229,52,58,.1);}
.otp-box.filled{border-color:var(--red);background:var(--red-light);}
.otp-timer{font-size:13px;color:var(--gray);margin-bottom:20px;}
.otp-timer span{color:var(--red);font-weight:700;}
.resend-link{background:none;border:none;color:var(--gray2);font-size:13px;cursor:not-allowed;font-family:'Poppins',sans-serif;}
.resend-link.active{color:var(--red);cursor:pointer;font-weight:600;}

/* SUCCESS */
.success-card{background:var(--white);border-radius:var(--r-xl);padding:48px 40px;box-shadow:var(--shadow);max-width:520px;margin:0 auto;text-align:center;}
.success-anim{width:100px;height:100px;background:linear-gradient(135deg,#DCFCE7,#BBF7D0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;animation:pop .5s cubic-bezier(.175,.885,.32,1.275);}
@keyframes pop{0%{transform:scale(0);}100%{transform:scale(1);}}
.success-anim i{font-size:42px;color:#16A34A;}
.success-title{font-family:'Plus Jakarta Sans',sans-serif;font-size:26px;font-weight:800;color:var(--navy);margin-bottom:8px;}
.success-sub{font-size:15px;color:var(--gray);line-height:1.7;margin-bottom:12px;}
.owner-type-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(229,52,58,.08);border:1px solid rgba(229,52,58,.2);border-radius:20px;padding:6px 16px;font-size:13px;font-weight:700;color:var(--red);margin-bottom:28px;}
.success-features{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:32px;text-align:left;}
.feat-item{display:flex;align-items:flex-start;gap:10px;padding:14px;background:var(--bg);border-radius:var(--r);border:1px solid var(--border);}
.feat-item i{font-size:16px;color:var(--red);margin-top:1px;flex-shrink:0;}
.feat-item div b{font-size:13px;font-weight:700;color:var(--navy);display:block;}
.feat-item div span{font-size:12px;color:var(--gray);}

/* PROP TYPE */
.prop-type-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:32px;}
.pt-card{border:2px solid var(--border);border-radius:var(--r-xl);padding:28px 20px;cursor:pointer;transition:all .28s;background:var(--white);text-align:center;}
.pt-card:hover{border-color:var(--red);transform:translateY(-4px);box-shadow:0 12px 32px rgba(229,52,58,.18);}
.pt-card.selected{border-color:var(--red);background:var(--red);}
.pt-card.selected .pt-icon{background:rgba(255,255,255,.2);}
.pt-card.selected .pt-icon i,.pt-card.selected .pt-title{color:#fff;}
.pt-card.selected .pt-desc{color:rgba(255,255,255,.75);}
.pt-icon{width:68px;height:68px;border-radius:18px;background:var(--red-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;}
.pt-icon i{font-size:28px;color:var(--red);}
.pt-title{font-size:17px;font-weight:800;color:var(--navy);margin-bottom:5px;font-family:'Plus Jakarta Sans',sans-serif;}
.pt-desc{font-size:12px;color:var(--gray);line-height:1.5;margin-bottom:12px;}
.pt-tag{display:inline-flex;gap:4px;background:var(--bg);border-radius:8px;padding:4px 10px;font-size:11px;font-weight:600;color:var(--gray);}

/* AD TYPE */
.ad-type-header{background:var(--white);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:24px;box-shadow:var(--shadow);display:flex;align-items:center;gap:16px;}
.at-icon{width:52px;height:52px;border-radius:14px;background:var(--red-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.at-icon i{font-size:22px;color:var(--red);}
.at-title{font-size:19px;font-weight:800;color:var(--navy);font-family:'Plus Jakarta Sans',sans-serif;}
.at-sub{font-size:13px;color:var(--gray);margin-top:2px;}
.ad-types-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:32px;}
.ad-types-grid.three{grid-template-columns:repeat(3,1fr);}
.ad-card{border:2px solid var(--border);border-radius:var(--r-lg);padding:24px 20px;cursor:pointer;transition:all .25s;background:var(--white);position:relative;}
.ad-card:hover{border-color:var(--red);transform:translateY(-3px);box-shadow:0 8px 24px rgba(229,52,58,.14);}
.ad-card.selected{border-color:var(--red);background:var(--red-light);}
.ad-card .ac-check{position:absolute;top:14px;right:14px;width:24px;height:24px;border-radius:50%;border:2px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:center;transition:all .2s;}
.ad-card.selected .ac-check{background:var(--red);border-color:var(--red);}
.ad-card.selected .ac-check::after{content:'\f00c';font-family:'Font Awesome 6 Free';font-weight:900;font-size:10px;color:#fff;}
.ac-emoji{font-size:32px;margin-bottom:12px;display:block;}
.ac-title{font-size:16px;font-weight:800;color:var(--navy);margin-bottom:4px;font-family:'Plus Jakarta Sans',sans-serif;}
.ac-desc{font-size:12px;color:var(--gray);line-height:1.55;}
.ac-badge{display:inline-flex;align-items:center;gap:4px;background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:3px 9px;font-size:11px;font-weight:600;color:var(--gray);margin-top:10px;}
.ad-card.selected .ac-badge{background:rgba(229,52,58,.1);border-color:rgba(229,52,58,.3);color:var(--red);}

/* NAV FOOTER */
.step-nav{display:flex;align-items:center;justify-content:space-between;margin-top:28px;flex-wrap:wrap;gap:12px;}
.back-btn{display:flex;align-items:center;gap:7px;background:none;border:none;font-size:14px;font-weight:600;color:var(--gray);cursor:pointer;font-family:'Poppins',sans-serif;padding:0;transition:color .2s;}
.back-btn:hover{color:var(--navy);}

/* TOAST */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(80px);background:var(--navy);color:#fff;padding:13px 24px;border-radius:12px;font-size:14px;font-weight:600;z-index:9999;transition:transform .35s cubic-bezier(.175,.885,.32,1.275);box-shadow:0 8px 32px rgba(0,0,0,.2);display:flex;align-items:center;gap:8px;}
.toast.show{transform:translateX(-50%) translateY(0);}
.toast.success{background:#16A34A;}
.toast.error{background:var(--red);}

/* ERRORS */
.server-errors{background:#FFF1F3;border:1px solid rgba(229,52,58,.3);border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--red);}

@media(max-width:640px){
  .owner-types,.prop-type-grid{grid-template-columns:1fr;}
  .form-grid{grid-template-columns:1fr;}
  .form-group.full{grid-column:1;}
  .ad-types-grid,.ad-types-grid.three{grid-template-columns:1fr;}
  .success-features{grid-template-columns:1fr;}
  .prog-label{display:none;}
  .page-wrap{padding:24px 16px 60px;}
  .reg-form{padding:20px;}
  .step-title{font-size:24px;}
}
</style>
</head>
<body>

<header class="topbar">
  <a href="/" class="logo">
    <span><span class="big">Big</span><span class="wein">Wein</span></span>
    <span class="logo-tag">Zero Brokerage · Verified Listings</span>
  </a>
  <div class="topbar-right">Already have an account? <a href="/owner/login">Login</a></div>
</header>

<div class="progress-wrap">
  <div class="progress-steps">
    <div class="prog-step active" id="ps1"><div class="prog-dot"><span>1</span></div><div class="prog-label">Registration</div></div>
    <div class="prog-line" id="pl1"></div>
    <div class="prog-step" id="ps2"><div class="prog-dot"><span>2</span></div><div class="prog-label">Verify OTP</div></div>
    <div class="prog-line" id="pl2"></div>
    <div class="prog-step" id="ps3"><div class="prog-dot"><span>3</span></div><div class="prog-label">Account Ready</div></div>
    <div class="prog-line" id="pl3"></div>
    <div class="prog-step" id="ps4"><div class="prog-dot"><span>4</span></div><div class="prog-label">KYC</div></div>
  </div>
</div>

<div class="page-wrap">

  {{-- Server errors --}}
  @if($errors->any())
  <div class="server-errors">
    <i class="fa-solid fa-circle-xmark"></i>
    @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
  </div>
  @endif
  @if(session('error'))
  <div class="server-errors"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
  @endif

  {{-- ═══ STEP 1: Registration ═══ --}}
  <div class="step active" id="step1">
    <div class="step-header">
      <div class="step-badge"><i class="fa-solid fa-building"></i> Post Your Property Free</div>
      <h1 class="step-title">Register as a <span>Property Owner</span></h1>
      <p class="step-sub">Choose your owner type and create your free account to start posting ads</p>
    </div>

    <div class="owner-types" id="ownerTypes">
      <div class="owner-card" data-type="seller" onclick="selectOwner(this)">
        <div class="oc-check"></div>
        <div class="oc-icon"><i class="fa-solid fa-user-tie"></i></div>
        <div class="oc-title">Seller / Owner</div>
        <div class="oc-desc">I own a property and want to sell or rent it directly without a broker</div>
      </div>
      <div class="owner-card" data-type="builder" onclick="selectOwner(this)">
        <div class="oc-check"></div>
        <div class="oc-icon"><i class="fa-solid fa-hard-hat"></i></div>
        <div class="oc-title">Builder / Developer</div>
        <div class="oc-desc">I'm a developer or builder with multiple projects and properties to list</div>
      </div>
    </div>

    <div class="reg-form">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Full Name <span class="form-required">*</span></label>
          <div class="form-input-wrap"><i class="fa-solid fa-user"></i>
          <input class="form-input" type="text" id="regName" placeholder="Your full name" value="{{ old('name') }}"/>
          </div>
        </div>
        <div class="form-group" id="companyField" style="display:none;">
          <label class="form-label">Company / Brand Name</label>
          <div class="form-input-wrap"><i class="fa-solid fa-building"></i>
          <input class="form-input" type="text" id="regCompany" placeholder="Company or brand name" value="{{ old('company_name') }}"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address <span class="form-required">*</span></label>
          <div class="form-input-wrap"><i class="fa-solid fa-envelope"></i>
          <input class="form-input" type="email" id="regEmail" placeholder="yourname@email.com" value="{{ old('email') }}"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Mobile Number <span class="form-required">*</span></label>
          <div class="phone-wrap">
            <select class="form-input phone-code no-icon" id="regCode" style="padding-left:10px;">
              <option value="+91">🇮🇳 +91</option>
              <option value="+1">🇺🇸 +1</option>
              <option value="+44">🇬🇧 +44</option>
              <option value="+65">🇸🇬 +65</option>
              <option value="+60">🇲🇾 +60</option>
            </select>
            <div class="form-input-wrap" style="flex:1;"><i class="fa-solid fa-mobile-screen-button"></i>
            <input class="form-input" type="tel" id="regMobile" placeholder="9876543210" value="{{ old('mobile') }}"/>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">City <span class="form-required">*</span></label>
          <div class="form-input-wrap"><i class="fa-solid fa-location-dot"></i>
          <input class="form-input" type="text" id="regCity" placeholder="Your city" value="{{ old('city') }}"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">State</label>
          <div class="form-input-wrap"><i class="fa-solid fa-map"></i>
          <input class="form-input" type="text" id="regState" placeholder="State" value="{{ old('state') }}"/>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password <span class="form-required">*</span></label>
          <div class="form-input-wrap"><i class="fa-solid fa-lock"></i>
          <input class="form-input" type="password" id="regPwd" placeholder="Min 6 characters"/>
          <i class="fa-regular fa-eye eye-toggle" onclick="tglPwd('regPwd',this)"></i>
          </div>
          <span class="form-hint">Min 6 characters with letters and numbers</span>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span class="form-required">*</span></label>
          <div class="form-input-wrap"><i class="fa-solid fa-lock"></i>
          <input class="form-input" type="password" id="regConf" placeholder="Re-enter password"/>
          <i class="fa-regular fa-eye eye-toggle" onclick="tglPwd('regConf',this)"></i>
          </div>
        </div>
      </div>

      <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin-bottom:20px;">
        <input type="checkbox" id="regAgree" style="margin-top:3px;accent-color:var(--red);flex-shrink:0;width:16px;height:16px;"/>
        <span style="font-size:13px;color:var(--gray);line-height:1.6;">
          I agree to BigWein's <a href="#" style="color:var(--red);font-weight:600;">Terms of Service</a> and
          <a href="#" style="color:var(--red);font-weight:600;">Privacy Policy</a>. I confirm that I am the
          authorised owner or representative of the property I intend to list.
        </span>
      </label>

      <button class="btn btn-primary btn-full btn-lg" onclick="doRegister()" id="regBtn" type="button">
        <i class="fa-solid fa-arrow-right"></i> Create Account &amp; Continue
      </button>
      <p class="terms-note">Already have an account? <a href="/owner/login">Login here</a></p>
    </div>
  </div>

  {{-- ═══ STEP 2: OTP ═══ --}}
  <div class="step" id="step2">
    <div class="step-header">
      <div class="step-badge"><i class="fa-solid fa-shield-check"></i> Verify Your Account</div>
      <h1 class="step-title">Check Your <span>Email</span></h1>
      <p class="step-sub">We've sent a 6-digit OTP to confirm your identity</p>
    </div>
    <div class="otp-card">
      <div class="otp-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
      <h3 style="font-size:18px;font-weight:800;color:var(--navy);margin-bottom:8px;">OTP Sent Successfully</h3>
      <p style="font-size:14px;color:var(--gray);">Enter the 6-digit code sent to</p>
      <div class="otp-email-highlight"><i class="fa-solid fa-circle-check"></i><span id="otpEmailShow">yourname@email.com</span></div>
      <div class="otp-inputs">
        @for($i=1;$i<=6;$i++)<input class="otp-box" maxlength="1" type="tel" id="otp{{ $i }}"/>@endfor
      </div>
      <div class="otp-timer">Code expires in <span id="timerVal">10:00</span></div>
      <p style="font-size:13px;color:var(--gray);margin-bottom:20px;">Didn't receive? <button class="resend-link" id="resendBtn" onclick="resendOtp()">Resend OTP</button></p>
      <button class="btn btn-primary btn-full btn-lg" onclick="verifyOtp()" id="verifyBtn" type="button"><i class="fa-solid fa-check-circle"></i> Verify &amp; Continue</button>
      <button class="back-btn" style="margin-top:16px;width:100%;justify-content:center;" onclick="goToStep(1)" type="button"><i class="fa-solid fa-chevron-left"></i> Back to Registration</button>
    </div>
  </div>

  {{-- ═══ STEP 3: SUCCESS ═══ --}}
  <div class="step" id="step3">
    <div class="success-card">
      <div class="success-anim"><i class="fa-solid fa-check"></i></div>
      <h2 class="success-title">Account Created!</h2>
      <p class="success-sub">Welcome to BigWein. Your owner account is ready. Complete Aadhaar KYC now, or skip it and continue to your dashboard.</p>
      <div class="owner-type-badge" id="successTypeBadge"><i class="fa-solid fa-user-tie"></i> <span id="successTypeText">Seller / Owner</span></div>
      <div class="success-features">
        <div class="feat-item"><i class="fa-solid fa-id-card"></i><div><b>Aadhaar KYC</b><span>Required before posting access is enabled</span></div></div>
        <div class="feat-item"><i class="fa-solid fa-shield-check"></i><div><b>Verified Profile</b><span>Admin-approved identity builds marketplace trust</span></div></div>
        <div class="feat-item"><i class="fa-solid fa-gauge-high"></i><div><b>Dashboard Access</b><span>You can use the dashboard while KYC is pending</span></div></div>
        <div class="feat-item"><i class="fa-solid fa-building"></i><div><b>Post After Approval</b><span>Property and project posting unlock automatically</span></div></div>
      </div>
      <a class="btn btn-primary btn-full btn-lg" href="/owner/kyc"><i class="fa-solid fa-id-card"></i> Continue to KYC</a>
      <form method="POST" action="/owner/kyc/skip" style="margin-top:12px;">@csrf
        <button type="submit" style="border:0;background:transparent;color:var(--gray);font-size:12px;cursor:pointer;">Skip for now and go to Dashboard →</button>
      </form>
    </div>
  </div>

<div class="toast" id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toastMsg"></span></div>

<script>
let ownerType = null, regEmail = '', regPassword = '', timerInterval = null, registrationDone = false;

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  t.className = 'toast show ' + type;
  t.querySelector('i').className = type==='error' ? 'fa-solid fa-circle-xmark' : 'fa-solid fa-circle-check';
  setTimeout(() => t.classList.remove('show'), 3200);
}

function goToStep(n) {
  document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
  document.getElementById('step' + n).classList.add('active');
  for(let i=1;i<=4;i++) {
    const ps = document.getElementById('ps'+i);
    ps.classList.remove('active','done');
    if(i < n) { ps.classList.add('done'); ps.querySelector('.prog-dot').innerHTML = '<i class="fa-solid fa-check" style="font-size:12px;"></i>'; }
    else if(i === n) { ps.classList.add('active'); ps.querySelector('.prog-dot').innerHTML = '<span>'+i+'</span>'; }
    else { ps.querySelector('.prog-dot').innerHTML = '<span>'+i+'</span>'; }
    if(i < 4) document.getElementById('pl'+i).classList.toggle('done', i < n);
  }
  window.scrollTo({top:0,behavior:'smooth'});
}

function selectOwner(card) {
  document.querySelectorAll('.owner-card').forEach(c => c.classList.remove('selected'));
  card.classList.add('selected');
  ownerType = card.dataset.type;
  document.getElementById('companyField').style.display = ownerType === 'builder' ? 'flex' : 'none';
}

function tglPwd(id, icon) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  icon.classList.toggle('fa-eye', i.type === 'password');
  icon.classList.toggle('fa-eye-slash', i.type === 'text');
}

async function doRegister() {
  const name  = document.getElementById('regName').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const mob   = document.getElementById('regMobile').value.trim();
  const city  = document.getElementById('regCity').value.trim();
  const pwd   = document.getElementById('regPwd').value;
  const conf  = document.getElementById('regConf').value;
  const agree = document.getElementById('regAgree').checked;

  if(!ownerType) { showToast('Please select your owner type first', 'error'); return; }
  if(!name)  { showToast('Please enter your full name', 'error'); return; }
  if(!email || !email.includes('@')) { showToast('Please enter a valid email address', 'error'); return; }
  if(!mob || mob.length < 10) { showToast('Please enter a valid mobile number', 'error'); return; }
  if(!city) { showToast('Please enter your city', 'error'); return; }
  if(pwd.length < 6) { showToast('Password must be at least 6 characters', 'error'); return; }
  if(pwd !== conf) { showToast('Passwords do not match', 'error'); return; }
  if(!agree) { showToast('Please agree to Terms of Service to continue', 'error'); return; }
  if(ownerType === 'builder' && !document.getElementById('regCompany').value.trim()) {
    showToast('Please enter your company or brand name', 'error'); return;
  }

  const btn = document.getElementById('regBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account…';
  btn.disabled = true;

  try {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
    const res  = await fetch('/owner/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({
        name, email, password: pwd, password_confirmation: conf,
        mobile: mob, country_code: document.getElementById('regCode').value,
        owner_type: ownerType, company_name: document.getElementById('regCompany').value.trim(),
        city, state: document.getElementById('regState').value.trim(),
      })
    });
    const data = await res.json();
    if(data.success || data.redirect) {
      regEmail = email; regPassword = pwd; registrationDone = true;
      document.getElementById('successTypeText').textContent = ownerType === 'builder' ? 'Builder / Developer' : 'Seller / Owner';
      document.getElementById('successTypeBadge').querySelector('i').className = ownerType === 'builder' ? 'fa-solid fa-hard-hat' : 'fa-solid fa-user-tie';
      showToast('Account created! Welcome to BigWein 🎉', 'success');
      goToStep(3);
    } else if(data.otp_required) {
      regEmail = email; regPassword = pwd;
      document.getElementById('otpEmailShow').textContent = email;
      goToStep(2); startTimer();
    } else {
      showToast(data.message || 'Registration failed. Please try again.', 'error');
    }
  } catch(e) {
    // Fallback — form submit
    submitFormNative(name, email, mob, pwd, conf, city);
  }

  btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Create Account & Continue';
  btn.disabled = false;
}

function submitFormNative(name, email, mob, pwd, conf, city) {
  const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
  const form = document.createElement('form');
  form.method = 'POST'; form.action = '/owner/register';
  const fields = { _token: csrf, name, email, password: pwd, password_confirmation: conf,
    mobile: mob, country_code: document.getElementById('regCode').value,
    owner_type: ownerType, company_name: document.getElementById('regCompany').value.trim(),
    city, state: document.getElementById('regState').value.trim() };
  Object.entries(fields).forEach(([k, v]) => {
    const i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = v;
    form.appendChild(i);
  });
  document.body.appendChild(form); form.submit();
}

// OTP
function startTimer() {
  let secs = 600; clearInterval(timerInterval);
  const el = document.getElementById('timerVal');
  timerInterval = setInterval(() => {
    secs--;
    const m = Math.floor(secs/60).toString().padStart(2,'0');
    const s = (secs%60).toString().padStart(2,'0');
    el.textContent = `${m}:${s}`;
    if(secs <= 0) { clearInterval(timerInterval); el.textContent = 'Expired'; document.getElementById('resendBtn').classList.add('active'); }
  }, 1000);
}

function resendOtp() {
  const rb = document.getElementById('resendBtn');
  if(!rb.classList.contains('active')) return;
  rb.classList.remove('active');
  showToast('OTP resent to your email', 'success'); startTimer();
}

const otpBoxes = [1,2,3,4,5,6].map(i => document.getElementById('otp'+i));
otpBoxes.forEach((inp, i) => {
  inp.addEventListener('input', () => {
    inp.value = inp.value.replace(/\D/g,'').slice(-1);
    inp.classList.toggle('filled', inp.value !== '');
    if(inp.value && i < 5) otpBoxes[i+1].focus();
  });
  inp.addEventListener('keydown', e => {
    if(e.key === 'Backspace' && !inp.value && i > 0) otpBoxes[i-1].focus();
    if(e.key === 'Enter') verifyOtp();
  });
});

async function verifyOtp() {
  const otp = otpBoxes.map(b => b.value).join('');
  if(otp.length < 6) { showToast('Please enter all 6 digits', 'error'); return; }
  const btn = document.getElementById('verifyBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying…'; btn.disabled = true;
  try {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
    const res  = await fetch('/bw-api/verify-otp', {
      method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
      body: JSON.stringify({ email: regEmail, otp })
    });
    const data = await res.json();
    if(!data.error) {
      clearInterval(timerInterval); showToast('Email verified!', 'success');
      document.getElementById('successTypeText').textContent = ownerType === 'builder' ? 'Builder / Developer' : 'Seller / Owner';
      goToStep(3);
    } else { showToast(data.message || 'Incorrect OTP. Try again.', 'error'); }
  } catch(e) { showToast('Verification error. Try again.', 'error'); }
  btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Verify & Continue'; btn.disabled = false;
}

</script>
</body>
</html>
