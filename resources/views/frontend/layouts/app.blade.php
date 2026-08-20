<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>@yield('title','BigWein') | Find Your Dream Property</title>
<meta name="description" content="@yield('meta_desc','Verified properties for sale, rent or investment. Zero brokerage.')"/>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('frontend/css/bigwein.css') }}?v={{ @filemtime(public_path('frontend/css/bigwein.css')) ?: time() }}"/>
@stack('styles')
<style>
.btn-owner-reg{display:inline-flex;align-items:center;}
@media(max-width:860px){
  .btn-owner-reg{display:none!important;}
  .btn-shortlist{display:none!important;}
  .bw-nav-links{display:none!important;}
  .bw-mobile-toggle{display:flex!important;}
}
</style>
</head>
<body>

@php
try {
  $s = $s ?? \App\Http\Controllers\Frontend\FrontendController::settings(['web_logo','company_name','currency_symbol','facebook_id','instagram_id','twitter_id','youtube_id','tagline']);
} catch(\Exception $e) { $s = []; }
$cname   = $s['company_name'] ?? 'BigWein';
$webLogo = $s['web_logo'] ?? '';
$logoUrl = $webLogo ? url('images/logo/'.$webLogo) : '';
$customer = $customer ?? session('bw_customer');

// Announcement banner
try {
  $annCfg = \App\Http\Controllers\SiteSettingsController::load('announcement');
} catch(\Exception $e) { $annCfg = ['show'=>false]; }

// Language switcher
try {
  $bwLangs = \Illuminate\Support\Facades\Cache::remember('bw_langs_nav', 3600,
    fn() => \DB::table('languages')->where('status',1)->orderBy('id')->get()
  );
} catch(\Exception $e) { $bwLangs = collect(); }
$bwCur   = session('locale','en');
$bwFlags = ['en'=>'🇬🇧','ta'=>'🇮🇳','hi'=>'🇮🇳'];
$bwNames = ['en'=>'EN','ta'=>'TA','hi'=>'HI'];
@endphp

{{-- Announcement Banner --}}
@if(!empty($annCfg['show']) && !empty($annCfg['text']))
<div id="bwAnnouncement" style="background:{{ $annCfg['color'] ?? '#E5343A' }};color:{{ $annCfg['text_color'] ?? '#fff' }};text-align:center;padding:9px 40px;font-size:13px;font-weight:600;position:relative;">
  @if(!empty($annCfg['link']))<a href="{{ $annCfg['link'] }}" style="color:inherit;text-decoration:none;">{{ $annCfg['text'] }}</a>
  @else{{ $annCfg['text'] }}@endif
  @if(!empty($annCfg['dismissible']))
  <button onclick="document.getElementById('bwAnnouncement').style.display='none'" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;color:inherit;font-size:20px;cursor:pointer;line-height:1;">×</button>
  @endif
</div>
@endif

<header class="bw-header" id="bwHeader">
  <div class="container">
    <nav class="bw-nav">

      {{-- Logo --}}
      <a href="/" class="bw-logo">
        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="{{ $cname }}"
            style="height:44px;width:auto;object-fit:contain;border-radius:6px;"
            onerror="this.style.display='none';document.getElementById('bwLogoFallback').style.display='flex'"/>
          <span id="bwLogoFallback" style="display:none;align-items:center;">
            <span class="logo-text"><span class="big">Big</span><span class="wein">Wein</span></span>
          </span>
        @else
          <span style="display:flex;align-items:center;">
            <span class="logo-text"><span class="big">Big</span><span class="wein">Wein</span></span>
          </span>
        @endif
        <span class="logo-sub" style="font-size:10px;color:#888;">{{ $s['tagline'] ?? 'Addressing your dreams' }}</span>
      </a>

      {{-- Desktop nav --}}
      <div class="bw-nav-links" id="bwNavLinks">
        <a href="/properties?type=0" class="{{ request()->is('properties') && request('type')=='0' ? 'active':'' }}">{{ __('Buy') }}</a>
        <a href="/properties?type=1" class="{{ request()->is('properties') && request('type')=='1' ? 'active':'' }}">{{ __('Rent') }}</a>
        <a href="/projects"          class="{{ request()->is('projects*') ? 'active':'' }}">{{ __('Projects') }}</a>
        <a href="/properties"        class="{{ request()->is('properties') && !request('type') ? 'active':'' }}">{{ __('Properties') }}</a>
      </div>

      {{-- Actions --}}
      <div class="bw-header-actions">

        {{-- List Your Property --}}
        <a href="/owner/post-property" class="btn-owner-reg"
          style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:transparent;border:1.5px solid var(--red);color:var(--red);border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;transition:all .18s;"
          onmouseover="this.style.background='var(--red)';this.style.color='#fff';"
          onmouseout="this.style.background='transparent';this.style.color='var(--red)';">
          <i class="fa-solid fa-plus-circle fa-sm"></i> {{ __('List Your Property') }}
        </a>

        {{-- Shortlist --}}
        <button class="btn-shortlist" onclick="bwGoFavs()">
          <i class="fa-regular fa-heart"></i> Shortlist
        </button>

        {{-- Language switcher --}}
        @if($bwLangs->count() > 1)
        <div style="position:relative;" id="bwLangWrap">
          <button onclick="toggleLangMenu(event)"
            style="display:flex;align-items:center;gap:5px;padding:7px 11px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#374151;white-space:nowrap;">
            {{ $bwFlags[$bwCur] ?? '🌐' }} {{ $bwNames[$bwCur] ?? strtoupper($bwCur) }}
            <i class="fa-solid fa-chevron-down fa-xs" style="opacity:.5;"></i>
          </button>
          <div id="bwLangMenu" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1px solid var(--border);border-radius:12px;padding:5px;min-width:140px;box-shadow:0 10px 32px rgba(0,0,0,.12);z-index:9999;">
            @foreach($bwLangs as $bl)
            @php $blActive = $bwCur === $bl->code; @endphp
            <a href="/lang/{{ $bl->code }}"
              style="display:flex;align-items:center;gap:9px;padding:9px 13px;border-radius:8px;font-size:13px;font-weight:{{ $blActive?'700':'500' }};color:{{ $blActive?'var(--red)':'#374151' }};text-decoration:none;background:{{ $blActive?'#FFF1F3':'transparent' }};">
              <span style="font-size:18px;">{{ $bwFlags[$bl->code] ?? '🌐' }}</span>{{ $bl->name }}
              @if($blActive)<i class="fa-solid fa-check fa-xs" style="margin-left:auto;color:var(--red);"></i>@endif
            </a>
            @endforeach
          </div>
        </div>
        @endif

        {{-- User account --}}
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
              <a href="/user/dashboard"><i class="fa-solid fa-gauge-high"></i> My Dashboard</a>
              <a href="/user/saved"><i class="fa-solid fa-heart"></i> Saved Properties</a>
              <a href="/user/enquiries"><i class="fa-solid fa-message"></i> My Enquiries</a>
              <a href="/user/profile"><i class="fa-solid fa-user-circle"></i> My Profile</a>
              <hr/>
              <button onclick="bwLogout()"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
            </div>
          </div>
        @else
          <a href="/user/login" class="btn-signin">
            <i class="fa-regular fa-user"></i><span class="signin-text"> {{ __('Sign In') }}</span>
          </a>
        @endif

        <button class="bw-mobile-toggle" id="bwToggle"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
  </div>

  {{-- Mobile Menu --}}
  <div class="mobile-menu" id="mobileMenu">
    <a href="/properties?type=0">{{ __('Buy') }}</a>
    <a href="/properties?type=1">{{ __('Rent') }}</a>
    <a href="/projects">{{ __('Projects') }}</a>
    <a href="/properties">{{ __('All Properties') }}</a>
    <a href="/owner/post-property" style="color:var(--red);font-weight:700;">+ {{ __('List Your Property') }}</a>
    @if($customer)
      <a href="/user/dashboard" class="mobile-auth-link" style="background:#E5343A!important;">{{ __('My Account') }}</a>
      <button onclick="bwLogout()" class="mobile-auth-link" style="background:transparent;border:1px solid #ccc;color:#374151;">Logout</button>
    @else
      <a href="/user/login" class="mobile-auth-link">{{ __('Sign In') }} / {{ __('Sign Up') }}</a>
    @endif
  </div>
</header>

@yield('content')

{{-- Footer --}}
<footer class="bw-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="footer-logo"><span class="big">Big</span>Wein</span>
        <p>Zero brokerage real estate platform. Buy, sell and rent property directly with owners across India.</p>
        <div class="footer-social">
          <a class="soc" href="{{ $s['facebook_id']  ?? '#' }}" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
          <a class="soc" href="{{ $s['instagram_id'] ?? '#' }}" target="_blank"><i class="fa-brands fa-instagram"></i></a>
          <a class="soc" href="{{ $s['twitter_id']   ?? '#' }}" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
          <a class="soc" href="{{ $s['youtube_id']   ?? '#' }}" target="_blank"><i class="fa-brands fa-youtube"></i></a>
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
        <li><a href="#">Contact</a></li>
      </ul></div>
      <div class="footer-col"><h4>Support</h4><ul>
        <li><a href="#">Help Center</a></li>
        <li><a href="{{ route('customer-privacy-policy') }}" target="_blank">Privacy Policy</a></li>
        <li><a href="{{ route('customer-terms-conditions') }}" target="_blank">Terms of Service</a></li>
      </ul></div>
    </div>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} {{ $cname }}. All rights reserved. Zero Brokerage · Verified Properties</p>
    </div>
  </div>
</footer>

<div class="bw-toast" id="bwToast"></div>

<script>
window.BW = {
  base:      "{{ url('/') }}",
  api:       "{{ url('/bw-api') }}",
  csrf:      "{{ csrf_token() }}",
  currency:  "{{ $s['currency_symbol'] ?? '₹' }}",
  customer:  @json($customer),
  isLoggedIn:{{ $customer ? 'true' : 'false' }},
};
</script>

<script>
function toggleLangMenu(e) {
  e.stopPropagation();
  var m = document.getElementById('bwLangMenu');
  if (m) m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function() {
  var m = document.getElementById('bwLangMenu');
  if (m) m.style.display = 'none';
});
window.bwGoFavs = function() {
  if (BW.isLoggedIn) window.location.href = '/user/saved';
  else window.location.href = '/user/login';
};
</script>
<script src="{{ asset('frontend/js/bw-translate.js') }}"></script>
<script src="{{ asset('frontend/js/bigwein.js') }}"></script>
@stack('scripts')
@yield('js')
@yield('script')
</body>
</html>
