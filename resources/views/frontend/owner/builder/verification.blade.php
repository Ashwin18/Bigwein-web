@extends('frontend.owner.layouts.app')
@section('title','Builder Verification')
@section('page-title','Builder / Developer Verification')
@section('page-bread','Company KYC · Required before posting projects')

@push('styles')
<style>
.bvk{max-width:980px;margin:auto}.bvk-head{background:linear-gradient(135deg,#172033,#30203a);color:#fff;border-radius:18px;padding:22px;margin-bottom:14px}.bvk-head h2{margin:0;font-size:22px}.bvk-head p{font-size:11px;color:#cbd5e1;margin:7px 0 0}
.bvk-note{padding:11px 12px;border-radius:11px;font-size:11px;margin-bottom:13px}.pending{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.approved{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.rejected{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.bvk-card{background:#fff;border:1px solid #e5eaf1;border-radius:15px;padding:19px;margin-bottom:13px}.bvk-title{font-size:14px;font-weight:800;color:#172033;margin-bottom:14px}
.bvk-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.full{grid-column:1/-1}.f label{display:block;font-size:9px;text-transform:uppercase;font-weight:800;color:#64748b;margin-bottom:5px}.f input,.f select,.f textarea{width:100%;box-sizing:border-box;border:1px solid #dce3eb;border-radius:9px;padding:10px 11px;font-size:12px}.f textarea{min-height:85px}
.bvk-submit{width:100%;border:0;border-radius:11px;background:#ef3f45;color:#fff;padding:13px;font-weight:800}.locked{opacity:.68;pointer-events:none}
@media(max-width:700px){.bvk-grid{grid-template-columns:1fr}.full{grid-column:auto}}
</style>
@endpush

@section('content')
@php
  $status=strtolower((string)($profile->status ?? 'draft'));
  $locked=in_array($status,['submitted','under_review','approved']);
@endphp
<div class="bvk">
  <div class="bvk-head"><h2><i class="fa-solid fa-building-shield"></i> Verify your development company</h2><p>Personal Aadhaar KYC and company verification are separate. Both must be approved before project posting is enabled.</p></div>

  @if($status==='approved')
    <div class="bvk-note approved"><strong>✓ Company Verified.</strong> You can now post projects. Verified details are locked; contact admin for corrections.</div>
  @elseif(in_array($status,['submitted','under_review']))
    <div class="bvk-note pending"><strong>Under Review.</strong> Company details cannot be changed until admin completes the review.</div>
  @elseif(in_array($status,['changes_requested','rejected']))
    <div class="bvk-note rejected"><strong>{{ $status==='changes_requested'?'Changes Requested':'Verification Rejected' }}:</strong> {{ $profile->admin_remarks ?? 'Please update the company details and resubmit.' }}</div>
  @endif

  <form method="POST" action="{{ url('/owner/builder-verification') }}" enctype="multipart/form-data" class="{{ $locked?'locked':'' }}">@csrf
    <div class="bvk-card"><div class="bvk-title">Company Information</div><div class="bvk-grid">
      <div class="f full"><label>Developer / Company Name *</label><input name="company_name" value="{{ old('company_name',$profile->company_name ?? $owner->company_name ?? '') }}" required></div>
      <div class="f"><label>Company Type *</label><select name="company_type" required><option value="">Select</option>@foreach(['proprietorship'=>'Proprietorship','partnership'=>'Partnership','llp'=>'LLP','private_limited'=>'Private Limited','public_limited'=>'Public Limited','other'=>'Other'] as $k=>$v)<option value="{{ $k }}" {{ old('company_type',$profile->company_type ?? '')===$k?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
      <div class="f"><label>Authorised Contact Person *</label><input name="contact_person" value="{{ old('contact_person',$profile->contact_person ?? $owner->name) }}" required></div>
      <div class="f"><label>PAN Number *</label><input name="pan_number" value="{{ old('pan_number',$profile->pan_number ?? '') }}" required></div>
      <div class="f"><label>GST Number</label><input name="gst_number" value="{{ old('gst_number',$profile->gst_number ?? '') }}"></div>
      <div class="f"><label>CIN / LLPIN</label><input name="cin_llpin" value="{{ old('cin_llpin',$profile->cin_llpin ?? '') }}"></div>
      <div class="f"><label>RERA Promoter Registration</label><input name="rera_promoter_number" value="{{ old('rera_promoter_number',$profile->rera_promoter_number ?? '') }}"></div>
      <div class="f"><label>Years in Business</label><input type="number" min="0" name="years_in_business" value="{{ old('years_in_business',$profile->years_in_business ?? '') }}"></div>
      <div class="f"><label>Website</label><input name="website" value="{{ old('website',$profile->website ?? '') }}" placeholder="https://"></div>
      <div class="f"><label>City *</label><input name="city" value="{{ old('city',$profile->city ?? $owner->city ?? '') }}" required></div>
      <div class="f"><label>State *</label><input name="state" value="{{ old('state',$profile->state ?? $owner->state ?? '') }}" required></div>
      <div class="f full"><label>Registered Office Address *</label><textarea name="registered_office_address" required>{{ old('registered_office_address',$profile->registered_office_address ?? '') }}</textarea></div>
      <div class="f full"><label>About Developer</label><textarea name="about_developer">{{ old('about_developer',$profile->about_developer ?? '') }}</textarea></div>
    </div></div>

    <div class="bvk-card"><div class="bvk-title">Verification Documents</div><div class="bvk-grid">
      <div class="f"><label>Company Logo</label><input type="file" name="logo" accept=".jpg,.jpeg,.png,.webp"></div>
      <div class="f"><label>PAN Document *</label><input type="file" name="pan_document" accept=".jpg,.jpeg,.png,.pdf">@if($profile?->pan_document)<small>Already uploaded</small>@endif</div>
      <div class="f"><label>GST Certificate</label><input type="file" name="gst_certificate" accept=".jpg,.jpeg,.png,.pdf"></div>
      <div class="f"><label>Registration Certificate *</label><input type="file" name="registration_certificate" accept=".jpg,.jpeg,.png,.pdf">@if($profile?->registration_certificate)<small>Already uploaded</small>@endif</div>
      <div class="f"><label>RERA Promoter Certificate</label><input type="file" name="rera_certificate" accept=".jpg,.jpeg,.png,.pdf"></div>
      <div class="f"><label>Authorised Person Aadhaar</label><input type="file" name="authorised_person_aadhaar" accept=".jpg,.jpeg,.png,.pdf"></div>
    </div></div>

    @if(!$locked)<button class="bvk-submit"><i class="fa-solid fa-paper-plane"></i> Submit Company Verification</button>@endif
  </form>
</div>
@endsection