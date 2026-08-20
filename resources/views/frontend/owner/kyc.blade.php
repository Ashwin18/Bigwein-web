@extends('frontend.owner.layouts.app')
@section('title','KYC Verification')
@section('page-title','KYC Verification')
@section('page-bread','Verify your identity to start posting properties')

@section('content')
@php
  $status = strtolower((string)($owner->kyc_status ?? ($kyc->status ?? 'pending')));
  $approved = $status === 'approved';
  $submitted = in_array($status,['submitted','under_review']);
  $rejected = $status === 'rejected';
  $changesRequested = $status === 'changes_requested';
  $editable = !$approved && !$submitted;
  $mask = !empty($owner->aadhaar_number) ? 'XXXX XXXX '.substr($owner->aadhaar_number,-4) : null;
@endphp

<style>
.kyc-hero{background:linear-gradient(135deg,#111827,#232C40);border-radius:20px;padding:26px 28px;color:white;display:flex;justify-content:space-between;gap:24px;align-items:center;margin-bottom:20px;overflow:hidden;position:relative}.kyc-hero:after{content:'';position:absolute;width:230px;height:230px;border-radius:50%;right:-90px;bottom:-130px;background:rgba(229,52,58,.18)}.kyc-hero h2{font-size:23px;margin:0 0 7px}.kyc-hero p{font-size:13px;color:rgba(255,255,255,.65);margin:0;max-width:620px}.ks{position:relative;z-index:1;padding:9px 13px;border-radius:999px;font-size:12px;font-weight:800;white-space:nowrap}.ks.pending{background:#FFF2CC;color:#9A6A00}.ks.submitted{background:#E8F2FF;color:#1E62B5}.ks.approved{background:#DCFCE7;color:#166534}.ks.rejected{background:#FDE8E8;color:#B42318}.ks.changes_requested{background:#FFF4E5;color:#B35C00}.kyc-grid{display:grid;grid-template-columns:1.35fr .65fr;gap:20px}.kyc-card{background:white;border:1px solid var(--border);border-radius:18px;padding:24px;box-shadow:var(--shadow)}.kyc-title{font-size:16px;font-weight:800;color:var(--navy);margin-bottom:5px}.kyc-sub{font-size:12px;color:var(--gray);margin-bottom:20px}.kyc-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.kyc-field.full{grid-column:1/-1}.kyc-field label{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;font-weight:800;color:var(--navy);margin-bottom:7px}.kyc-input{width:100%;height:46px;border:1px solid var(--border);border-radius:10px;padding:0 13px;font:inherit;font-size:13px;outline:none}.kyc-input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(229,52,58,.08)}.upload-box{border:1.5px dashed #CBD5E1;background:#F8FAFC;border-radius:12px;padding:18px;text-align:center}.upload-box i{font-size:24px;color:var(--red);display:block;margin-bottom:8px}.upload-box input{width:100%;font-size:12px}.kyc-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}.kyc-btn{border:0;border-radius:10px;padding:11px 17px;font-weight:800;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px}.kyc-primary{background:var(--red);color:#fff}.kyc-light{background:#fff;color:#475569;border:1px solid var(--border)}.kyc-side-item{display:flex;gap:12px;padding:13px 0;border-bottom:1px solid #EEF2F6}.kyc-side-item:last-child{border-bottom:0}.kyc-side-item i{width:28px;height:28px;border-radius:8px;background:var(--red-light);color:var(--red);display:flex;align-items:center;justify-content:center}.kyc-side-item strong{font-size:12px;display:block}.kyc-side-item span{font-size:11px;color:var(--gray);display:block;margin-top:2px}.kyc-reject{background:#FFF1F2;border:1px solid #FECDD3;color:#9F1239;border-radius:12px;padding:13px;margin-bottom:16px;font-size:12px}.kyc-approved{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;border-radius:12px;padding:16px;font-size:13px;margin-bottom:16px}.doc-preview{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}.doc-preview a{font-size:11px;color:var(--red);font-weight:700;text-decoration:none}@media(max-width:850px){.kyc-grid{grid-template-columns:1fr}.kyc-hero{align-items:flex-start;flex-direction:column}.kyc-form-grid{grid-template-columns:1fr}.kyc-field.full{grid-column:auto}}
</style>

<div class="kyc-hero">
  <div>
    <h2><i class="fa-solid fa-shield-halved" style="color:#ff676c;margin-right:8px"></i>Identity Verification</h2>
    <p>BigWein requires Seller / Owner and Builder / Developer accounts to complete Aadhaar KYC before posting properties or projects.</p>
  </div>
  <span class="ks {{ $status }}">{{ $approved ? '✓ KYC Approved' : ($submitted ? '⏳ Under Review' : ($changesRequested ? '✎ Changes Requested' : ($rejected ? '✕ Rejected' : '⚠ KYC Pending'))) }}</span>
</div>

<div class="kyc-grid">
  <div class="kyc-card">
    <div class="kyc-title">Aadhaar Verification</div>
    <div class="kyc-sub">Enter your 12-digit Aadhaar number and upload a clear image of the card. The back image is optional.</div>

    @if($approved)
      <div class="kyc-approved"><i class="fa-solid fa-circle-check"></i> Your identity is verified. You can now post properties and projects.</div>
    @elseif($submitted)
      <div class="kyc-approved" style="background:#EFF6FF;border-color:#BFDBFE;color:#1D4ED8"><i class="fa-solid fa-clock"></i> Your KYC is submitted and waiting for admin approval. Posting will unlock automatically after approval.</div>
    @elseif($changesRequested)
      <div class="kyc-reject" style="background:#FFF7ED;border-color:#FED7AA;color:#9A3412">
        <strong><i class="fa-solid fa-pen-to-square"></i> Admin Requested Changes</strong><br>
        {{ $owner->kyc_reject_reason ?: ($kyc->remarks ?? 'Please update the requested KYC information and submit it again.') }}
      </div>
    @elseif($rejected)
      <div class="kyc-reject"><strong>KYC Rejected</strong><br>{{ $owner->kyc_reject_reason ?: ($kyc->remarks ?? 'Please review your Aadhaar details and submit again.') }}</div>
    @endif

    @if($submitted || $approved)
      {{-- Locked review state: seller cannot alter KYC while admin is reviewing it. --}}
      <div class="kyc-form-grid">
        <div class="kyc-field full">
          <label>Aadhaar Number</label>
          <div class="kyc-input" style="display:flex;align-items:center;background:#F8FAFC;color:#475569;cursor:not-allowed">
            {{ $mask ?: 'Aadhaar submitted' }}
            <span style="margin-left:auto;font-size:10px;font-weight:800;color:#94A3B8"><i class="fa-solid fa-lock"></i> LOCKED</span>
          </div>
          @if($submitted)
            <div style="font-size:11px;color:#64748B;margin-top:6px">Your submitted Aadhaar details are locked until the admin completes review or requests a modification.</div>
          @endif
        </div>

        <div class="kyc-field">
          <label>Aadhaar Front</label>
          <div class="upload-box" style="min-height:112px;display:flex;flex-direction:column;justify-content:center">
            <i class="fa-solid fa-id-card"></i>
            @if(!empty($kyc->aadhaar_front))
              <strong style="font-size:12px;color:#334155">Document submitted</strong>
            @else
              <strong style="font-size:12px;color:#94A3B8">No document</strong>
            @endif
          </div>
          @if(!empty($kyc->aadhaar_front))
            <div class="doc-preview"><a target="_blank" href="{{ asset('images/customer_kyc/'.$owner->id.'/'.$kyc->aadhaar_front) }}">View submitted front →</a></div>
          @endif
        </div>

        <div class="kyc-field">
          <label>Aadhaar Back <span style="font-weight:500;color:var(--gray)">(Optional)</span></label>
          <div class="upload-box" style="min-height:112px;display:flex;flex-direction:column;justify-content:center">
            <i class="fa-solid fa-id-card-clip"></i>
            @if(!empty($kyc->aadhaar_back))
              <strong style="font-size:12px;color:#334155">Document submitted</strong>
            @else
              <strong style="font-size:12px;color:#94A3B8">Not submitted</strong>
            @endif
          </div>
          @if(!empty($kyc->aadhaar_back))
            <div class="doc-preview"><a target="_blank" href="{{ asset('images/customer_kyc/'.$owner->id.'/'.$kyc->aadhaar_back) }}">View submitted back →</a></div>
          @endif
        </div>
      </div>

      <div class="kyc-actions">
        <a class="kyc-btn kyc-light" href="{{ url('/owner/dashboard') }}"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
      </div>
    @else
      <form method="POST" action="{{ url('/owner/kyc') }}" enctype="multipart/form-data">
        @csrf
        <div class="kyc-form-grid">
          <div class="kyc-field full">
            <label>Aadhaar Number *</label>
            <input class="kyc-input" type="text" name="aadhaar_number" maxlength="12" inputmode="numeric" pattern="[0-9]{12}" value="{{ old('aadhaar_number', $owner->aadhaar_number ?? $kyc->aadhaar_number ?? '') }}" placeholder="Enter 12-digit Aadhaar number" required>
            @if($mask)<div style="font-size:11px;color:var(--gray);margin-top:5px">Saved Aadhaar: {{ $mask }}</div>@endif
          </div>

          <div class="kyc-field">
            <label>Aadhaar Front *</label>
            <div class="upload-box"><i class="fa-solid fa-id-card"></i><input type="file" name="aadhaar_front" accept="image/jpeg,image/png,image/webp" {{ empty($kyc->aadhaar_front) ? 'required' : '' }}></div>
            @if(!empty($kyc->aadhaar_front))<div class="doc-preview"><a target="_blank" href="{{ asset('images/customer_kyc/'.$owner->id.'/'.$kyc->aadhaar_front) }}">View current front →</a></div>@endif
          </div>

          <div class="kyc-field">
            <label>Aadhaar Back <span style="font-weight:500;color:var(--gray)">(Optional)</span></label>
            <div class="upload-box"><i class="fa-solid fa-id-card-clip"></i><input type="file" name="aadhaar_back" accept="image/jpeg,image/png,image/webp"></div>
            @if(!empty($kyc->aadhaar_back))<div class="doc-preview"><a target="_blank" href="{{ asset('images/customer_kyc/'.$owner->id.'/'.$kyc->aadhaar_back) }}">View current back →</a></div>@endif
          </div>
        </div>

        <div class="kyc-actions">
          <button class="kyc-btn kyc-primary" type="submit">
            <i class="fa-solid fa-paper-plane"></i>
            {{ ($rejected || $changesRequested) ? 'Update & Resubmit KYC' : 'Submit KYC' }}
          </button>
          <a class="kyc-btn kyc-light" href="{{ url('/owner/dashboard') }}">Back to Dashboard</a>
        </div>
      </form>

      @if(!$rejected && !$changesRequested)
        <form method="POST" action="{{ url('/owner/kyc/skip') }}" style="margin-top:10px">
          @csrf
          <button type="submit" style="border:0;background:transparent;color:var(--gray);font-size:12px;cursor:pointer">Skip for now →</button>
        </form>
      @endif
    @endif
  </div>

  <div class="kyc-card">
    <div class="kyc-title">Why KYC?</div>
    <div class="kyc-sub">KYC improves marketplace trust and helps prevent fake listings.</div>
    <div class="kyc-side-item"><i class="fa-solid fa-user-check"></i><div><strong>Verified Owner</strong><span>Confirms the identity behind the listing.</span></div></div>
    <div class="kyc-side-item"><i class="fa-solid fa-building-shield"></i><div><strong>Posting Access</strong><span>Post Property / Project unlocks only after approval.</span></div></div>
    <div class="kyc-side-item"><i class="fa-solid fa-lock"></i><div><strong>Private Documents</strong><span>Aadhaar is used for admin verification and is not shown publicly.</span></div></div>
    <div class="kyc-side-item"><i class="fa-solid fa-rotate"></i><div><strong>Re-submit if needed</strong><span>If rejected, correct the document and submit again.</span></div></div>
  </div>
</div>
@endsection
