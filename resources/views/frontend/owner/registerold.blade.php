<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Owner Registration — BigWein</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}"/>
<style>
body{background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.reg-wrap{width:100%;max-width:960px;}
.reg-header{text-align:center;margin-bottom:32px;}
.reg-header .logo-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:16px;}
.reg-header h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:28px;font-weight:900;color:var(--navy);}
.reg-header p{font-size:15px;color:var(--gray);}
.step-bar{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:32px;}
.sb-step{display:flex;align-items:center;gap:8px;}
.sb-dot{width:32px;height:32px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--gray2);background:#fff;transition:all .3s;flex-shrink:0;}
.sb-label{font-size:12px;font-weight:600;color:var(--gray2);}
.sb-step.active .sb-dot{border-color:var(--red);background:var(--red);color:#fff;}
.sb-step.active .sb-label{color:var(--red);}
.sb-step.done .sb-dot{border-color:var(--green);background:var(--green);color:#fff;}
.sb-step.done .sb-label{color:var(--green);}
.sb-line{width:48px;height:2px;background:var(--border);margin:0 4px;}
.sb-line.done{background:var(--green);}

/* Type selection */
.type-cards{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;}
.type-card{background:#fff;border:2.5px solid var(--border);border-radius:var(--r-xl);padding:28px 24px;text-align:center;cursor:pointer;transition:all .25s;}
.type-card:hover{border-color:var(--red);transform:translateY(-3px);box-shadow:var(--shadow-md);}
.type-card.selected{border-color:var(--red);background:var(--red-light);}
.tc-icon{width:70px;height:70px;border-radius:20px;background:var(--bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;transition:all .25s;}
.type-card.selected .tc-icon{background:var(--red);color:#fff;}
.type-card:not(.selected) .tc-icon{color:var(--red);}
.tc-title{font-size:18px;font-weight:800;color:var(--navy);font-family:'Plus Jakarta Sans',sans-serif;margin-bottom:6px;}
.tc-sub{font-size:13px;color:var(--gray);line-height:1.5;}
.tc-feats{list-style:none;margin-top:14px;text-align:left;display:flex;flex-direction:column;gap:7px;}
.tc-feats li{font-size:12px;color:var(--gray);display:flex;align-items:center;gap:6px;}
.tc-feats li i{color:var(--green);font-size:11px;}
.type-card.selected .tc-feats li{color:var(--navy);}

.form-card{background:#fff;border-radius:var(--r-xl);padding:32px;box-shadow:var(--shadow);margin-bottom:20px;}
</style>
</head>
<body>
<div class="reg-wrap">
  <div class="reg-header">
    <div class="logo-row">
      <div class="logo-img">B</div>
      <div class="logo-text"><span class="big">Big</span><span class="wein">Wein</span></div>
    </div>
    <h1>Owner Registration</h1>
    <p>List your properties and connect with thousands of buyers across India</p>
  </div>

  {{-- Step bar --}}
  <div class="step-bar">
    <div class="sb-step active" id="sbs1"><div class="sb-dot">1</div><div class="sb-label">Owner Type</div></div>
    <div class="sb-line" id="sbl1"></div>
    <div class="sb-step" id="sbs2"><div class="sb-dot">2</div><div class="sb-label">Your Details</div></div>
    <div class="sb-line" id="sbl2"></div>
    <div class="sb-step" id="sbs3"><div class="sb-dot">3</div><div class="sb-label">All Set!</div></div>
  </div>

  @if($errors->any())
    <div class="alert alert-error" style="max-width:600px;margin:0 auto 20px;"><i class="fa-solid fa-circle-xmark"></i> @foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>
  @endif

  {{-- STEP 1: Owner Type --}}
  <div id="regStep1">
    <div class="type-cards">
      <div class="type-card selected" id="tc-seller" onclick="selectType('seller')">
        <div class="tc-icon"><i class="fa-solid fa-user-tie"></i></div>
        <div class="tc-title">Individual Seller</div>
        <div class="tc-sub">I want to sell or rent my own property directly</div>
        <ul class="tc-feats">
          <li><i class="fa-solid fa-check"></i> Individual property listing</li>
          <li><i class="fa-solid fa-check"></i> Direct buyer contact</li>
          <li><i class="fa-solid fa-check"></i> Residential &amp; commercial</li>
          <li><i class="fa-solid fa-check"></i> Free to list up to 2 properties</li>
        </ul>
      </div>
      <div class="type-card" id="tc-builder" onclick="selectType('builder')">
        <div class="tc-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div class="tc-title">Builder / Developer</div>
        <div class="tc-sub">I build projects and list multiple properties</div>
        <ul class="tc-feats">
          <li><i class="fa-solid fa-check"></i> Multiple project listings</li>
          <li><i class="fa-solid fa-check"></i> Company/brand profile</li>
          <li><i class="fa-solid fa-check"></i> RERA project showcase</li>
          <li><i class="fa-solid fa-check"></i> Priority placement options</li>
        </ul>
      </div>
    </div>
    <div style="text-align:center;">
      <button class="btn btn-red btn-lg" onclick="goStep(2)">Continue <i class="fa-solid fa-arrow-right"></i></button>
      <div style="margin-top:16px;font-size:13px;color:var(--gray);">Already have an account? <a href="{{ url('/owner/login') }}" style="color:var(--red);font-weight:700;">Login here</a></div>
    </div>
  </div>

  {{-- STEP 2: Registration Form --}}
  <div id="regStep2" style="display:none;">
    <div class="form-card">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid var(--border);">
        <div class="tc-icon" id="formTypeIcon" style="width:48px;height:48px;border-radius:14px;font-size:20px;background:var(--red);color:#fff;flex-shrink:0;">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
          <div style="font-size:16px;font-weight:800;color:var(--navy);" id="formTypeLabel">Individual Seller Registration</div>
          <div style="font-size:12px;color:var(--gray);">Fill in your details to create your owner account</div>
        </div>
        <button class="btn btn-outline btn-sm" onclick="goStep(1)" style="margin-left:auto;padding:6px 14px;font-size:12px;"><i class="fa-solid fa-chevron-left"></i> Change</button>
      </div>

      <form method="POST" action="{{ url('/owner/register') }}">
        @csrf
        <input type="hidden" name="owner_type" id="ownerTypeInput" value="seller"/>

        <div class="fg">
          <div class="f-group">
            <label class="f-label">Full Name <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-user"></i>
            <input class="f-input" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Email Address <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-envelope"></i>
            <input class="f-input" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Mobile Number <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-mobile-screen-button"></i>
            <input class="f-input" type="tel" name="mobile" value="{{ old('mobile') }}" placeholder="+91 9876543210" required/>
            </div>
          </div>
          <div class="f-group" id="companyGroup" style="display:none;">
            <label class="f-label">Company / Builder Name</label>
            <div class="f-wrap"><i class="fa-solid fa-building"></i>
            <input class="f-input" type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Your company name"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Password <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-lock"></i>
            <input class="f-input" type="password" name="password" placeholder="Minimum 6 characters" required id="regPass"/>
            <button type="button" class="pw-toggle" onclick="togglePw('regPass',this)"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Confirm Password <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-lock"></i>
            <input class="f-input" type="password" name="password_confirmation" placeholder="Re-enter password" required id="regPass2"/>
            </div>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-top:18px;font-size:12px;color:var(--gray);margin-bottom:20px;">
          <input type="checkbox" id="termsCheck" required style="accent-color:var(--red);width:14px;height:14px;"/>
          <label for="termsCheck">I agree to BigWein's <a href="#" style="color:var(--red);">Terms of Service</a> &amp; <a href="#" style="color:var(--red);">Privacy Policy</a></label>
        </div>

        <div style="display:flex;gap:12px;align-items:center;">
          <button type="submit" class="btn btn-red btn-lg" style="flex:1;justify-content:center;">
            <i class="fa-solid fa-user-plus"></i> Create Owner Account
          </button>
        </div>
        <div style="text-align:center;margin-top:14px;font-size:13px;color:var(--gray);">
          Already registered? <a href="{{ url('/owner/login') }}" style="color:var(--red);font-weight:700;">Login to your account</a>
        </div>
      </form>
    </div>
  </div>

  {{-- STEP 3: Success --}}
  <div id="regStep3" style="display:none;text-align:center;padding:40px 20px;">
    <div style="width:80px;height:80px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <i class="fa-solid fa-check" style="color:#fff;font-size:36px;"></i>
    </div>
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:26px;font-weight:900;color:var(--navy);margin-bottom:8px;">You're All Set!</h2>
    <p style="font-size:15px;color:var(--gray);">Your owner account has been created. Redirecting to your dashboard…</p>
  </div>
</div>

<script src="{{ asset('frontend/js/owner.js') }}"></script>
<script>
let selectedType = 'seller';

function selectType(type) {
    selectedType = type;
    document.getElementById('tc-seller').classList.toggle('selected', type === 'seller');
    document.getElementById('tc-builder').classList.toggle('selected', type === 'builder');
    document.getElementById('ownerTypeInput').value = type;
    document.getElementById('companyGroup').style.display = type === 'builder' ? 'flex' : 'none';
    const icon = document.getElementById('formTypeIcon');
    icon.innerHTML = type === 'builder'
        ? '<i class="fa-solid fa-building-columns"></i>'
        : '<i class="fa-solid fa-user-tie"></i>';
    document.getElementById('formTypeLabel').textContent = type === 'builder'
        ? 'Builder / Developer Registration'
        : 'Individual Seller Registration';
}

function goStep(n) {
    document.getElementById('regStep1').style.display = n === 1 ? 'block' : 'none';
    document.getElementById('regStep2').style.display = n === 2 ? 'block' : 'none';
    document.getElementById('regStep3').style.display = n === 3 ? 'block' : 'none';
    [1, 2, 3].forEach(i => {
        const el = document.getElementById('sbs' + i);
        el.classList.toggle('active', i === n);
        el.classList.toggle('done', i < n);
        if (i < 3) document.getElementById('sbl' + i).classList.toggle('done', i < n);
    });
}

function togglePw(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.innerHTML = inp.type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
}
</script>
</body>
</html>
