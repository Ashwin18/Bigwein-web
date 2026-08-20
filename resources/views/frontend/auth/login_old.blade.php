@extends('frontend.layouts.app')
@section('title', ($tab ?? 'login')==='register' ? 'Create Account' : 'Login')
@section('content')

@if(session('bw_customer'))
  <script>window.location.href='/';</script>
@endif

<div class="auth-page">
  <div class="auth-split">
    <div class="auth-promo">
      <a href="/" style="display:flex;flex-direction:column;color:#fff;text-decoration:none;margin-bottom:32px;"><span style="font-size:26px;font-weight:800;"><span style="color:var(--red)">Big</span>Wein</span><span style="font-size:10px;opacity:.5;">Find Your Perfect Space</span></a>
      <h2 style="font-size:24px;font-weight:800;color:#fff;line-height:1.3;margin-bottom:14px;">Your Dream Property Awaits</h2>
      <img src="{{ url('images/Logo.jpeg') }}" alt="Bigwein"
        style="height:70px;width:auto;object-fit:contain;border-radius:8px;margin-bottom:20px;display:block;"
        onerror="this.style.display='none'"/>
      <p style="font-size:14px;color:rgba(255,255,255,.65);line-height:1.75;margin-bottom:32px;">Join thousands of Indians using BigWein to buy, sell and rent property — completely free of brokerage.</p>
      @foreach(['Verified listings across India','Zero brokerage — connect directly with owners','Simple, fast and secure platform'] as $b)
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
          <div style="width:32px;height:32px;background:rgba(229,52,58,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-check" style="color:var(--red);font-size:12px;"></i></div>
          <span style="font-size:13px;color:rgba(255,255,255,.82);">{{ $b }}</span>
        </div>
      @endforeach
    </div>
    <div class="auth-panel">
      <div class="auth-header">
        <h1 id="authH">{{ ($tab ?? 'login')==='register'?'Create Account':'Welcome Back' }}</h1>
        <img src="{{ url('images/Logo.jpeg') }}" alt="Bigwein"
          style="height:48px;width:auto;object-fit:contain;border-radius:6px;margin-bottom:6px;"
          onerror="this.style.display='none'"/>
        <p id="authP">{{ ($tab ?? 'login')==='register'?"Join BigWein — it's free":'Login to your BigWein account' }}</p>
        <div class="auth-tabs">
          <button id="tabL" class="{{ ($tab ?? 'login')!=='register'?'active':'' }}" onclick="switchTab('login')" type="button">Login</button>
          <button id="tabR" class="{{ ($tab ?? 'login')==='register'?'active':'' }}" onclick="switchTab('register')" type="button">Register</button>
        </div>
      </div>
      <div class="auth-body">
        <!-- LOGIN -->
        <div class="auth-form {{ ($tab ?? 'login')!=='register'?'active':'' }}" id="formL">
          <div class="form-field"><label class="form-label">Email</label><input class="form-input" id="lEmail" type="email" placeholder="you@example.com" autocomplete="email"/></div>
          <div class="form-field" style="margin-top:14px;"><label class="form-label">Password</label>
            <div class="form-input-icon"><input class="form-input" id="lPwd" type="password" placeholder="Your password"/><i class="fa-regular fa-eye" onclick="tglPwd('lPwd',this)" style="cursor:pointer;"></i></div>
          </div>
          <button class="btn-auth" id="lBtn" onclick="doLogin()" style="margin-top:20px;" type="button"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
          <p class="auth-footer-note">No account? <a href="#" onclick="switchTab('register')">Register Free</a></p>
        </div>
        <!-- REGISTER -->
        <div class="auth-form {{ ($tab ?? 'login')==='register'?'active':'' }}" id="formR">
          <div class="auth-step active" id="step1">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
              <div class="form-field"><label class="form-label">First Name</label><input class="form-input" id="rFirst" type="text" placeholder="First name"/></div>
              <div class="form-field"><label class="form-label">Last Name</label><input class="form-input" id="rLast" type="text" placeholder="Last name"/></div>
            </div>
            <div class="form-field" style="margin-bottom:14px;"><label class="form-label">Email</label><input class="form-input" id="rEmail" type="email" placeholder="you@example.com"/></div>
            <div class="form-field" style="margin-bottom:14px;"><label class="form-label">Mobile</label>
              <div style="display:flex;gap:8px;"><input class="form-input" id="rCode" type="text" value="+91" style="width:65px;flex-shrink:0;"/><input class="form-input" id="rMob" type="tel" placeholder="9876543210" style="flex:1;"/></div>
            </div>
            <div class="form-field" style="margin-bottom:14px;"><label class="form-label">Password</label>
              <div class="form-input-icon"><input class="form-input" id="rPwd" type="password" placeholder="Min 6 characters"/><i class="fa-regular fa-eye" onclick="tglPwd('rPwd',this)" style="cursor:pointer;"></i></div>
            </div>
            <div class="form-field" style="margin-bottom:20px;"><label class="form-label">Confirm Password</label><input class="form-input" id="rConf" type="password" placeholder="Re-enter password"/></div>
            <button class="btn-auth" id="rBtn" onclick="doReg()" type="button"><i class="fa-solid fa-user-plus"></i> Create Account</button>
            <p class="auth-footer-note">Have an account? <a href="#" onclick="switchTab('login')">Login</a></p>
          </div>
          <!-- OTP Step -->
          <div class="auth-step" id="step2">
            <div style="text-align:center;margin-bottom:20px;">
              <div style="width:64px;height:64px;background:rgba(229,52,58,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fa-solid fa-envelope" style="font-size:24px;color:var(--red);"></i></div>
              <h3 style="font-size:18px;font-weight:700;margin-bottom:6px;">Verify Email</h3>
              <p style="font-size:13px;color:#6B7280;">OTP sent to <strong id="otpEmail"></strong></p>
            </div>
            <div class="otp-inputs">@for($i=0;$i<6;$i++)<input class="otp-input" maxlength="1" type="number" min="0" max="9"/>@endfor</div>
            <button class="btn-auth" id="vBtn" onclick="doVerify()" type="button"><i class="fa-solid fa-check"></i> Verify &amp; Continue</button>
            <p class="auth-footer-note">Didn't receive? <a href="#" onclick="resendOtp()">Resend</a></p>
          </div>
        </div>
      </div>
    </div>
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
  document.getElementById('authH').textContent = t==='login'?'Welcome Back':'Create Account';
  document.getElementById('authP').textContent = t==='login'?'Login to your BigWein account':"Join BigWein — it's free";
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
    pendingEmail=email; pendingPwd=pwd;
    document.getElementById('otpEmail').textContent=email;
    bwToast('Account created! Check your email for OTP.','success');
    showStep2();
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

document.querySelectorAll('.otp-input').forEach((inp,i,all)=>{
  inp.addEventListener('input',()=>{ inp.value=inp.value.slice(-1); if(inp.value&&i<all.length-1)all[i+1].focus(); });
  inp.addEventListener('keydown',e=>{ if(e.key==='Backspace'&&!inp.value&&i>0)all[i-1].focus(); if(e.key==='Enter'&&i===all.length-1)doVerify(); });
});

function tglPwd(id,icon){ const i=document.getElementById(id); i.type=i.type==='password'?'text':'password'; icon.classList.toggle('fa-eye',i.type==='password'); icon.classList.toggle('fa-eye-slash',i.type==='text'); }
document.getElementById('lPwd').addEventListener('keypress',e=>{ if(e.key==='Enter')doLogin(); });
</script>
@endpush
