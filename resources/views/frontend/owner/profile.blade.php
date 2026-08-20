@extends('frontend.owner.layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')
@section('page-bread','Manage your account details')

@section('content')
<div class="profile-layout">
  {{-- Left sidebar --}}
  <div>
    <div class="profile-sidebar-card">
      <div class="profile-avatar-big" id="avatarPreview">
        @if($full->profile)
          <img src="{{ asset('images/'.config('global.USER_IMG_PATH','user_img/').$full->profile) }}" style="width:90px;height:90px;border-radius:50%;object-fit:cover;" alt=""/>
        @else
          {{ strtoupper(substr($full->name, 0, 1)) }}
        @endif
        <label class="avatar-edit" title="Change photo" for="profileImgInput"><i class="fa-solid fa-camera"></i></label>
      </div>
      <div class="prof-name">{{ $full->name }}</div>
      <div class="prof-email">{{ $full->email }}</div>
      @if($full->is_email_verified)
        <div class="prof-verified"><i class="fa-solid fa-circle-check"></i> Email Verified</div>
      @endif
      <div class="prof-type-badge">
        <i class="fa-solid fa-{{ $full->owner_type === 'builder' ? 'building-columns' : 'user-tie' }}"></i>
        {{ $full->owner_type === 'builder' ? 'Builder / Developer' : 'Seller / Owner' }}
      </div>
      @if($full->company_name)
      <div style="font-size:12px;color:var(--gray2);margin-top:8px;"><i class="fa-solid fa-building" style="color:var(--red);margin-right:4px;"></i> {{ $full->company_name }}</div>
      @endif
      <div class="prof-stats">
        @php
          $myListings  = \Illuminate\Support\Facades\DB::table('propertys')->where('added_by',$full->id)->where('post_type',1)->count();
          $myEnquiries = \Illuminate\Support\Facades\DB::table('interested_users')->whereIn('property_id', \Illuminate\Support\Facades\DB::table('propertys')->where('added_by',$full->id)->pluck('id'))->count();
          $myViews     = (int) \Illuminate\Support\Facades\DB::table('propertys')->where('added_by',$full->id)->sum('total_click');
        @endphp
        <div class="ps-box"><div class="num">{{ $myListings }}</div><div class="lbl">Listings</div></div>
        <div class="ps-box"><div class="num">{{ $myEnquiries }}</div><div class="lbl">Enquiries</div></div>
        <div class="ps-box"><div class="num">{{ number_format($myViews/1000,1) }}K</div><div class="lbl">Views</div></div>
        <div class="ps-box"><div class="num">{{ \Carbon\Carbon::parse($full->created_at)->diffInMonths(now()) }}m</div><div class="lbl">Member</div></div>
      </div>

      @php
        $fields = ['name','mobile','city','state','about_me'];
        $filled = collect($fields)->filter(fn($f) => !empty($full->$f))->count();
        $pct = round(($filled / count($fields)) * 100);
        if($full->profile) $pct = min(100, $pct + 10);
        if($full->company_name) $pct = min(100, $pct + 5);
      @endphp
      <div class="completion-bar">
        <div class="cb-head"><span style="color:var(--navy);">Profile Strength</span><span style="color:var(--red);">{{ $pct }}%</span></div>
        <div class="cb-track"><div class="cb-fill" style="width:{{ $pct }}%;"></div></div>
        @if($pct < 100)
        <div style="font-size:11px;color:var(--gray2);margin-top:6px;">Complete all fields for 100%</div>
        @endif
      </div>

      <a href="{{ url('/owner/my-properties') }}" class="btn btn-red" style="width:100%;justify-content:center;margin-top:16px;font-size:13px;padding:10px;">
        <i class="fa-solid fa-building"></i> View My Properties
      </a>
    </div>
  </div>

  {{-- Right forms --}}
  <div>
    {{-- Personal info --}}
    <div class="form-card">
      <div class="form-card-title"><i class="fa-solid fa-user-pen"></i> Personal Information</div>
      <form method="POST" action="{{ url('/owner/profile/update') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="profile" id="profileImgInput" accept="image/*" style="display:none;" onchange="previewAvatar(event)"/>

        <div class="fg">
          <div class="f-group">
            <label class="f-label">Full Name <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-user"></i>
            <input class="f-input" type="text" name="name" value="{{ old('name', $full->name) }}" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Email Address</label>
            <div class="f-wrap"><i class="fa-solid fa-envelope"></i>
            <input class="f-input" type="email" value="{{ $full->email }}" readonly style="background:var(--bg);cursor:not-allowed;"/>
            </div>
            <span class="f-hint">Email cannot be changed</span>
          </div>
          <div class="f-group">
            <label class="f-label">Mobile Number <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-mobile-screen-button"></i>
            <input class="f-input" type="tel" name="mobile" value="{{ old('mobile', $full->mobile) }}" required/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Alternate Mobile</label>
            <div class="f-wrap"><i class="fa-solid fa-phone"></i>
            <input class="f-input" type="tel" name="phone_alt" value="{{ old('phone_alt', $full->phone_alt??'') }}" placeholder="Optional"/>
            </div>
          </div>
          @if($full->owner_type === 'builder')
          <div class="f-group span2">
            <label class="f-label">Company / Builder Name</label>
            <div class="f-wrap"><i class="fa-solid fa-building"></i>
            <input class="f-input" type="text" name="company_name" value="{{ old('company_name', $full->company_name) }}" placeholder="Your company name"/>
            </div>
          </div>
          @endif
          <div class="f-group">
            <label class="f-label">City</label>
            <div class="f-wrap"><i class="fa-solid fa-city"></i>
            <input class="f-input" type="text" name="city" value="{{ old('city', $full->city) }}" placeholder="Your city"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">State</label>
            <div class="f-wrap"><i class="fa-solid fa-map"></i>
            <input class="f-input" type="text" name="state" value="{{ old('state', $full->state) }}" placeholder="Your state"/>
            </div>
          </div>
          <div class="f-group span2">
            <label class="f-label">About You / Bio</label>
            <textarea class="f-textarea" name="about_me" rows="3" placeholder="Tell buyers about your experience, areas you deal in, etc.">{{ old('about_me', $full->about_me) }}</textarea>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
          <button type="submit" class="btn btn-red"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
        </div>
      </form>
    </div>

    {{-- Identity Verification --}}
    @php
      $profileKyc = \Illuminate\Support\Facades\DB::table('customer_kyc')->where('customer_id',$full->id)->latest('id')->first();
      $profileKycStatus = strtolower((string)($full->kyc_status ?? $profileKyc->status ?? 'pending'));
      $profileKycApproved = $profileKycStatus === 'approved';
    @endphp
    <div class="form-card" style="border:1px solid {{ $profileKycApproved ? '#BBF7D0' : 'var(--border)' }};">
      <div class="form-card-title"><i class="fa-solid fa-id-card"></i> Identity Verification
        <span style="margin-left:auto;font-size:11px;padding:5px 9px;border-radius:999px;background:{{ $profileKycApproved ? '#DCFCE7' : ($profileKycStatus==='submitted' ? '#DBEAFE' : '#FEF3C7') }};color:{{ $profileKycApproved ? '#166534' : ($profileKycStatus==='submitted' ? '#1D4ED8' : '#92400E') }};">{{ $profileKycApproved ? 'KYC Approved' : ucfirst(str_replace('_',' ',$profileKycStatus)) }}</span>
      </div>
      @if($profileKycStatus==='rejected' && !empty($full->kyc_reject_reason))
        <div style="background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;padding:10px 12px;border-radius:10px;font-size:12px;margin-bottom:14px;"><strong>Rejected:</strong> {{ $full->kyc_reject_reason }}</div>
      @endif
      <form method="POST" action="{{ url('/owner/kyc') }}" enctype="multipart/form-data">@csrf
        <div class="fg">
          <div class="f-group span2">
            <label class="f-label">Aadhaar Number <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-fingerprint"></i><input class="f-input" name="aadhaar_number" maxlength="12" inputmode="numeric" value="{{ old('aadhaar_number',$full->aadhaar_number ?? $profileKyc->aadhaar_number ?? '') }}" placeholder="12-digit Aadhaar number" {{ $profileKycApproved ? 'readonly' : '' }} required></div>
          </div>
          <div class="f-group"><label class="f-label">Aadhaar Front <span class="f-req">*</span></label><input class="f-input" style="padding:10px" type="file" name="aadhaar_front" accept="image/jpeg,image/png,image/webp" {{ (!$profileKycApproved && empty($profileKyc->aadhaar_front)) ? 'required' : '' }} {{ $profileKycApproved ? 'disabled' : '' }}></div>
          <div class="f-group"><label class="f-label">Aadhaar Back <span style="font-weight:500;color:var(--gray)">(Optional)</span></label><input class="f-input" style="padding:10px" type="file" name="aadhaar_back" accept="image/jpeg,image/png,image/webp" {{ $profileKycApproved ? 'disabled' : '' }}></div>
        </div>
        @if(!$profileKycApproved)<div style="display:flex;justify-content:flex-end;margin-top:16px;"><button type="submit" class="btn btn-red"><i class="fa-solid fa-shield-halved"></i> Submit KYC</button></div>@endif
      </form>
    </div>

    {{-- Change password --}}
    <div class="form-card">
      <div class="form-card-title"><i class="fa-solid fa-lock"></i> Change Password</div>
      <form method="POST" action="{{ url('/owner/profile/password') }}">
        @csrf
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Current Password <span class="f-req">*</span></label>
            <div class="f-wrap" style="position:relative;"><i class="fa-solid fa-lock"></i>
            <input class="f-input" type="password" name="current_password" placeholder="Your current password" id="cp1" required/>
            <button type="button" class="pw-toggle" onclick="tpw('cp1',this)"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">New Password <span class="f-req">*</span></label>
            <div class="f-wrap" style="position:relative;"><i class="fa-solid fa-key"></i>
            <input class="f-input" type="password" name="password" placeholder="Min 6 characters" id="cp2" required/>
            <button type="button" class="pw-toggle" onclick="tpw('cp2',this)"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Confirm New Password <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-lock"></i>
            <input class="f-input" type="password" name="password_confirmation" placeholder="Re-enter new password" required/>
            </div>
          </div>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
          <button type="submit" class="btn btn-outline">Update Password</button>
        </div>
      </form>
    </div>

    {{-- Danger zone --}}
    <div class="form-card" style="border:1px solid var(--red-light);">
      <div class="form-card-title" style="color:var(--red);"><i class="fa-solid fa-triangle-exclamation"></i> Account Settings</div>
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--navy);">Owner Type</div>
          <div style="font-size:12px;color:var(--gray);">Currently registered as: <strong>{{ ucfirst($full->owner_type) }}</strong>. Contact support to change.</div>
        </div>
        <form method="POST" action="{{ url('/owner/logout') }}">
          @csrf
          <button type="submit" class="btn btn-outline" style="border-color:var(--red);color:var(--red);">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('avatarPreview').innerHTML =
            `<img src="${ev.target.result}" style="width:90px;height:90px;border-radius:50%;object-fit:cover;"/><label class="avatar-edit" for="profileImgInput"><i class="fa-solid fa-camera"></i></label>`;
    };
    reader.readAsDataURL(file);
}
function tpw(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.innerHTML = inp.type === 'password' ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
}
</script>
@endpush
