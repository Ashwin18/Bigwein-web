@extends('frontend.layouts.app')
@section('title','Edit Profile — BigWein')
@section('content')

<style>
.ud-wrap{max-width:700px;margin:0 auto;padding:28px 16px 60px;}
.ud-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
.ud-nav a{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;color:#374151;}
.ud-nav a.active{background:var(--red);color:#fff;border-color:var(--red);}
.prof-card{background:#fff;border:1px solid #F1F5F9;border-radius:16px;padding:28px;margin-bottom:16px;}
.prof-avatar{width:80px;height:80px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#fff;margin:0 auto 16px;}
.form-grp{margin-bottom:18px;}
.form-lbl{display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;}
.form-inp{width:100%;padding:10px 14px;border:1px solid #E2E8F0;border-radius:10px;font-size:14px;color:#0F172A;outline:none;transition:border-color .15s;font-family:inherit;}
.form-inp:focus{border-color:var(--red);}
.form-inp:disabled{background:#F8FAFC;color:#94A3B8;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.prof-save-btn{width:100%;padding:13px;background:var(--red);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;}
.prof-save-btn:hover{background:#C4272D;}
.danger-zone{background:#FFF1F3;border:1px solid #FECDD3;border-radius:12px;padding:18px;margin-top:8px;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
</style>

<div class="ud-wrap">
  <div style="margin-bottom:18px;">
    <h1 style="font-size:22px;font-weight:800;color:#0F172A;margin-bottom:2px;"><i class="fa-solid fa-user" style="color:var(--red);margin-right:8px;"></i>Edit Profile</h1>
  </div>

  <div class="ud-nav">
    <a href="/user/dashboard"><i class="fa-solid fa-grid-2 fa-xs"></i> Dashboard</a>
    <a href="/user/saved"><i class="fa-solid fa-heart fa-xs"></i> Saved</a>
    <a href="/user/enquiries"><i class="fa-solid fa-message fa-xs"></i> Enquiries</a>
    <a href="/user/profile" class="active"><i class="fa-solid fa-user fa-xs"></i> Profile</a>
  </div>

  {{-- Profile info --}}
  <div class="prof-card">
    <div class="prof-avatar">{{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}</div>
    <div id="profMsg" style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;text-align:center;margin-bottom:16px;"></div>

    <div class="form-row">
      <div class="form-grp">
        <label class="form-lbl">Full Name</label>
        <input type="text" id="profName" class="form-inp" value="{{ $u->name ?? '' }}" placeholder="Your full name"/>
      </div>
      <div class="form-grp">
        <label class="form-lbl">Mobile</label>
        <input type="tel" id="profMobile" class="form-inp" value="{{ $u->mobile ?? '' }}" placeholder="+91 9876543210"/>
      </div>
    </div>

    <div class="form-grp">
      <label class="form-lbl">Email <span style="font-size:11px;color:#94A3B8;">(cannot be changed)</span></label>
      <input type="email" class="form-inp" value="{{ $u->email ?? '' }}" disabled/>
    </div>

    <div style="border-top:1px solid #F1F5F9;padding-top:20px;margin-top:4px;">
      <div style="font-size:14px;font-weight:700;color:#0F172A;margin-bottom:14px;"><i class="fa-solid fa-lock" style="color:var(--red);margin-right:6px;"></i> Change Password</div>
      <div class="form-row">
        <div class="form-grp">
          <label class="form-lbl">New Password</label>
          <input type="password" id="profPwd" class="form-inp" placeholder="Min 6 characters"/>
        </div>
        <div class="form-grp">
          <label class="form-lbl">Confirm Password</label>
          <input type="password" id="profPwdConf" class="form-inp" placeholder="Re-enter password"/>
        </div>
      </div>
      <p style="font-size:12px;color:#94A3B8;margin-top:-8px;">Leave blank to keep current password.</p>
    </div>

    <button class="prof-save-btn" onclick="saveProfile()">
      <i class="fa-solid fa-floppy-disk"></i> Save Changes
    </button>
  </div>

  {{-- Account info --}}
  <div class="prof-card" style="padding:18px 22px;">
    <div style="font-size:14px;font-weight:700;color:#0F172A;margin-bottom:12px;"><i class="fa-solid fa-circle-info" style="color:var(--red);margin-right:6px;"></i> Account Info</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px;">
      <div style="color:#64748B;">Member since</div>
      <div style="font-weight:600;color:#0F172A;">{{ \Carbon\Carbon::parse($u->created_at)->format('M Y') }}</div>
      <div style="color:#64748B;">Account status</div>
      <div><span style="background:#DCFCE7;color:#166534;font-size:11px;font-weight:700;padding:3px 10px;border-radius:8px;">Active</span></div>
      <div style="color:#64748B;">Email verified</div>
      <div>
        @if($u->is_email_verified)
          <span style="background:#DCFCE7;color:#166534;font-size:11px;font-weight:700;padding:3px 10px;border-radius:8px;"><i class="fa-solid fa-check"></i> Verified</span>
        @else
          <span style="background:#FEE2E2;color:#991B1B;font-size:11px;font-weight:700;padding:3px 10px;border-radius:8px;">Not verified</span>
        @endif
      </div>
    </div>
  </div>

  {{-- Danger zone --}}
  <div class="danger-zone">
    <div style="font-size:13px;font-weight:700;color:#9F1239;margin-bottom:6px;"><i class="fa-solid fa-triangle-exclamation"></i> Logout</div>
    <p style="font-size:12px;color:#64748B;margin-bottom:12px;">You will be logged out from all sessions.</p>
    <button onclick="bwLogout()" style="background:#E5343A;color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:700;cursor:pointer;">
      <i class="fa-solid fa-right-from-bracket"></i> Logout
    </button>
  </div>
</div>
@endsection
@section('script')
<script>
async function saveProfile() {
  const name = document.getElementById('profName').value.trim();
  const mobile = document.getElementById('profMobile').value.trim();
  const pwd = document.getElementById('profPwd').value;
  const conf = document.getElementById('profPwdConf').value;
  const msgEl = document.getElementById('profMsg');

  if (!name) { showMsg('Name is required.', false); return; }
  if (pwd && pwd.length < 6) { showMsg('Password must be at least 6 characters.', false); return; }
  if (pwd && pwd !== conf) { showMsg('Passwords do not match.', false); return; }

  const body = {name, mobile};
  if (pwd) body.password = pwd;

  const res = await fetch('/user/profile/update', {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify(body)
  });
  const d = await res.json();
  showMsg(d.message, !d.error);
  if (!d.error) { document.getElementById('profPwd').value=''; document.getElementById('profPwdConf').value=''; }
}

function showMsg(msg, ok) {
  const el = document.getElementById('profMsg');
  el.textContent = msg;
  el.style.display = 'block';
  el.style.background = ok ? '#DCFCE7' : '#FEE2E2';
  el.style.color = ok ? '#166534' : '#991B1B';
  setTimeout(() => el.style.display='none', 3500);
}
</script>
@endsection
