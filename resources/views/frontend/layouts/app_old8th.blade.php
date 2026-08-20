<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title','BigWein') | Find Your Dream Property</title>
<meta name="description" content="@yield('meta_desc','Verified properties for sale, rent or investment. Zero brokerage.')"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/bigwein.css') }}"/>
@stack('styles')
<style>
/* ── Critical mobile overrides — always last, always wins ── */
.btn-owner-reg{display:inline-flex;align-items:center;}
@media(max-width:860px){
  .btn-owner-reg{display:none!important;}
  .btn-shortlist{display:none!important;}
  .bw-nav-links{display:none!important;}
  .bw-mobile-toggle{display:flex!important;align-items:center;justify-content:center;}
}
@media(max-width:860px){
  .btn-signin .signin-text{display:none!important;}
  .btn-signin{
    padding:8px!important;
    border-radius:50%!important;
    width:36px!important;
    height:36px!important;
    justify-content:center!important;
    font-size:0!important;
  }
  .btn-signin i{font-size:16px!important;}
}
@media(max-width:480px){
  .btn-signin{width:34px!important;height:34px!important;padding:6px!important;}
  .logo-sub{display:none!important;}
}
</style>
</head>
<body>

@php
  $s       = $s ?? \App\Http\Controllers\Frontend\FrontendController::settings(['web_logo','company_name','currency_symbol','facebook_id','instagram_id','twitter_id','youtube_id']);
  $cname   = $s['company_name'] ?? 'BigWein';
  $webLogo = $s['web_logo'] ?? '';
  $logoUrl = $webLogo ? url('images/logo/'.$webLogo) : '';
  $customer = $customer ?? session('bw_customer');
@endphp

<!-- HEADER -->
<header class="bw-header" id="bwHeader">
  <div class="container">
    <nav class="bw-nav">
      <a href="/" class="bw-logo">
        <img src="{{ url('images/Logo.jpeg') }}" alt="Bigwein"
          style="height:44px;width:auto;object-fit:contain;border-radius:6px;"
          onerror="this.style.display='none';document.getElementById('bwLogoFallback').style.display='flex'"/>
        <span id="bwLogoFallback" style="display:none;align-items:center;gap:0;">
          <span class="logo-text"><span class="big">Big</span><span class="wein">Wein</span></span>
        </span>
        <span class="logo-sub" style="color:#111;font-weight:600;">Addressing your dreams</span>
      </a>
      <div class="bw-nav-links" id="bwNavLinks">
        <a href="/properties?type=0" class="{{ request()->is('properties') && request('type')=='0' ? 'active':'' }}">Buy</a>
        <a href="/properties?type=1" class="{{ request()->is('properties') && request('type')=='1' ? 'active':'' }}">Rent</a>
        <a href="/projects" class="{{ request()->is('projects') ? 'active':'' }}">Projects</a>
        <a href="/properties">Properties</a>
        
      </div>
      <div class="bw-header-actions">
        <a href="/owner/post-property" class="btn-owner-reg"
          style="align-items:center;gap:7px;padding:9px 18px;background:transparent;border:1.5px solid var(--red);color:var(--red);border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;transition:all .18s;white-space:nowrap;"
          onmouseover="this.style.background='var(--red)';this.style.color='#fff';"
          onmouseout="this.style.background='transparent';this.style.color='var(--red)';">
          <i class="fa-solid fa-plus-circle fa-sm"></i> List Your Property
        </a>
        <button class="btn-shortlist" onclick="bwGoFavs()">
          <i class="fa-regular fa-heart"></i> Shortlist
        </button>
        @if($customer)
          <div class="bw-user-menu">
            <div class="user-trigger">
              <div class="user-avatar">
                @if(!empty($customer['profile']))
                  <img src="{{ url('images/users/'.$customer['profile']) }}" onerror="this.style.display='none'"/>
                @endif
                <span>{{ strtoupper(substr($customer['name'],0,1)) }}</span>
              </div>
              <span class="user-name">{{ \Illuminate\Support\Str::limit($customer['name'],14) }}</span>
              <i class="fa-solid fa-chevron-down fa-xs"></i>
            </div>
            <div class="user-dropdown">
              <a href="#"><i class="fa-solid fa-user-circle"></i> My Profile</a>
              <a href="#" onclick="bwGoFavs()"><i class="fa-solid fa-heart"></i> Saved Properties</a>
              <hr/>
              <button onclick="bwLogout()"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
            </div>
          </div>
        @else
          @if(session('bw_customer'))
          <a href="/user/dashboard" class="btn-signin" style="background:var(--red);color:#fff;border-radius:50% !important;"><i class="fa-solid fa-user"></i></a>
        @else
          <a href="/user/login" class="btn-signin"><i class="fa-regular fa-user"></i><span class="signin-text"> Sign In</span></a>
        @endif
        @endif
        <button class="bw-mobile-toggle" id="bwToggle"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
  </div>
  <div class="mobile-menu" id="mobileMenu">
    <a href="/properties?type=0">Buy</a>
    <a href="/properties?type=1">Rent</a>
    <a href="/projects">Projects</a>
    <a href="/properties">All Properties</a>
    
    <a href="/owner/post-property" style="color:var(--red);font-weight:700;">+ List Your Property</a>
    @if($customer)
      <button onclick="bwLogout()" class="mobile-auth-link">Logout</button>
    @else
      @if(session('bw_customer'))
        <a href="/user/dashboard" class="mobile-auth-link" style="background:#E5343A !important;">My Account</a>
      @else
        <a href="/user/login" class="mobile-auth-link">Sign In / Sign Up</a>
      @endif
    @endif
  </div>
</header>

@yield('content')

<!-- FOOTER -->
<footer class="bw-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="footer-logo"><span class="big">Big</span>Wein</span>
        <p>Zero brokerage real estate platform. Buy, sell and rent property directly with owners across India.</p>
        <div class="footer-social">
          <a class="soc" href="{{ $s['facebook_id'] ?? '#' }}" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
          <a class="soc" href="{{ $s['instagram_id'] ?? '#' }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>
          <a class="soc" href="{{ $s['twitter_id'] ?? '#' }}" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
          <a class="soc" href="{{ $s['youtube_id'] ?? '#' }}" target="_blank"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col"><h4>Properties</h4><ul>
        <li><a href="/properties">All Properties</a></li>
        <li><a href="/properties?type=0">For Sale</a></li>
        <li><a href="/properties?type=1">For Rent</a></li>
        <li><a href="/projects">Projects</a></li>
      </ul></div>
      <div class="footer-col"><h4>Company</h4><ul>
        <li><a href="#">About Us</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contact</a></li>
      </ul></div>
      <div class="footer-col"><h4>Support</h4><ul>
        <li><a href="#">Help Center</a></li>
        <li><a href="{{ route('customer-privacy-policy') }}" target="_blank">Privacy Policy</a></li>
        <li><a href="{{ route('customer-terms-conditions') }}" target="_blank">Terms of Service</a></li>
      </ul></div>
      <div class="footer-col"><h4>Cities</h4><ul>
        <li><a href="/properties?city=Chennai">Chennai</a></li>
        <li><a href="/properties?city=Bengaluru">Bengaluru</a></li>
        <li><a href="/properties?city=Hyderabad">Hyderabad</a></li>
        <li><a href="/properties?city=Mumbai">Mumbai</a></li>
        <li><a href="/properties?city=Trichy">Trichy</a></li>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} {{ $cname }}. All rights reserved. Zero Brokerage · Verified Properties</p>
      <p style="font-size:11px;opacity:.5;">Built by CodeGen Solutions</p>
    </div>
  </div>
</footer>

<!-- TOAST -->
<div class="bw-toast" id="bwToast"></div>

<script>
window.BW = {
  base:     "{{ url('/') }}",
  api:      "{{ url('/bw-api') }}",
  csrf:     "{{ csrf_token() }}",
  imgBase:  "{{ url('') }}",
  currency: "{{ $s['currency_symbol'] ?? '₹' }}",
  customer: @json($customer),
  isLoggedIn: {{ $customer ? 'true' : 'false' }},
};
</script>
<script>
// Override bwGoFavs to redirect to saved page
window.bwGoFavs = function() {
  @if(session('bw_customer'))
    window.location.href = '/user/saved';
  @else
    window.location.href = '/user/login';
  @endif
};
</script>
<script src="{{ asset('frontend/js/bigwein.js') }}"></script>
@stack('scripts')
</body>
</html>
