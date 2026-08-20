<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title','Dashboard') — BigWein Owner Portal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/owner.css') }}"/>
@stack('styles')
</head>
<body>
<div class="app" id="app">

  {{-- SIDEBAR --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <a href="{{ url('/') }}">
        <div class="logo-img">B</div>
        <div class="logo-text">
          <span class="big">Big</span><span class="wein">Wein</span>
          <span class="logo-sub">Owner Portal</span>
        </div>
      </a>
    </div>

    @php
      $cust = session('bw_customer');
      $ownerIdForKyc = $cust['id'] ?? $cust['customer_id'] ?? null;
      if(!$ownerIdForKyc && !empty($cust['email'])) $ownerIdForKyc = \Illuminate\Support\Facades\DB::table('customers')->where('email',$cust['email'])->value('id');
      $kycStatus = $ownerIdForKyc ? strtolower((string)(\Illuminate\Support\Facades\DB::table('customers')->where('id',$ownerIdForKyc)->value('kyc_status') ?? 'pending')) : 'pending';
      $kycApproved = $kycStatus === 'approved';
      $ownerType = strtolower((string)($cust['owner_type'] ?? 'seller'));
      $isBuilder = $ownerType === 'builder';
      $builderCompanyStatus = 'draft';
      if($isBuilder && $ownerIdForKyc){
        try { $builderCompanyStatus = strtolower((string)(\Illuminate\Support\Facades\DB::table('builder_profiles')->where('customer_id',$ownerIdForKyc)->value('status') ?? 'draft')); } catch(\Throwable $e) { $builderCompanyStatus='draft'; }
      }
      $builderApproved = $builderCompanyStatus === 'approved';
    @endphp
    <div class="sidebar-user">
      <div class="su-inner">
        @if(!empty($cust['profile']))
          <img src="{{ asset('images/'.config('global.USER_IMG_PATH','user_img/').$cust['profile']) }}" class="su-avatar" style="object-fit:cover;" alt=""/>
        @else
          <div class="su-avatar">{{ strtoupper(substr($cust['name']??'U',0,1)) }}</div>
        @endif
        <div style="min-width:0;">
          <span class="su-name">{{ $cust['name'] ?? 'Owner' }}</span>
          <div class="su-type">
            <div class="dot"></div>
            <span>{{ $cust['owner_type'] === 'builder' ? 'Builder / Developer' : 'Seller / Owner' }}</span>
          </div>
          <div class="su-plan">{{ $activePlanName ?? 'Free Plan' }}</div>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-label">Main</div>
        <a href="{{ url('/owner/dashboard') }}" class="nav-item {{ request()->is('owner/dashboard') ? 'active' : '' }}">
          <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
        <a href="{{ url('/owner/my-properties') }}" class="nav-item {{ request()->is('owner/my-properties') ? 'active' : '' }}">
          <i class="fa-solid fa-building"></i> My Properties
          @if(($propCount??0) > 0)<div class="nav-badge">{{ $propCount }}</div>@endif
        </a>
        <a href="{{ $kycApproved ? url('/owner/post-property') : url('/owner/kyc') }}" class="nav-item {{ request()->is('owner/post-property') ? 'active' : '' }}">
          <i class="fa-solid fa-plus-circle"></i> Post Property
        </a>
      </div>
      @if($isBuilder)
      <div class="nav-section">
        <div class="nav-section-label">Builder / Developer</div>
        <a href="{{ url('/owner/my-projects') }}" class="nav-item {{ request()->is('owner/my-projects') ? 'active' : '' }}">
          <i class="fa-solid fa-city"></i> My Projects
        </a>
        <a href="{{ url('/owner/project-enquiries') }}" class="nav-item {{ request()->is('owner/project-enquiries*') ? 'active' : '' }}">
          <i class="fa-solid fa-message"></i> Project Enquiries
        </a>
        <a href="{{ ($kycApproved && $builderApproved) ? url('/owner/post-project') : ($kycApproved ? url('/owner/builder-verification') : url('/owner/kyc')) }}" class="nav-item {{ request()->is('owner/post-project') ? 'active' : '' }}">
          <i class="fa-solid fa-square-plus"></i> Add Project
        </a>
        <a href="{{ url('/owner/builder-verification') }}" class="nav-item {{ request()->is('owner/builder-verification') ? 'active' : '' }}">
          <i class="fa-solid fa-building-shield"></i> Company Verification
          @if(!$builderApproved)<div class="nav-badge" style="background:#F59E0B;">!</div>@endif
        </a>
      </div>
      @endif

      <div class="nav-section">
        <div class="nav-section-label">Business Marketplace</div>
        <a href="{{ url('/owner/my-businesses') }}" class="nav-item {{ request()->is('owner/my-businesses') ? 'active' : '' }}"><i class="fa-solid fa-briefcase"></i> My Businesses</a>
        <a href="{{ $kycApproved ? url('/owner/list-business') : url('/owner/kyc') }}" class="nav-item {{ request()->is('owner/list-business') ? 'active' : '' }}"><i class="fa-solid fa-store"></i> List Business for Sale</a>
        <a href="{{ url('/owner/business-enquiries') }}" class="nav-item {{ request()->is('owner/business-enquiries') ? 'active' : '' }}"><i class="fa-solid fa-user-tie"></i> Business Enquiries</a>
      </div>
      <div class="nav-section">
        <div class="nav-section-label">Activity</div>
        <a href="{{ url('/owner/enquiries') }}" class="nav-item {{ request()->is('owner/enquiries') ? 'active' : '' }}">
          <i class="fa-solid fa-message"></i> Enquiries
          @if(($enquiryCount??0) > 0)<div class="nav-badge">{{ $enquiryCount }}</div>@endif
        </a>
      </div>
      <div class="nav-section">
        <div class="nav-section-label">Account</div>
        <a href="{{ url('/owner/kyc') }}" class="nav-item {{ request()->is('owner/kyc') ? 'active' : '' }}">
          <i class="fa-solid fa-id-card"></i> KYC Verification
          @if(!$kycApproved)<div class="nav-badge" style="background:#F59E0B;">!</div>@endif
        </a>
        <a href="{{ url('/owner/subscription') }}" class="nav-item {{ request()->is('owner/subscription') ? 'active' : '' }}">
          <i class="fa-solid fa-crown"></i> Subscription
        </a>
        <a href="{{ url('/owner/profile') }}" class="nav-item {{ request()->is('owner/profile') ? 'active' : '' }}">
          <i class="fa-solid fa-user-circle"></i> My Profile
        </a>
        <a href="{{ url('/') }}" class="nav-item" target="_blank">
          <i class="fa-solid fa-globe"></i> View Website
        </a>
      </div>
    </nav>

    <div class="sidebar-bottom">
      @php $plan = $activePlanName ?? 'Free'; @endphp
      @if($plan === 'Free' || $plan === 'Free Plan')
      <div class="plan-card-mini">
        <div class="pcm-title"><i class="fa-solid fa-crown"></i> Free Plan Active</div>
        <div class="pcm-sub">Upgrade for more listings &amp; premium visibility</div>
        <a href="{{ url('/owner/subscription') }}" class="pcm-btn">Upgrade Now →</a>
      </div>
      @else
      <div class="plan-card-mini" style="background:rgba(22,163,74,.15);border-color:rgba(22,163,74,.25);">
        <div class="pcm-title" style="color:#4ADE80;"><i class="fa-solid fa-crown"></i> {{ $plan }}</div>
        <div class="pcm-sub">Your plan is active and running</div>
        <a href="{{ url('/owner/subscription') }}" class="pcm-btn" style="background:var(--green);">Manage Plan</a>
      </div>
      @endif
    </div>
  </aside>

  {{-- MAIN --}}
  <div class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="tb-btn" id="sidebarToggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div>
          <div class="page-title">@yield('page-title','Dashboard')</div>
          <div class="page-bread">@yield('page-bread','Owner Portal')</div>
        </div>
      </div>
      <div class="tb-right">
        <button class="tb-btn" title="Notifications"><i class="fa-regular fa-bell"></i></button>
        <a href="{{ $kycApproved ? url('/owner/post-property') : url('/owner/kyc') }}" class="post-btn"><i class="fa-solid fa-house"></i> Post Property</a>
@if($isBuilder)
<a href="{{ ($kycApproved && $builderApproved) ? url('/owner/post-project') : ($kycApproved ? url('/owner/builder-verification') : url('/owner/kyc')) }}" class="post-btn" style="background:#243248;"><i class="fa-solid fa-city"></i> Add Project</a>
@endif
<a href="{{ $kycApproved ? url('/owner/list-business') : url('/owner/kyc') }}" class="post-btn" style="background:#172033;"><i class="fa-solid fa-briefcase"></i> List Business</a>
        <form method="POST" action="{{ url('/owner/logout') }}" style="display:inline;">
          @csrf
          <button type="submit" class="tb-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
      </div>
    </header>

    <div class="content">
      {{-- Flash messages --}}
      @if(session('success'))
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-error">
          <i class="fa-solid fa-triangle-exclamation"></i>
          @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
        </div>
      @endif

      @yield('content')
    </div>
  </div>
</div>

{{-- Overlay for mobile sidebar --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('sidebar').classList.remove('open');this.classList.remove('show')"></div>

<div class="toast" id="toast"></div>
<script src="{{ asset('frontend/js/owner.js') }}"></script>
@stack('scripts')
</body>
</html>
