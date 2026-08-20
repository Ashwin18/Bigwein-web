@extends('frontend.owner.layouts.app')
@section('title','Dashboard')
@section('page-title','Dashboard')
@section('page-bread','Welcome back, ' . ($cust['name'] ?? 'Owner'))

@section('content')
@php
  $isBuilder = ($cust['owner_type'] ?? '') === 'builder';
  $ownerIdForKyc = $cust['id'] ?? $cust['customer_id'] ?? null;
  if(!$ownerIdForKyc && !empty($cust['email'])) $ownerIdForKyc = \Illuminate\Support\Facades\DB::table('customers')->where('email',$cust['email'])->value('id');
  $kycOwner = $ownerIdForKyc ? \Illuminate\Support\Facades\DB::table('customers')->where('id',$ownerIdForKyc)->first() : null;
  $kycStatus = strtolower((string)($kycOwner->kyc_status ?? 'pending'));
  $kycApproved = $kycStatus === 'approved';
@endphp

{{-- Greeting bar --}}
<div class="greeting-bar">
  <div class="gb-text">
    <h2>{{ $isBuilder ? '🏗️' : '👋' }} Good day, {{ explode(' ', $cust['name'])[0] ?? 'there' }}!</h2>
    <p>
      @if($isBuilder)
        Managing your projects &amp; property listings
      @else
        Here's what's happening with your properties today
      @endif
    </p>
    @if($pendingListings > 0)
      <div style="margin-top:8px;font-size:12px;background:rgba(255,255,255,.15);display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;color:rgba(255,255,255,.9);">
        <i class="fa-solid fa-clock"></i> {{ $pendingListings }} listing(s) pending admin review
      </div>
    @endif
  </div>
  <div class="gb-stat" style="display:flex;gap:32px;">
    <div>
      <div class="num">{{ $activeListings }}</div>
      <div class="lbl">Active Listings</div>
    </div>
    <div>
      <div class="num">{{ number_format($totalViews) }}</div>
      <div class="lbl">Total Views</div>
    </div>
  </div>
  <a href="{{ $kycApproved ? url('/owner/post-property') : url('/owner/kyc') }}" class="gb-cta">
    <i class="fa-solid fa-plus"></i> Post New Property
  </a>
</div>

{{-- KYC Status --}}
<div style="margin:0 0 22px;background:{{ $kycApproved ? '#F0FDF4' : ($kycStatus==='submitted' ? '#EFF6FF' : ($kycStatus==='rejected' ? '#FFF1F2' : '#FFFBEB')) }};border:1px solid {{ $kycApproved ? '#BBF7D0' : ($kycStatus==='submitted' ? '#BFDBFE' : ($kycStatus==='rejected' ? '#FECDD3' : '#FDE68A')) }};border-radius:16px;padding:17px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
  <div style="width:42px;height:42px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;color:{{ $kycApproved ? '#16A34A' : '#E5343A' }};"><i class="fa-solid {{ $kycApproved ? 'fa-circle-check' : 'fa-id-card' }}"></i></div>
  <div style="flex:1;min-width:240px;">
    <div style="font-size:14px;font-weight:800;color:var(--navy);">{{ $kycApproved ? 'KYC Approved — Posting Enabled' : ($kycStatus==='submitted' ? 'KYC Under Review' : ($kycStatus==='rejected' ? 'KYC Rejected — Re-submit Required' : 'Complete KYC to Post Properties')) }}</div>
    <div style="font-size:12px;color:var(--gray);margin-top:3px;">{{ $kycApproved ? 'Your Aadhaar verification is approved. You can post properties and projects.' : ($kycStatus==='submitted' ? 'Your Aadhaar is waiting for admin approval. Posting will unlock after approval.' : 'Upload Aadhaar from KYC Verification. You can use your dashboard now, but posting stays locked until approval.') }}</div>
  </div>
  @if(!$kycApproved)<a href="{{ url('/owner/kyc') }}" class="btn btn-red" style="font-size:12px;padding:9px 15px;"><i class="fa-solid fa-arrow-right"></i> {{ $kycStatus==='submitted' ? 'View KYC' : 'Submit KYC' }}</a>@endif
</div>

{{-- Stats --}}
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
  <div class="stat-card red">
    <div class="sc-icon"><i class="fa-solid fa-building"></i></div>
    <div class="sc-num">{{ $totalListings }}</div>
    <div class="sc-label">Total Listings</div>
    <div class="sc-change {{ $activeListings > 0 ? 'up' : '' }}">
      <i class="fa-solid fa-circle-check"></i> {{ $activeListings }} active
    </div>
  </div>
  <div class="stat-card blue">
    <div class="sc-icon"><i class="fa-solid fa-eye"></i></div>
    <div class="sc-num">{{ number_format($totalViews) }}</div>
    <div class="sc-label">Total Views</div>
    <div class="sc-change up"><i class="fa-solid fa-arrow-up"></i> All time</div>
  </div>
  <div class="stat-card green">
    <div class="sc-icon"><i class="fa-solid fa-message"></i></div>
    <div class="sc-num">{{ $totalEnquiries }}</div>
    <div class="sc-label">Enquiries Received</div>
    @if($newEnquiries > 0)
    <div class="sc-change up"><i class="fa-solid fa-bell"></i> {{ $newEnquiries }} new today</div>
    @endif
  </div>
  <div class="stat-card amber">
    <div class="sc-icon"><i class="fa-solid fa-heart"></i></div>
    <div class="sc-num">{{ $savedCount }}</div>
    <div class="sc-label">Saved by Users</div>
  </div>
</div>

{{-- Builder extra row --}}
@if($isBuilder)
<div style="margin:-8px 0 24px;padding:16px 20px;background:#fff;border-radius:var(--r-lg);border:1px solid var(--border);display:flex;align-items:center;gap:20px;box-shadow:var(--shadow);">
  <div style="font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Builder Profile:</div>
  <div style="font-size:14px;font-weight:700;color:var(--navy);">{{ $cust['company_name'] ?? 'Company not set' }}</div>
  <div style="margin-left:auto;display:flex;gap:10px;">
    <a href="{{ url('/owner/subscription') }}" class="btn btn-red" style="padding:7px 14px;font-size:12px;"><i class="fa-solid fa-crown"></i> {{ $activePlan ? $activePlan->plan_name : 'Free Plan' }}</a>
    <a href="{{ url('/owner/profile') }}" class="btn btn-outline" style="padding:7px 14px;font-size:12px;"><i class="fa-solid fa-pen"></i> Edit Profile</a>
  </div>
</div>
@endif

<div class="grid-2">
  {{-- Recent listings --}}
  <div class="card">
    <div class="card-head">
      <div class="card-title"><i class="fa-solid fa-building" style="color:var(--red);margin-right:6px;"></i>Recent Listings</div>
      <a href="{{ url('/owner/my-properties') }}" class="card-action">View All →</a>
    </div>
    <div class="card-body">
      @forelse($recentListings as $prop)
      <div class="prop-list-item">
        <div class="pli-img">
          @if($prop->title_image)
            <img src="{{ asset('images/'.config('global.PROPERTY_TITLE_IMG_PATH','property_title_img/').$prop->title_image) }}" alt=""/>
          @else
            <i class="fa-solid fa-building fa-lg"></i>
          @endif
        </div>
        <div class="pli-info">
          <div class="pli-name">{{ strlen($prop->title) > 40 ? substr($prop->title, 0, 40).'...' : $prop->title }}</div>
          <div class="pli-loc"><i class="fa-solid fa-location-dot" style="color:var(--red);font-size:10px;"></i> {{ $prop->city }}</div>
        </div>
        <div class="pli-price">₹ {{ number_format($prop->price) }}</div>
        <div class="pli-status status-{{ $prop->request_status === 'approved' ? 'active' : ($prop->request_status === 'pending' ? 'pending' : 'expired') }}">
          {{ ucfirst($prop->request_status) }}
        </div>
      </div>
      @empty
      <div style="text-align:center;padding:32px;color:var(--gray2);">
        <i class="fa-solid fa-building" style="font-size:32px;margin-bottom:12px;display:block;opacity:.3;"></i>
        <div style="font-size:13px;">No properties yet</div>
        <a href="{{ $kycApproved ? url('/owner/post-property') : url('/owner/kyc') }}" class="btn btn-red" style="margin-top:12px;font-size:12px;padding:8px 16px;"><i class="fa-solid fa-plus"></i> Post Your First Property</a>
      </div>
      @endforelse
    </div>
  </div>

  {{-- Recent enquiries --}}
  <div class="card">
    <div class="card-head">
      <div class="card-title"><i class="fa-solid fa-message" style="color:var(--red);margin-right:6px;"></i>Recent Enquiries</div>
      <a href="{{ url('/owner/enquiries') }}" class="card-action">View All →</a>
    </div>
    <div class="card-body">
      @forelse($recentEnquiries as $enq)
      <div class="enq-item">
        <div class="enq-dot" style="{{ $loop->index > 0 ? 'background:var(--gray3);' : '' }}"></div>
        <div class="enq-avatar" style="background:linear-gradient(135deg,{{ ['#2563EB','#16A34A','#D97706','#7C3AED','#E5343A'][$loop->index % 5] }},rgba(255,255,255,.4))">
          {{ strtoupper(substr($enq->name, 0, 1)) }}
        </div>
        <div>
          <div class="enq-name">{{ $enq->name }}</div>
          <div class="enq-prop">{{ strlen($enq->title) > 35 ? substr($enq->title, 0, 35).'...' : $enq->title }}</div>
        </div>
        <div class="enq-time">{{ \Carbon\Carbon::parse($enq->created_at)->diffForHumans() }}</div>
      </div>
      @empty
      <div style="text-align:center;padding:32px;color:var(--gray2);">
        <i class="fa-solid fa-message" style="font-size:32px;margin-bottom:12px;display:block;opacity:.3;"></i>
        <div style="font-size:13px;">No enquiries yet</div>
      </div>
      @endforelse
    </div>
  </div>
</div>

{{-- Subscription prompt if free --}}
@if(!$activePlan)
<div style="background:linear-gradient(135deg,var(--navy),#1a0a0e);border-radius:var(--r-xl);padding:24px 28px;display:flex;align-items:center;justify-content:space-between;gap:20px;">
  <div>
    <div style="font-size:17px;font-weight:800;color:#fff;margin-bottom:5px;"><i class="fa-solid fa-crown" style="color:gold;margin-right:6px;"></i>Upgrade to Premium</div>
    <div style="font-size:13px;color:rgba(255,255,255,.6);">Get featured badge, unlimited photos &amp; 25x more visibility for your listings</div>
  </div>
  <a href="{{ url('/owner/subscription') }}" class="btn btn-red" style="white-space:nowrap;flex-shrink:0;">View Plans →</a>
</div>
@endif

@endsection
