@extends('frontend.layouts.app')
@section('title', ($tab ?? 'login')==='register' ? 'Create Account' : 'Login')
@section('content')

@if(session('bw_customer'))
    <script>window.location.href='/';</script>
@endif

<style>
:root{
    --auth-red:#EF3E3E;
    --auth-red-dark:#D93238;
    --auth-ink:#172033;
    --auth-muted:#6F7A8D;
    --auth-line:#E5EAF1;
    --auth-soft:#F7F9FC;
    --auth-dark:#141C2D;
}

.bw-auth-page{
    min-height:calc(100vh - 82px);
    padding:44px 20px 64px;
    background:
        radial-gradient(circle at 12% 16%,rgba(239,62,62,.08),transparent 25%),
        linear-gradient(180deg,#FAFBFD 0%,#F4F6FA 100%);
}

.bw-auth-shell{
    width:min(1080px,100%);
    margin:0 auto;
    display:grid;
    grid-template-columns:minmax(0,1.06fr) minmax(420px,.94fr);
    background:#fff;
    border:1px solid #E4E9F0;
    border-radius:26px;
    overflow:hidden;
    box-shadow:0 28px 70px rgba(17,24,39,.11);
}

/* LEFT PANEL */
.bw-auth-visual{
    position:relative;
    overflow:hidden;
    min-height:680px;
    padding:58px 50px;
    color:#fff;
    background:
        radial-gradient(circle at 85% 80%,rgba(239,62,62,.24),transparent 33%),
        linear-gradient(145deg,#111827 0%,#1D2537 60%,#2B1A26 100%);
}
.bw-auth-visual:before{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    right:-100px;
    top:-90px;
    border-radius:50%;
    border:1px solid rgba(255,255,255,.09);
}
.bw-auth-visual:after{
    content:"";
    position:absolute;
    width:390px;
    height:390px;
    left:-190px;
    bottom:-210px;
    border-radius:50%;
    background:rgba(239,62,62,.08);
}

.bw-auth-visual-inner{
    position:relative;
    z-index:1;
    max-width:500px;
}

.bw-auth-eyebrow{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border:1px solid rgba(255,255,255,.12);
    border-radius:999px;
    background:rgba(255,255,255,.055);
    color:rgba(255,255,255,.75);
    font-size:11px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.bw-auth-visual h2{
    margin:30px 0 16px;
    font-size:42px;
    line-height:1.12;
    font-weight:800;
    letter-spacing:-.035em;
    color:#fff;
}
.bw-auth-visual h2 span{color:#FF6065;}

.bw-auth-lead{
    max-width:470px;
    margin:0;
    font-size:15px;
    line-height:1.8;
    color:rgba(255,255,255,.66);
}

.bw-auth-benefits{
    display:grid;
    gap:13px;
    margin-top:34px;
}
.bw-auth-benefit{
    display:flex;
    align-items:flex-start;
    gap:13px;
    padding:14px 15px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    background:rgba(255,255,255,.045);
}
.bw-auth-benefit-icon{
    width:34px;
    height:34px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    color:#FF7175;
    background:rgba(239,62,62,.16);
}
.bw-auth-benefit strong{
    display:block;
    margin:0 0 3px;
    color:#fff;
    font-size:13px;
    font-weight:700;
}
.bw-auth-benefit small{
    display:block;
    color:rgba(255,255,255,.5);
    font-size:11px;
    line-height:1.5;
}

.bw-auth-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:10px;
    margin-top:34px;
    padding-top:24px;
    border-top:1px solid rgba(255,255,255,.09);
}
.bw-auth-stat strong{
    display:block;
    color:#fff;
    font-size:18px;
    line-height:1;
    margin-bottom:5px;
}
.bw-auth-stat span{
    color:rgba(255,255,255,.48);
    font-size:10px;
    line-height:1.35;
}

/* RIGHT PANEL */
.bw-auth-panel{
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:48px 46px;
    background:#fff;
}

.bw-auth-heading{
    margin-bottom:22px;
}
.bw-auth-heading h1{
    margin:0;
    font-size:30px;
    color:var(--auth-ink);
    line-height:1.2;
    font-weight:800;
    letter-spacing:-.025em;
}
.bw-auth-heading p{
    margin:8px 0 0;
    font-size:13px;
    color:var(--auth-muted);
}

.bw-auth-tabs{
    display:grid;
    grid-template-columns:1fr 1fr;
    padding:4px;
    margin-bottom:24px;
    border-radius:12px;
    background:#F2F4F7;
}
.bw-auth-tabs button{
    height:42px;
    border:0;
    border-radius:9px;
    background:transparent;
    color:#7B8493;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:.18s ease;
}
.bw-auth-tabs button.active{
    color:#fff;
    background:var(--auth-red);
    box-shadow:0 8px 18px rgba(239,62,62,.22);
}

.auth-form{display:none;}
.auth-form.active{display:block;}
.auth-step{display:none;}
.auth-step.active{display:block;}

.auth-login-options{display:grid;gap:10px;}
.auth-social-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}

.auth-option{
    width:100%;
    min-height:46px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:9px;
    border:1px solid #DEE4EC;
    border-radius:11px;
    background:#fff;
    color:#334155;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:.18s ease;
}
.auth-option:hover{
    transform:translateY(-1px);
    border-color:#C9D1DC;
    box-shadow:0 7px 18px rgba(15,23,42,.06);
}
.auth-option.whatsapp{
    color:#178A54;
    border-color:#BFE8D3;
    background:#F5FCF8;
}
.auth-option.whatsapp i{font-size:18px;}
.auth-option.google i{color:#DB4437;}
.auth-option.facebook i{color:#1877F2;}

.auth-divider{
    display:flex;
    align-items:center;
    gap:12px;
    margin:20px 0;
    color:#98A2B3;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:700;
}
.auth-divider:before,.auth-divider:after{
    content:"";
    height:1px;
    background:#E8ECF1;
    flex:1;
}

.form-field{width:100%;}
.form-label{
    display:block;
    margin-bottom:7px;
    color:#667085;
    font-size:10px;
    font-weight:800;
    letter-spacing:.055em;
    text-transform:uppercase;
}
.form-input{
    width:100%;
    height:47px;
    border:1px solid #DCE2EA;
    border-radius:10px;
    background:#fff;
    padding:0 13px;
    color:#1F2937;
    font-family:inherit;
    font-size:13px;
    outline:none;
    transition:.18s ease;
}
.form-input::placeholder{color:#B0B8C5;}
.form-input:focus{
    border-color:var(--auth-red);
    box-shadow:0 0 0 3px rgba(239,62,62,.09);
}

.form-input-icon{position:relative;}
.form-input-icon .form-input{padding-right:42px;}
.form-input-icon i{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    color:#9AA4B2;
}

.btn-auth{
    width:100%;
    min-height:48px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border:0;
    border-radius:11px;
    background:var(--auth-red);
    color:#fff;
    font-size:13px;
    font-weight:800;
    font-family:inherit;
    cursor:pointer;
    box-shadow:0 10px 22px rgba(239,62,62,.20);
    transition:.18s ease;
}
.btn-auth:hover{
    background:var(--auth-red-dark);
    transform:translateY(-1px);
}
.btn-auth:disabled{opacity:.7;cursor:not-allowed;transform:none;}

.auth-footer-note{
    margin:18px 0 0;
    text-align:center;
    color:#8791A1;
    font-size:12px;
}
.auth-footer-note a{
    color:var(--auth-red);
    font-weight:800;
    text-decoration:none;
}

.whatsapp-login-box{
    display:none;
    margin-top:12px;
    padding:15px;
    border:1px solid #CFEADD;
    border-radius:12px;
    background:#F8FCFA;
}
.whatsapp-login-box.open{display:block;}
.btn-auth-whatsapp{
    min-height:43px;
    background:#1FAF6A;
    box-shadow:none;
    margin-top:10px;
}
.btn-auth-whatsapp:hover{background:#188F57;}
.wa-otp-wrap{display:none;margin-top:12px;}
.wa-otp-wrap.open{display:block;}
.wa-otp-row{
    display:grid;
    grid-template-columns:1fr auto;
    gap:8px;
}
.wa-verify-btn{
    border:0;
    border-radius:10px;
    padding:0 17px;
    background:#1FAF6A;
    color:#fff;
    font-weight:800;
    cursor:pointer;
}

.otp-inputs{
    display:grid;
    grid-template-columns:repeat(6,1fr);
    gap:8px;
    margin:20px 0;
}
.otp-input{
    width:100%;
    height:52px;
    border:1px solid #DCE2EA;
    border-radius:10px;
    text-align:center;
    font-size:19px;
    font-weight:800;
    outline:none;
    color:#1F2937;
}
.otp-input:focus{
    border-color:var(--auth-red);
    box-shadow:0 0 0 3px rgba(239,62,62,.09);
}

/* Hide the frontend footer on auth screen for a cleaner focused login experience */
.bw-footer{display:none!important;}

@media(max-width:900px){
    .bw-auth-page{padding:24px 15px 44px;}
    .bw-auth-shell{
        grid-template-columns:1fr;
        max-width:620px;
    }
    .bw-auth-visual{
        min-height:auto;
        padding:40px 34px;
    }
    .bw-auth-visual h2{font-size:34px;}
    .bw-auth-stats{max-width:430px;}
    .bw-auth-panel{padding:38px 34px 42px;}
}

@media(max-width:560px){
    .bw-auth-page{padding:14px 10px 28px;}
    .bw-auth-shell{border-radius:18px;}
    .bw-auth-visual{padding:32px 24px;}
    .bw-auth-visual h2{font-size:29px;}
    .bw-auth-benefit small{display:none;}
    .bw-auth-panel{padding:30px 22px 34px;}
    .bw-auth-heading h1{font-size:25px;}
    .auth-social-grid{grid-template-columns:1fr;}
    .bw-auth-stats{gap:6px;}
    .bw-auth-stat strong{font-size:16px;}
    .otp-inputs{gap:5px;}
    .otp-input{height:46px;font-size:17px;}
}
</style>

<div class="bw-auth-page">
    <div class="bw-auth-shell">

        <aside class="bw-auth-visual">
            <div class="bw-auth-visual-inner">
                <span class="bw-auth-eyebrow">
                    <i class="fa-solid fa-house"></i>
                    Zero Brokerage Property Platform
                </span>

                <h2>Find a place that feels like <span>home.</span></h2>

                <p class="bw-auth-lead">
                    Discover verified properties, connect directly with owners and manage your property journey from one secure BigWein account.
                </p>

                <div class="bw-auth-benefits">
                    <div class="bw-auth-benefit">
                        <div class="bw-auth-benefit-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div>
                            <strong>Verified Property Listings</strong>
                            <small>Discover genuine property opportunities with clearer listing information.</small>
                        </div>
                    </div>

                    <div class="bw-auth-benefit">
                        <div class="bw-auth-benefit-icon"><i class="fa-solid fa-handshake"></i></div>
                        <div>
                            <strong>Connect Directly</strong>
                            <small>Reach owners and property professionals without unnecessary brokerage layers.</small>
                        </div>
                    </div>

                    <div class="bw-auth-benefit">
                        <div class="bw-auth-benefit-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div>
                            <strong>Simple & Secure</strong>
                            <small>Save properties, manage enquiries and continue your search from one account.</small>
                        </div>
                    </div>
                </div>

                <div class="bw-auth-stats">
                    <div class="bw-auth-stat">
                        <strong>Buy</strong>
                        <span>Homes & properties</span>
                    </div>
                    <div class="bw-auth-stat">
                        <strong>Rent</strong>
                        <span>Flexible options</span>
                    </div>
                    <div class="bw-auth-stat">
                        <strong>Invest</strong>
                        <span>Projects & businesses</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="bw-auth-panel">
            <div class="bw-auth-heading">
                <h1 id="authH">{{ ($tab ?? 'login')==='register' ? 'Create your account' : 'Welcome back' }}</h1>
                <p id="authP">{{ ($tab ?? 'login')==='register' ? 'Create your free BigWein account to get started.' : 'Sign in to continue your property journey.' }}</p>
            </div>

            <div class="bw-auth-tabs">
                <button id="tabL" class="{{ ($tab ?? 'login')!=='register'?'active':'' }}" onclick="switchTab('login')" type="button">
                    Login
                </button>
                <button id="tabR" class="{{ ($tab ?? 'login')==='register'?'active':'' }}" onclick="switchTab('register')" type="button">
                    Register
                </button>
            </div>

            {{-- LOGIN --}}
            <div class="auth-form {{ ($tab ?? 'login')!=='register'?'active':'' }}" id="formL">

                <div class="auth-login-options">
                    <button class="auth-option whatsapp" onclick="toggleWhatsAppLogin()" type="button">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Continue with WhatsApp OTP</span>
                    </button>

                    <div class="auth-divider" style="margin:5px 0 0;">
                        <span>or continue with</span>
                    </div>

                    <div class="auth-social-grid">
                        <button class="auth-option google" onclick="socialLogin('google')" type="button">
                            <i class="fa-brands fa-google"></i>
                            <span>Google</span>
                        </button>

                        <button class="auth-option facebook" onclick="socialLogin('facebook')" type="button">
                            <i class="fa-brands fa-facebook-f"></i>
                            <span>Facebook</span>
                        </button>
                    </div>
                </div>

                <div class="whatsapp-login-box" id="waLoginBox">
                    <div class="form-field">
                        <label class="form-label">WhatsApp Number</label>
                        <div style="display:flex;gap:8px;">
                            <input class="form-input" id="waCode" type="text" value="+91" style="width:70px;flex-shrink:0;"/>
                            <input class="form-input" id="waMobile" type="tel" placeholder="9876543210" style="flex:1;"/>
                        </div>
                    </div>

                    <button class="btn-auth btn-auth-whatsapp" onclick="sendWhatsAppOtp()" type="button">
                        <i class="fa-brands fa-whatsapp"></i> Send WhatsApp OTP
                    </button>

                    <div class="wa-otp-wrap" id="waOtpWrap">
                        <label class="form-label">Enter OTP</label>
                        <div class="wa-otp-row">
                            <input class="form-input" id="waOtp" type="text" inputmode="numeric" maxlength="6" placeholder="6-digit OTP"/>
                            <button class="wa-verify-btn" onclick="verifyWhatsAppOtp()" type="button">Verify</button>
                        </div>
                    </div>
                </div>

                <div class="auth-divider"><span>or login using email</span></div>

                <div class="form-field">
                    <label class="form-label">Email</label>
                    <input class="form-input" id="lEmail" type="email" placeholder="you@example.com" autocomplete="email"/>
                </div>

                <div class="form-field" style="margin-top:14px;">
                    <label class="form-label">Password</label>
                    <div class="form-input-icon">
                        <input class="form-input" id="lPwd" type="password" placeholder="Your password"/>
                        <i class="fa-regular fa-eye" onclick="tglPwd('lPwd',this)" style="cursor:pointer;"></i>
                    </div>
                </div>

                <button class="btn-auth" id="lBtn" onclick="doLogin()" style="margin-top:20px;" type="button">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>

                <p class="auth-footer-note">
                    New to BigWein? <a href="#" onclick="switchTab('register');return false;">Create Account</a>
                </p>
            </div>

            {{-- REGISTER --}}
            <div class="auth-form {{ ($tab ?? 'login')==='register'?'active':'' }}" id="formR">

                <div class="auth-step active" id="step1">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div class="form-field">
                            <label class="form-label">First Name</label>
                            <input class="form-input" id="rFirst" type="text" placeholder="First name"/>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Last Name</label>
                            <input class="form-input" id="rLast" type="text" placeholder="Last name"/>
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom:14px;">
                        <label class="form-label">Email</label>
                        <input class="form-input" id="rEmail" type="email" placeholder="you@example.com"/>
                    </div>

                    <div class="form-field" style="margin-bottom:14px;">
                        <label class="form-label">Mobile</label>
                        <div style="display:flex;gap:8px;">
                            <input class="form-input" id="rCode" type="text" value="+91" style="width:65px;flex-shrink:0;"/>
                            <input class="form-input" id="rMob" type="tel" placeholder="9876543210" style="flex:1;"/>
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom:14px;">
                        <label class="form-label">Password</label>
                        <div class="form-input-icon">
                            <input class="form-input" id="rPwd" type="password" placeholder="Min 6 characters"/>
                            <i class="fa-regular fa-eye" onclick="tglPwd('rPwd',this)" style="cursor:pointer;"></i>
                        </div>
                    </div>

                    <div class="form-field" style="margin-bottom:20px;">
                        <label class="form-label">Confirm Password</label>
                        <input class="form-input" id="rConf" type="password" placeholder="Re-enter password"/>
                    </div>

                    <button class="btn-auth" id="rBtn" onclick="doReg()" type="button">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>

                    <p class="auth-footer-note">
                        Already have an account? <a href="#" onclick="switchTab('login');return false;">Login</a>
                    </p>
                </div>

                {{-- OTP STEP --}}
                <div class="auth-step" id="step2">
                    <div style="text-align:center;margin-bottom:20px;">
                        <div style="width:64px;height:64px;background:rgba(239,62,62,.09);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="fa-solid fa-envelope" style="font-size:24px;color:var(--auth-red);"></i>
                        </div>
                        <h3 style="font-size:19px;font-weight:800;margin:0 0 6px;color:var(--auth-ink);">Verify your email</h3>
                        <p style="font-size:12px;color:#6B7280;margin:0;">OTP sent to <strong id="otpEmail"></strong></p>
                    </div>

                    <div class="otp-inputs">
                        @for($i=0;$i<6;$i++)
                            <input class="otp-input" maxlength="1" type="number" min="0" max="9"/>
                        @endfor
                    </div>

                    <button class="btn-auth" id="vBtn" onclick="doVerify()" type="button">
                        <i class="fa-solid fa-check"></i> Verify & Continue
                    </button>

                    <p class="auth-footer-note">
                        Didn't receive the OTP? <a href="#" onclick="resendOtp();return false;">Resend</a>
                    </p>
                </div>

            </div>
        </main>

    </div>
</div>

@endsection

@push('scripts')
<script>
if(BW.isLoggedIn) window.location.href = '/';
const redir = new URLSearchParams(window.location.search).get('redirect') || '/';
let pendingEmail = '';
let pendingPwd   = '';

function switchTab(t){
  ['L','R'].forEach(x=>{ document.getElementById('tab'+x).classList.toggle('active',x===(t==='login'?'L':'R')); document.getElementById('form'+x).classList.toggle('active',x===(t==='login'?'L':'R')); });
  document.getElementById('authH').textContent = t==='login'?'Welcome back':'Create your account';
  document.getElementById('authP').textContent = t==='login'?'Sign in to continue your property journey.':'Create your free BigWein account to get started.';
}

async function doLogin(){
  const email=document.getElementById('lEmail').value.trim();
  const pwd  =document.getElementById('lPwd').value;
  if(!email||!pwd){ bwToast('Please enter email and password','error'); return; }
  const btn=document.getElementById('lBtn');
  btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Logging in…'; btn.disabled=true;
  const res = await bwPost('/bw-api/login',{email,password:pwd});
  btn.innerHTML='<i class="fa-solid fa-right-to-bracket"></i> Login'; btn.disabled=false;
  if(!res.error){
    bwToast('Login successful!','success');
    setTimeout(()=>window.location.href=redir,600);
  } else {
    if(res.key==='notVerified'){ pendingEmail=email; pendingPwd=pwd; document.getElementById('otpEmail').textContent=email; showStep2(); bwToast('Please verify your email first.','error'); }
    else bwToast(res.message,'error');
  }
}

async function doReg(){
  const first=document.getElementById('rFirst').value.trim();
  const last =document.getElementById('rLast').value.trim();
  const email=document.getElementById('rEmail').value.trim();
  const mob  =document.getElementById('rMob').value.trim();
  const code =document.getElementById('rCode').value.trim();
  const pwd  =document.getElementById('rPwd').value;
  const conf =document.getElementById('rConf').value;
  if(!first||!email||!pwd){ bwToast('Please fill all required fields','error'); return; }
  if(pwd.length<6){ bwToast('Password must be at least 6 characters','error'); return; }
  if(pwd!==conf){ bwToast('Passwords do not match','error'); return; }
  const btn=document.getElementById('rBtn');
  btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Creating…'; btn.disabled=true;
  const res=await bwPost('/bw-api/register',{name:`${first} ${last}`.trim(),email,password:pwd,mobile:mob,country_code:code});
  btn.innerHTML='<i class="fa-solid fa-user-plus"></i> Create Account'; btn.disabled=false;
  if(!res.error){
    if(res.auto_login){
      bwToast('Account created! Welcome to BigWein 🎉','success');
      setTimeout(()=>window.location.href='/user/dashboard', 900);
    } else {
      pendingEmail=email; pendingPwd=pwd;
      document.getElementById('otpEmail').textContent=email;
      bwToast('Account created! Enter OTP 123456 to verify.','success');
      showStep2();
    }
  } else { bwToast(res.message,'error'); }
}

function showStep2(){ document.querySelectorAll('.auth-step').forEach(s=>s.classList.remove('active')); document.getElementById('step2').classList.add('active'); document.querySelector('.otp-input')?.focus(); }

async function doVerify(){
  const otp=Array.from(document.querySelectorAll('.otp-input')).map(i=>i.value).join('');
  if(otp.length<6){ bwToast('Enter the complete 6-digit OTP','error'); return; }
  const btn=document.getElementById('vBtn');
  btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Verifying…'; btn.disabled=true;
  const res=await bwPost('/bw-api/verify-otp',{email:pendingEmail,otp});
  btn.innerHTML='<i class="fa-solid fa-check"></i> Verify & Continue'; btn.disabled=false;
  if(!res.error){
    bwToast('Email verified! Logging in…','success');
    if(pendingPwd){ const lr=await bwPost('/bw-api/login',{email:pendingEmail,password:pendingPwd}); if(!lr.error){ setTimeout(()=>window.location.href='/',600); return; } }
    setTimeout(()=>{ switchTab('login'); document.getElementById('lEmail').value=pendingEmail; },700);
  } else { bwToast(res.message,'error'); }
}

async function resendOtp(){
  const res=await bwPost('/bw-api/send-otp',{email:pendingEmail});
  bwToast(res.message,res.error?'error':'success');
}

function toggleWhatsAppLogin(){
  const box = document.getElementById('waLoginBox');
  box.classList.toggle('open');
  if(box.classList.contains('open')) document.getElementById('waMobile')?.focus();
}

function sendWhatsAppOtp(){
  const code = document.getElementById('waCode').value.trim();
  const mobile = document.getElementById('waMobile').value.trim();
  if(!mobile || mobile.length < 8){ bwToast('Please enter a valid WhatsApp number','error'); return; }
  document.getElementById('waOtpWrap').classList.add('open');
  bwToast('WhatsApp OTP option is ready in design. Connect the WhatsApp OTP API to enable sending.','success');
}

function verifyWhatsAppOtp(){
  const otp = document.getElementById('waOtp').value.trim();
  if(otp.length < 6){ bwToast('Please enter the 6-digit OTP','error'); return; }
  bwToast('WhatsApp OTP verification API is not connected yet.','error');
}

function socialLogin(provider){
  const providerName = provider.charAt(0).toUpperCase() + provider.slice(1);
  bwToast(providerName + ' login button is added. Connect OAuth route to enable login.','success');
}

document.querySelectorAll('.otp-input').forEach((inp,i,all)=>{
  inp.addEventListener('input',()=>{ inp.value=inp.value.slice(-1); if(inp.value&&i<all.length-1)all[i+1].focus(); });
  inp.addEventListener('keydown',e=>{ if(e.key==='Backspace'&&!inp.value&&i>0)all[i-1].focus(); if(e.key==='Enter'&&i===all.length-1)doVerify(); });
});

function tglPwd(id,icon){ const i=document.getElementById(id); i.type=i.type==='password'?'text':'password'; icon.classList.toggle('fa-eye',i.type==='password'); icon.classList.toggle('fa-eye-slash',i.type==='text'); }
document.getElementById('lPwd').addEventListener('keypress',e=>{ if(e.key==='Enter')doLogin(); });
</script>
@endpush
