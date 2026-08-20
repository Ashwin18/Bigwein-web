<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Complete Your Profile — BigWein</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}"/>
<style>
body{background:#F8FAFC;display:flex;flex-direction:column;min-height:100vh;}
.setup-topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10;box-shadow:var(--shadow);}
.setup-wrap{max-width:760px;margin:40px auto;padding:0 20px 80px;width:100%;}
.setup-hero{text-align:center;margin-bottom:32px;}
.setup-hero h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:28px;font-weight:900;color:var(--navy);margin-bottom:8px;}
.setup-hero p{font-size:14px;color:var(--gray);}
.progress-steps-setup{display:flex;justify-content:center;gap:0;margin-bottom:36px;}
.pss{display:flex;align-items:center;gap:6px;}
.pss-dot{width:30px;height:30px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--gray2);background:#fff;flex-shrink:0;transition:all .3s;}
.pss.active .pss-dot{background:var(--red);border-color:var(--red);color:#fff;}
.pss.done .pss-dot{background:var(--green);border-color:var(--green);color:#fff;}
.pss-label{font-size:11px;font-weight:600;color:var(--gray2);}
.pss.active .pss-label{color:var(--red);}
.pss.done .pss-label{color:var(--green);}
.pss-line{width:40px;height:2px;background:var(--border);margin:0 4px;}
.pss-line.done{background:var(--green);}

.setup-section{display:none;}
.setup-section.active{display:block;animation:fadeUp .35s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.setup-card{background:#fff;border-radius:var(--r-xl);padding:28px;box-shadow:var(--shadow);margin-bottom:16px;border:1px solid var(--border);}
.setup-card-title{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:20px;display:flex;align-items:center;gap:8px;}
.setup-card-title i{color:var(--red);}

/* Avatar upload */
.avatar-upload-zone{display:flex;align-items:center;gap:20px;margin-bottom:20px;}
.avatar-preview{width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--red),#FF6B6B);display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:900;color:#fff;flex-shrink:0;overflow:hidden;position:relative;cursor:pointer;}
.avatar-preview img{width:100%;height:100%;object-fit:cover;}
.avatar-preview .camera-overlay{position:absolute;inset:0;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center;border-radius:50%;}
.avatar-preview:hover .camera-overlay{display:flex;}
.avatar-upload-info h4{font-size:14px;font-weight:700;color:var(--navy);margin-bottom:4px;}
.avatar-upload-info p{font-size:12px;color:var(--gray2);}
.avatar-upload-btn{margin-top:8px;padding:8px 16px;border:1.5px dashed var(--border);border-radius:8px;font-size:12px;font-weight:600;color:var(--gray);cursor:pointer;background:var(--bg);transition:all .2s;}
.avatar-upload-btn:hover{border-color:var(--red);color:var(--red);}

/* Social links */
.social-item{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.social-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}

/* Bottom nav */
.setup-nav{display:flex;align-items:center;justify-content:space-between;margin-top:20px;}
.skip-link{font-size:13px;color:var(--gray);cursor:pointer;background:none;border:none;font-family:'Poppins',sans-serif;}
.skip-link:hover{color:var(--navy);}
</style>
</head>
<body>

@php $cust = session('bw_customer'); @endphp

<header class="setup-topbar">
  <div class="logo-text"><span class="big" style="color:var(--red);font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:800;">Big</span><span class="wein" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:800;">Wein</span></div>
  <div style="font-size:13px;color:var(--gray);">Setting up your owner profile</div>
  <a href="/owner/dashboard" style="font-size:13px;color:var(--gray2);">Skip for now →</a>
</header>

<div class="setup-wrap">
  <div class="setup-hero">
    @if(session('success'))
    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 20px;display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#15803D;margin-bottom:20px;">
      <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif
    <h1>Complete Your Profile 🎉</h1>
    <p>A complete profile gets <strong>3× more enquiries</strong> — takes less than 2 minutes</p>
  </div>

  {{-- Progress --}}
  <div class="progress-steps-setup">
    <div class="pss active" id="pss1"><div class="pss-dot"><span>1</span></div><div class="pss-label">Basic Info</div></div>
    <div class="pss-line" id="pssl1"></div>
    <div class="pss" id="pss2"><div class="pss-dot"><span>2</span></div><div class="pss-label">About You</div></div>
    <div class="pss-line" id="pssl2"></div>
    <div class="pss" id="pss3"><div class="pss-dot"><span>3</span></div><div class="pss-label">All Set!</div></div>
  </div>

  {{-- ═══ SECTION 1: Basic Info ═══ --}}
  <div class="setup-section active" id="ss1">
    <form method="POST" action="{{ url('/owner/profile/update') }}" enctype="multipart/form-data" id="profileForm1">
      @csrf
      <div class="setup-card">
        <div class="setup-card-title"><i class="fa-solid fa-user-circle"></i> Profile Photo</div>
        <div class="avatar-upload-zone">
          <div class="avatar-preview" onclick="document.getElementById('avatarInput').click()" id="avatarPreview">
            @if(!empty($cust['profile']))
              <img src="{{ asset('images/'.config('global.USER_IMG_PATH','user_img/').$cust['profile']) }}" id="avatarImg"/>
            @else
              <span>{{ strtoupper(substr($cust['name']??'U',0,1)) }}</span>
            @endif
            <div class="camera-overlay"><i class="fa-solid fa-camera" style="color:#fff;font-size:20px;"></i></div>
          </div>
          <div class="avatar-upload-info">
            <h4>Upload your photo</h4>
            <p>JPG or PNG, max 2MB. A clear headshot builds trust with buyers.</p>
            <label class="avatar-upload-btn" for="avatarInput"><i class="fa-solid fa-cloud-arrow-up"></i> Choose Photo</label>
            <input type="file" id="avatarInput" name="profile" accept="image/*" style="display:none;" onchange="previewAvatar(event)"/>
          </div>
        </div>
      </div>

      <div class="setup-card">
        <div class="setup-card-title"><i class="fa-solid fa-id-card"></i> Personal Details</div>
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Full Name <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-user"></i>
            <input class="f-input" type="text" name="name" value="{{ $cust['name'] ?? '' }}" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Mobile <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-mobile-screen-button"></i>
            <input class="f-input" type="tel" name="mobile" value="{{ $cust['mobile'] ?? '' }}" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Alternate Phone</label>
            <div class="f-wrap"><i class="fa-solid fa-phone"></i>
            <input class="f-input" type="tel" name="phone_alt" value="{{ $cust['phone_alt'] ?? '' }}" placeholder="Optional"/>
            </div>
          </div>
          @if(($cust['owner_type'] ?? '') === 'builder')
          <div class="f-group">
            <label class="f-label">Company / Builder Name</label>
            <div class="f-wrap"><i class="fa-solid fa-building"></i>
            <input class="f-input" type="text" name="company_name" value="{{ $cust['company_name'] ?? '' }}" placeholder="Your company name"/>
            </div>
          </div>
          @endif
          <div class="f-group">
            <label class="f-label">City <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-city"></i>
            <input class="f-input" type="text" name="city" value="{{ $cust['city'] ?? '' }}" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">State</label>
            <div class="f-wrap"><i class="fa-solid fa-map"></i>
            <input class="f-input" type="text" name="state" value="{{ $cust['state'] ?? '' }}"/>
            </div>
          </div>
        </div>
      </div>

      <div class="setup-nav">
        <a href="/owner/dashboard" class="skip-link">Skip for now →</a>
        <button type="button" class="btn btn-red" onclick="saveAndNext(1)">Save &amp; Continue <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  {{-- ═══ SECTION 2: About ═══ --}}
  <div class="setup-section" id="ss2">
    <form method="POST" action="{{ url('/owner/profile/update') }}" enctype="multipart/form-data" id="profileForm2">
      @csrf
      <div class="setup-card">
        <div class="setup-card-title"><i class="fa-solid fa-pen-to-square"></i> About You</div>
        <div class="f-group" style="margin-bottom:16px;">
          <label class="f-label">Bio / About</label>
          <textarea class="f-textarea" name="about_me" rows="4" placeholder="Tell buyers about yourself — your experience, areas you specialize in, years in real estate…">{{ $cust['about_me'] ?? '' }}</textarea>
          <span class="f-hint">A good bio increases buyer trust by 40%</span>
        </div>
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Years of Experience</label>
            <div class="f-wrap"><i class="fa-solid fa-briefcase"></i>
            <select class="f-input f-select" name="experience">
              <option value="">Select</option>
              @foreach(['Less than 1 year','1-3 years','3-5 years','5-10 years','10+ years'] as $e)
              <option value="{{ $e }}">{{ $e }}</option>
              @endforeach
            </select></div>
          </div>
          <div class="f-group">
            <label class="f-label">Languages Spoken</label>
            <div class="f-wrap"><i class="fa-solid fa-language"></i>
            <input class="f-input" type="text" name="languages" placeholder="e.g. Tamil, English, Hindi"/>
            </div>
          </div>
        </div>
      </div>

      <div class="setup-card">
        <div class="setup-card-title"><i class="fa-solid fa-link"></i> Social Links <span style="font-size:11px;color:var(--gray2);font-weight:400;">(Optional)</span></div>
        <div class="social-item">
          <div class="social-icon" style="background:#E7F3FF;color:#1877F2;"><i class="fa-brands fa-facebook-f"></i></div>
          <div class="f-wrap" style="flex:1;"><i class="fa-brands fa-facebook-f" style="color:#1877F2;"></i>
          <input class="f-input" type="url" name="facebook" placeholder="Facebook profile URL"/>
          </div>
        </div>
        <div class="social-item">
          <div class="social-icon" style="background:#E4F6FE;color:#1DA1F2;"><i class="fa-brands fa-twitter"></i></div>
          <div class="f-wrap" style="flex:1;"><i class="fa-brands fa-twitter" style="color:#1DA1F2;"></i>
          <input class="f-input" type="url" name="twitter" placeholder="Twitter / X profile URL"/>
          </div>
        </div>
        <div class="social-item">
          <div class="social-icon" style="background:#E7F8EE;color:#25D366;"><i class="fa-brands fa-whatsapp"></i></div>
          <div class="f-wrap" style="flex:1;"><i class="fa-brands fa-whatsapp" style="color:#25D366;"></i>
          <input class="f-input" type="text" name="whatsapp" placeholder="+91 WhatsApp number"/>
          </div>
        </div>
      </div>

      <div class="setup-nav">
        <button type="button" class="skip-link" onclick="goSetupStep(1)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="saveAndNext(2)">Save &amp; Finish <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  {{-- ═══ SECTION 3: Done ═══ --}}
  <div class="setup-section" id="ss3">
    <div style="text-align:center;padding:40px 20px;background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);box-shadow:var(--shadow);">
      <div style="width:90px;height:90px;background:linear-gradient(135deg,#DCFCE7,#BBF7D0);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <i class="fa-solid fa-check" style="font-size:40px;color:#16A34A;"></i>
      </div>
      <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:26px;font-weight:900;color:var(--navy);margin-bottom:8px;">Profile Complete! 🎉</h2>
      <p style="font-size:14px;color:var(--gray);margin-bottom:28px;">Your owner profile is all set. Start posting properties and connect with buyers!</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="/owner/post-property" class="btn btn-red"><i class="fa-solid fa-plus"></i> Post First Property</a>
        <a href="/owner/dashboard" class="btn btn-outline">Go to Dashboard →</a>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('frontend/js/owner.js') }}"></script>
<script>
function goSetupStep(n) {
  document.querySelectorAll('.setup-section').forEach(s => s.classList.remove('active'));
  document.getElementById('ss' + n).classList.add('active');
  [1,2,3].forEach(i => {
    const el = document.getElementById('pss' + i);
    el.classList.remove('active','done');
    if(i < n) { el.classList.add('done'); el.querySelector('.pss-dot').innerHTML = '<i class="fa-solid fa-check" style="font-size:11px;"></i>'; }
    else if(i === n) { el.classList.add('active'); el.querySelector('.pss-dot').innerHTML = '<span>'+i+'</span>'; }
    else { el.querySelector('.pss-dot').innerHTML = '<span>'+i+'</span>'; }
    if(i < 3) document.getElementById('pssl'+i).classList.toggle('done', i < n);
  });
  window.scrollTo({top:0,behavior:'smooth'});
}

function previewAvatar(e) {
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    const p = document.getElementById('avatarPreview');
    p.innerHTML = `<img src="${ev.target.result}" id="avatarImg" style="width:100%;height:100%;object-fit:cover;"/><div class="camera-overlay"><i class="fa-solid fa-camera" style="color:#fff;font-size:20px;"></i></div>`;
  };
  reader.readAsDataURL(file);
}

async function saveAndNext(step) {
  const form = document.getElementById('profileForm' + step);
  const formData = new FormData(form);
  const btn = event.currentTarget;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';
  btn.disabled = true;

  try {
    const res = await fetch('/owner/profile/update', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '', 'Accept': 'application/json' },
      body: formData
    });
    const data = await res.json().catch(() => ({}));
    showToast('Saved successfully!', 'success');
    if(step < 2) { goSetupStep(step + 1); }
    else { goSetupStep(3); }
  } catch(e) {
    // Fallback: submit normally
    form.submit();
  }

  btn.innerHTML = step < 2 ? 'Save & Continue <i class="fa-solid fa-arrow-right"></i>' : 'Save & Finish <i class="fa-solid fa-arrow-right"></i>';
  btn.disabled = false;
}
</script>
</body>
</html>
