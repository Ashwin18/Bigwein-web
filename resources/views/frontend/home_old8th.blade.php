@extends('frontend.layouts.app')
@section('title','Find Your Dream Property')

<style>
.bw-hero{position:relative;height:500px;overflow:hidden;}
.hero-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s ease;display:flex;align-items:center;}
.hero-slide.active{opacity:1;z-index:2;}
.hero-slide img.hero-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;}
.hero-overlay-dark{position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.72) 0%,rgba(0,0,0,.5) 50%,rgba(0,0,0,.2) 100%);z-index:1;}
.hero-slide-inner{position:relative;z-index:3;width:100%;padding:0 20px;padding-top:60px;}
.hero-slide-inner .container{max-width:1180px;margin:0 auto;}
.hs-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(229,52,58,.25);border:1px solid rgba(229,52,58,.5);color:#fff;padding:6px 16px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1px;margin-bottom:18px;}
.hs-title{font-size:clamp(28px,4vw,50px);font-weight:900;color:#fff;line-height:1.15;margin-bottom:14px;}
.hs-sub{font-size:14px;color:rgba(255,255,255,.68);line-height:1.7;margin-bottom:20px;max-width:480px;}

.hero-arrow{position:absolute;top:35%;transform:translateY(-50%);z-index:10;width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.25);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .18s;}
.hero-arrow:hover{background:rgba(229,52,58,.7);border-color:transparent;}
.hero-arrow.left{left:20px;} .hero-arrow.right{right:20px;}
.hw-dots{position:absolute;bottom:140px;left:24px;transform:none;display:flex;gap:8px;z-index:10;}
.hero-dot{width:30px;height:4px;border-radius:4px;background:rgba(255,255,255,.3);border:none;cursor:pointer;transition:all .3s;padding:0;}
.hero-dot.active{background:#E5343A;width:48px;}
.hw-progress{position:absolute;bottom:130px;left:0;right:0;height:3px;background:#E5343A;z-index:10;width:0%;transition:none;}
@media(max-width:768px){.bw-hero{height:420px;}.hs-title{font-size:24px;}.hs-sub{font-size:13px;}.hero-slide-inner{padding-top:40px;}.search-container{margin-top:-100px;}}
@media(max-width:480px){.bw-hero{height:300px;}.hs-title{font-size:18px;}.hs-sub{display:none;}.hero-arrow{display:none;}.hero-slide-inner{padding-top:24px;}.search-container{margin-top:-60px;}.hw-dots{display:none;}}
</style>
<style>
.search-container{margin-top:-130px;position:relative;z-index:20;}
.nsb-card{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:visible;}
.nsb-tabs{display:flex;padding:8px 8px 0;gap:3px;border-bottom:1px solid #F1F5F9;overflow-x:auto;}
.nsb-tab{display:flex;align-items:center;gap:7px;padding:12px 20px;font-size:13px;font-weight:600;color:#94A3B8;border:none;background:none;cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-1px;white-space:nowrap;transition:all .18s;border-radius:10px 10px 0 0;font-family:'Poppins',sans-serif;}
.nsb-tab i{font-size:14px;}
.nsb-tab.active{color:#E5343A;background:#FFF1F3;border-bottom-color:#E5343A;}
.nsb-tab:hover:not(.active){color:#1E293B;background:#F8FAFC;}
.nsb-body{padding:20px 22px 22px;}
.nsb-panel{display:none;}
.nsb-panel.active{display:block;}
.nsb-pills{display:flex;gap:7px;margin-bottom:16px;flex-wrap:wrap;}
.nsb-pill{display:inline-flex;align-items:center;padding:7px 16px;border-radius:20px;border:1.5px solid #E2E8F0;font-size:12px;font-weight:600;color:#64748B;cursor:pointer;transition:all .18s;}
.nsb-pill.active{background:#E5343A;border-color:#E5343A;color:#fff;}
.nsb-pill:hover:not(.active){border-color:#E5343A;color:#E5343A;}
.nsb-fields{display:flex;border:1.5px solid #E2E8F0;border-radius:14px;overflow:visible;background:#F8FAFC;}
.nsb-field{flex:1;min-width:0;padding:13px 18px;display:flex;flex-direction:column;gap:4px;border-right:1px solid #E2E8F0;cursor:pointer;transition:background .15s;position:relative;}
.nsb-field:hover{background:#fff;}
.nsb-field.nsb-wide{flex:2;}
.nsb-sep{display:none;}
.nsb-flbl{font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:5px;}
.nsb-fval{font-size:13px;color:#1E293B;display:flex;align-items:center;justify-content:space-between;gap:6px;margin-top:2px;}
.nsb-fval input{border:none;background:none;outline:none;font-size:13px;color:#1E293B;font-family:'Poppins',sans-serif;width:100%;}
.nsb-fval input::placeholder{color:#94A3B8;}
.nsb-sel{border:none;background:none;outline:none;font-size:13px;color:#1E293B;font-family:'Poppins',sans-serif;appearance:none;cursor:pointer;width:100%;}
.nsb-ph{font-size:13px;color:#94A3B8;}
.nsb-btn{display:flex;align-items:center;gap:9px;padding:0 28px;background:#E5343A;color:#fff;border:none;cursor:pointer;font-size:14px;font-weight:700;white-space:nowrap;flex-shrink:0;transition:background .18s;font-family:'Poppins',sans-serif;border-radius:0 12px 12px 0;}
.nsb-btn i{font-size:17px;}
.nsb-btn:hover{background:#C4272D;}
.nsb-chips{display:flex;align-items:center;gap:8px;margin-top:13px;flex-wrap:wrap;}
.nsb-chip-lbl{font-size:11px;font-weight:700;color:#64748B;flex-shrink:0;}
.nsb-chip{display:inline-flex;align-items:center;padding:5px 14px;border-radius:16px;border:1.5px solid #E2E8F0;font-size:11px;font-weight:600;color:#64748B;cursor:pointer;transition:all .18s;}
.nsb-chip.active{background:#FFF1F3;border-color:#E5343A;color:#C4272D;}
.nsb-chip:hover:not(.active){border-color:#CBD5E1;color:#1E293B;}
.nsb-ms{overflow:visible!important;}
.nsb-dd{position:absolute;top:calc(100% + 6px);left:0;min-width:210px;background:#fff;border:1.5px solid #E2E8F0;border-radius:12px;z-index:9999;padding:8px 0;box-shadow:0 8px 32px rgba(0,0,0,.12);display:none;}
.nsb-dd.open{display:block;}
.nsb-dd-head{padding:8px 16px 4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94A3B8;}
.nsb-dd-item{display:flex;align-items:center;gap:10px;padding:9px 16px;font-size:13px;color:#1E293B;cursor:pointer;transition:background .12s;}
.nsb-dd-item:hover{background:#F8FAFC;}
.nsb-dd-item input[type=checkbox]{accent-color:#E5343A;width:14px;height:14px;flex-shrink:0;cursor:pointer;}
@media(max-width:860px){
  .nsb-tabs{padding:4px 4px 0;}
  .nsb-tab{padding:10px 13px;font-size:12px;}
  .nsb-body{padding:14px 14px 16px;}
  .nsb-fields{flex-direction:column;border-radius:12px;}
  .nsb-field{border-right:none;border-bottom:1px solid #E2E8F0;}
  .nsb-btn{border-radius:0 0 10px 10px;padding:14px;justify-content:center;}
}
@media(max-width:500px){
  .nsb-tab i{display:none;}
  .nsb-tab{padding:9px 10px;font-size:12px;}
  .nsb-pill{font-size:11px;padding:5px 10px;}
  .nsb-chip{font-size:11px;padding:4px 10px;}
}
</style>

@section('content')

@php
  $BASE    = 'https://bigweinadmin.codegensolutions.com';
  $IMG     = $BASE . '/images';
  $currency = $s['currency_symbol'] ?? '₹';
  use App\Http\Controllers\Frontend\FrontendController as FC;
@endphp

<!-- HERO -->
@php
  $defSlides = [
    ['grad'=>'linear-gradient(to right,#0a0a14 0%,#0f1f40 60%,#162040 100%)','badge'=>'ADDRESSING YOUR DREAMS','title'=>'Find Your Dream Home<br/>with <span style="color:#E5343A;">BigWein</span>','sub'=>'Explore verified properties for sale, rent and investment across Chennai and beyond.','btn1'=>['txt'=>'Explore Properties','url'=>'/properties'],'btn2'=>['txt'=>'List Your Property','url'=>'/owner/post-property']],
    ['grad'=>'linear-gradient(to right,#1a0505 0%,#3d1010 60%,#4a1520 100%)','badge'=>'ZERO BROKERAGE PLATFORM','title'=>'Buy &amp; Sell Direct —<br/>No Middlemen, <span style="color:#E5343A;">No Fees</span>','sub'=>'Connect directly with property owners. Save lakhs in brokerage on every deal.','btn1'=>['txt'=>'Browse for Sale','url'=>'/properties?propery_type=0'],'btn2'=>['txt'=>'Browse for Rent','url'=>'/properties?propery_type=1']],
    ['grad'=>'linear-gradient(to right,#050a14 0%,#0a1f3d 60%,#102040 100%)','badge'=>'PREMIUM LISTINGS','title'=>'Plots, Villas &amp; Commercial<br/>Spaces Across <span style="color:#E5343A;">India</span>','sub'=>'From residential plots to premium villas — find the right property at the right price.','btn1'=>['txt'=>'View Plots','url'=>'/properties?category_id=2'],'btn2'=>['txt'=>'List as Owner','url'=>'/owner/register']],
  ];
  $adminSlides = $sliders->filter(fn($s) => ($s->web_image || $s->image))->sortBy('sequence')->values();
  $useAdmin    = $adminSlides->isNotEmpty();
  $slideCount  = $useAdmin ? $adminSlides->count() : count($defSlides);
@endphp

<section class="bw-hero" id="bwHero">
<div class="hero-slides-wrap">
  @if($useAdmin)
    @foreach($adminSlides as $i => $sl)
    <div class="hero-slide {{ $i===0?'active':'' }}">
      <img class="hero-bg" src="{{ $sl->web_image ?: $sl->image }}" alt="BigWein"
        onerror="this.parentElement.style.background='#0a0a14';this.style.display='none'"/>
      <div class="hero-overlay-dark"></div>
      <div class="hero-slide-inner"><div class="container">
        <div class="hs-badge"><i class="fa-solid fa-star fa-xs"></i> ADDRESSING YOUR DREAMS</div>
        <h1 class="hs-title">Find Your Dream Property<br/>with <span style="color:#E5343A;">BigWein</span></h1>
        <p class="hs-sub">Explore verified properties for sale, rent and investment across prime locations.</p>
      </div></div>
    </div>
    @endforeach
  @else
    @foreach($defSlides as $i => $ds)
    <div class="hero-slide {{ $i===0?'active':'' }}" style="background:{{ $ds['grad'] }};">
      <div class="hero-overlay-dark" style="background:rgba(0,0,0,.25);"></div>
      <div class="hero-slide-inner"><div class="container">
        <div class="hs-badge"><i class="fa-solid fa-star fa-xs"></i> {{ $ds['badge'] }}</div>
        <h1 class="hs-title">{!! $ds['title'] !!}</h1>
        <p class="hs-sub">{{ $ds['sub'] }}</p>
      </div></div>
    </div>
    @endforeach
  @endif

</div><!-- /hero-slides-wrap -->
  @if($slideCount > 1)
  <button class="hero-arrow left"  onclick="hwPrev()" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="hero-arrow right" onclick="hwNext()" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
  <div class="hw-dots" id="hwDots">
    @for($d=0;$d<$slideCount;$d++)
      <button class="hero-dot {{ $d===0?'active':'' }}" onclick="hwGo({{ $d }})"></button>
    @endfor
  </div>
  @endif

  <div class="hw-progress" id="hwProgress"></div>

</section>



<!-- SEARCH BOX -->
<div class="container search-container" style="margin-top:-130px!important;position:relative;z-index:20;">
  <div class="nsb-card">

    {{-- TABS --}}
    <div class="nsb-tabs">
      @foreach($swCfg['tabs'] as $ti => $tab)
      @if($tab['active'] ?? true)
      <button class="nsb-tab {{ $ti===0?'active':'' }}" onclick="nsbTab('{{ $tab['slug'] }}',this)">
        <i class="fa-solid {{ $tab['icon'] ?? 'fa-house' }}"></i> {{ $tab['label'] }}
      </button>
      @endif
      @endforeach
    </div>

    <div class="nsb-body">

      {{-- BUY --}}
      <div class="nsb-panel active" id="nsb-buy">
        <div class="nsb-pills">
          @foreach($swCfg['buy_subtypes'] as $bsi => $bst)
          @if($bst['active'] ?? true)
          <span class="nsb-pill {{ $bsi===0?'active':'' }}" onclick="nsbSub(this)">{{ $bst['label'] }}</span>
          @endif
          @endforeach
        </div>
        <div class="nsb-fields">
          <div class="nsb-field nsb-wide">
            <div class="nsb-flbl"><i class="fa-solid fa-location-dot" style="color:#E5343A;"></i> City or locality</div>
            <div class="nsb-fval"><input type="text" placeholder="Mumbai, Chennai, Bangalore..." autocomplete="off"/></div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field nsb-ms" onclick="nsbToggle('nsb-dd-bhk',event)">
            <div class="nsb-flbl">BHK type <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:10px;color:#94A3B8;"></i></div>
            <div class="nsb-fval"><span class="nsb-ph" id="nsb-lbl-bhk">Any BHK</span></div>
            <div class="nsb-dd" id="nsb-dd-bhk">
              <div class="nsb-dd-head">Select BHK</div>
              @foreach($swCfg['bhk_options'] as $b)
              <label class="nsb-dd-item"><input type="checkbox" onchange="nsbUpdLbl('nsb-dd-bhk','nsb-lbl-bhk','Any BHK')"> {{ $b }}</label>
              @endforeach
            </div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Property status</div>
            <div class="nsb-fval">
              <select id="nsb-buy-status" class="nsb-sel"><option value="">Any status</option><option>Ready to Move</option><option>Under Construction</option><option>New Launch</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <button class="nsb-btn" onclick="nsbSearch('buy')"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
        <div class="nsb-chips">
          <span class="nsb-chip-lbl">Budget:</span>
          @foreach($swCfg['budget_buy'] as $bud)
          <span class="nsb-chip" data-val="{{ $bud['min'] }}-{{ $bud['max'] }}" onclick="this.classList.toggle('active')">{{ $bud['label'] }}</span>
          @endforeach
        </div>
      </div>

      {{-- RENT --}}
      <div class="nsb-panel" id="nsb-rent">
        <div class="nsb-pills">
          @foreach($swCfg['rent_subtypes'] as $rsi => $rst)
          @if($rst['active'] ?? true)
          <span class="nsb-pill {{ $rsi===0?'active':'' }}" onclick="nsbSub(this)">{{ $rst['label'] }}</span>
          @endif
          @endforeach
        </div>
        <div class="nsb-fields">
          <div class="nsb-field nsb-wide">
            <div class="nsb-flbl"><i class="fa-solid fa-location-dot" style="color:#E5343A;"></i> City or locality</div>
            <div class="nsb-fval"><input type="text" placeholder="Mumbai, Chennai, Bangalore..." autocomplete="off"/></div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field nsb-ms" onclick="nsbToggle('nsb-dd-rent-bhk',event)">
            <div class="nsb-flbl">BHK type <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:10px;color:#94A3B8;"></i></div>
            <div class="nsb-fval"><span class="nsb-ph" id="nsb-lbl-rent-bhk">Any BHK</span></div>
            <div class="nsb-dd" id="nsb-dd-rent-bhk">
              <div class="nsb-dd-head">Select BHK</div>
              @foreach($swCfg['bhk_options'] as $b)
              <label class="nsb-dd-item"><input type="checkbox" onchange="nsbUpdLbl('nsb-dd-rent-bhk','nsb-lbl-rent-bhk','Any BHK')"> {{ $b }}</label>
              @endforeach
            </div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Monthly rent</div>
            <div class="nsb-fval">
              <select id="nsb-rent-budget" class="nsb-sel"><option value="">Any budget</option><option value="0-10000">Under ₹10,000</option><option value="10000-25000">₹10k–₹25k</option><option value="25000-50000">₹25k–₹50k</option><option value="50000-">Above ₹50k</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <button class="nsb-btn" onclick="nsbSearch('rent')"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
      </div>

      {{-- COMMERCIAL --}}
      <div class="nsb-panel" id="nsb-commercial">
        <div class="nsb-pills">
          <span class="nsb-pill active" onclick="nsbSub(this)">Rent</span>
          <span class="nsb-pill" onclick="nsbSub(this)">Buy</span>
        </div>
        <div class="nsb-fields">
          <div class="nsb-field nsb-wide">
            <div class="nsb-flbl"><i class="fa-solid fa-location-dot" style="color:#E5343A;"></i> City or area</div>
            <div class="nsb-fval"><input type="text" placeholder="Search city, locality..." autocomplete="off"/></div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field nsb-ms" onclick="nsbToggle('nsb-dd-comm',event)" style="flex:1.4;">
            <div class="nsb-flbl">Property type <i class="fa-solid fa-chevron-down" style="margin-left:auto;font-size:10px;color:#94A3B8;"></i></div>
            <div class="nsb-fval"><span class="nsb-ph" id="nsb-lbl-comm">Any type</span></div>
            <div class="nsb-dd" id="nsb-dd-comm" style="min-width:230px;">
              <div class="nsb-dd-head">Commercial type</div>
              @foreach($commercialTypes as $ct)
              <label class="nsb-dd-item"><input type="checkbox" onchange="nsbUpdLbl('nsb-dd-comm','nsb-lbl-comm','Any type')"> {{ $ct }}</label>
              @endforeach
            </div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Budget</div>
            <div class="nsb-fval">
              <select id="nsb-comm-budget" class="nsb-sel"><option value="">Any budget</option><option value="0-2500000">Under ₹25L</option><option value="2500000-10000000">₹25L–₹1Cr</option><option value="10000000-50000000">₹1Cr–₹5Cr</option><option value="50000000-">Above ₹5Cr</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <button class="nsb-btn" onclick="nsbSearch('commercial')"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
      </div>

      {{-- PLOTS --}}
      <div class="nsb-panel" id="nsb-plots">
        <div style="height:6px;"></div>
        <div class="nsb-fields">
          <div class="nsb-field nsb-wide">
            <div class="nsb-flbl"><i class="fa-solid fa-location-dot" style="color:#E5343A;"></i> City or area</div>
            <div class="nsb-fval"><input type="text" id="nsb-plot-city" placeholder="Search city, area..." autocomplete="off"/></div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Budget</div>
            <div class="nsb-fval">
              <select id="nsb-plot-budget" class="nsb-sel"><option value="">Any budget</option><option value="0-2500000">Under ₹25L</option><option value="2500000-5000000">₹25L–₹50L</option><option value="5000000-10000000">₹50L–₹1Cr</option><option value="10000000-">Above ₹1Cr</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Plot area</div>
            <div class="nsb-fval">
              <select id="nsb-plot-area" class="nsb-sel"><option value="">Any size</option><option value="0-1000">Under 1000 sqft</option><option value="1000-2000">1000–2000 sqft</option><option value="2000-5000">2000–5000 sqft</option><option value="5000-">Above 5000 sqft</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <button class="nsb-btn" onclick="nsbSearch('plots')"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
      </div>

      {{-- PROJECTS --}}
      <div class="nsb-panel" id="nsb-projects">
        <div style="height:6px;"></div>
        <div class="nsb-fields">
          <div class="nsb-field nsb-wide">
            <div class="nsb-flbl"><i class="fa-solid fa-location-dot" style="color:#E5343A;"></i> City</div>
            <div class="nsb-fval"><input type="text" id="nsb-proj-city" placeholder="Search city for new projects..." autocomplete="off"/></div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Project status</div>
            <div class="nsb-fval">
              <select id="nsb-proj-status" class="nsb-sel"><option value="">Any status</option><option>Under Construction</option><option>Ready to Move</option><option>New Launch</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <div class="nsb-field">
            <div class="nsb-flbl">Configuration</div>
            <div class="nsb-fval">
              <select class="nsb-sel"><option value="">Any BHK</option><option>1 BHK</option><option>2 BHK</option><option>3 BHK</option><option>4+ BHK</option></select>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94A3B8;flex-shrink:0;"></i>
            </div>
          </div>
          <div class="nsb-sep"></div>
          <button class="nsb-btn" onclick="nsbSearch('projects')"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- BROWSE BY CATEGORY -->
<section class="section">
  <div class="container">
    <div class="sec-row">
      <div>
        <h2 class="sec-title">Browse by <span class="red">Category</span></h2>
        <p class="sec-sub">Explore properties that match your needs</p>
      </div>
      <a href="/properties" class="btn-all">View All Categories <i class="fa-solid fa-arrow-right"></i></a>
    </div>
    <div class="cat-grid">
      @php
        $iconMap=['villa'=>'fa-house-chimney','plot'=>'fa-map','townhouse'=>'fa-home',
                  'commercial'=>'fa-store','pg'=>'fa-person-shelter','house'=>'fa-home',
                  'apartment'=>'fa-building','flat'=>'fa-building','project'=>'fa-city'];
      @endphp
      @forelse($categories as $cat)
        @php
          $cName = $cat->translated_name ?? $cat->category ?? 'Category';
          $cKey  = strtolower(preg_replace('/[^a-z]/i','',$cName));
          $cIcon = $iconMap[$cKey] ?? 'fa-building';
          $cImg  = $cat->image ?? '';
          $cCnt  = $cat->property_count ?? 0;
        @endphp
        <a href="/properties?category_id={{ $cat->id }}" class="cat-card">
          <div class="cat-icon">
            @if($cImg && !str_contains($cImg,'placeholder'))
              <img src="{{ $cImg }}" alt="{{ $cName }}" style="width:28px;height:28px;object-fit:contain;"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='block'"/>
              <i class="fa-solid {{ $cIcon }}" style="display:none;color:var(--red);font-size:22px;"></i>
            @else
              <i class="fa-solid {{ $cIcon }}" style="color:var(--red);font-size:22px;"></i>
            @endif
          </div>
          <div class="cat-info">
            <b>{{ $cName }}</b>
            <span>{{ $cCnt ? $cCnt.'+ Properties' : 'Browse' }}</span>
          </div>
        </a>
      @empty
        @foreach([['fa-building','Apartments'],['fa-house-chimney','Villas'],['fa-home','Houses'],['fa-map','Plots'],['fa-store','Commercial'],['fa-city','Projects']] as $fc)
          <a href="/properties" class="cat-card">
            <div class="cat-icon"><i class="fa-solid {{ $fc[0] }}" style="color:var(--red);font-size:22px;"></i></div>
            <div class="cat-info"><b>{{ $fc[1] }}</b><span>Browse</span></div>
          </a>
        @endforeach
      @endforelse
    </div>
  </div>
</section>

<!-- FEATURED PROPERTIES -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="sec-row">
      <div><h2 class="sec-title">Featured Properties</h2><p class="sec-sub">Properties handpicked for you</p></div>
      <div class="sec-actions">
        <a href="/properties" class="btn-all">Explore All <i class="fa-solid fa-arrow-right"></i></a>
        <div class="car-btns">
          <button class="car-btn" onclick="scrollProp(-1)"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="car-btn" onclick="scrollProp(1)"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>
    <div class="prop-car-wrap">
      <div class="prop-car" id="propCar">
        @php $badges=['FEATURED','PREMIUM','','NEW LAUNCH']; @endphp
        @forelse($featured as $i => $prop)
          @php
            $pType   = $prop->propery_type; // 0=sell, 1=rent (raw int)
            $pImg    = $prop->title_image ?: '';              // accessor returns full URL
            $pTitle  = $prop->translated_title ?? $prop->title ?? 'Property';
            $pCity   = implode(', ', array_filter([$prop->city, $prop->state]));
            $pBadge  = $prop->is_premium ? 'PREMIUM' : ($badges[$i % 4]);
            $pUrl    = '/property/'.($prop->slug_id ?? $prop->id);
            $pFav    = \DB::table('favourites')->where('user_id', optional($customer)['id'] ?? 0)->where('property_id',$prop->id)->exists();
            // parameters is array of arrays — use array access
            $pParams = collect($prop->parameters ?? []);
            $pBeds   = $pParams->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bed'));
            $pBaths  = $pParams->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bath'));
            $pArea   = $pParams->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'area') || str_contains(strtolower($p['name'] ?? ''),'sqft') || str_contains(strtolower($p['name'] ?? ''),'size'));
          @endphp
          <div class="prop-card" onclick="window.location.href='{{ $pUrl }}'">
            <div class="pci">
              @if($pImg)
                <img src="{{ $pImg }}" alt="{{ $pTitle }}" loading="lazy" onerror="this.style.background='#f3f4f6'"/>
              @else
                <div style="width:100%;height:100%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-image" style="font-size:40px;color:#d1d5db;"></i></div>
              @endif
              @if($pBadge)<span class="pbadge">{{ $pBadge }}</span>@endif
              <button class="pwish {{ $pFav?'liked':'' }}" onclick="event.stopPropagation();bwFav(this,{{ $prop->id }})">
                <i class="fa-{{ $pFav?'solid':'regular' }} fa-heart"></i>
              </button>
              <div class="pprice-over">
                <span class="pprice">{{ FC::fmt($prop->price) }}{{ $pType==1 && $prop->rentduration ? ' / '.$prop->rentduration : '' }}</span>
              </div>
            </div>
            <div class="pcb">
              <div class="pname">{{ $pTitle }}</div>
              <div class="ploc"><i class="fa-solid fa-location-dot"></i> {{ $pCity ?: 'India' }}</div>
              <div class="pspecs">
                @if($pBeds)<span class="pspec"><i class="fa-solid fa-bed"></i> {{ $pBeds['value'] ?? '-' }} Bed</span>@endif
                @if($pBaths)<span class="pspec"><i class="fa-solid fa-bath"></i> {{ $pBaths['value'] ?? '-' }} Bath</span>@endif
                @if($pArea)<span class="pspec"><i class="fa-solid fa-ruler-combined"></i> {{ $pArea['value'] ?? '-' }} sq.ft</span>@endif
              </div>
            </div>
          </div>
        @empty
          <div style="grid-column:1/-1;text-align:center;padding:48px;color:#6B7280;">
            <i class="fa-solid fa-house" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>
            <p>No featured properties at the moment.</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</section>

<!-- FEATURED PROJECTS -->
<section class="section bg-light">
  <div class="container">
    <div class="sec-row">
      <div><h2 class="sec-title">Featured Projects</h2><p class="sec-sub">Top residential &amp; commercial projects</p></div>
      <div class="sec-actions">
        <a href="/projects" class="btn-all">View All <i class="fa-solid fa-arrow-right"></i></a>
        <div class="car-btns">
          <button class="car-btn" onclick="scrollProj(-1)"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="car-btn" onclick="scrollProj(1)"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
      </div>
    </div>
    <div class="proj-grid" id="projGrid">
      @php
        $pjBadges=[['NEW LAUNCH','#E5343A'],['POPULAR','#F59E0B'],['PREMIUM','#7C3AED'],['FEATURED','#059669']];
      @endphp
      @forelse($projects->take(8) as $pi => $project)
        @php
          $pjImg   = $project->image ?: ''; // accessor returns full URL
          $pjTitle = $project->translated_title ?? $project->title ?? 'Project';
          $pjLoc   = implode(', ', array_filter([$project->city, $project->state]));
          $pjBadge = $pjBadges[$pi % 4];
        @endphp
        <div class="proj-card" onclick="window.location.href='/projects'">
          <div class="proj-thumb">
            @if($pjImg)
              <img src="{{ $pjImg }}" alt="{{ $pjTitle }}" loading="lazy" onerror="this.style.background='#e5e7eb'"/>
            @else
              <div style="width:100%;height:100%;background:#e5e7eb;"></div>
            @endif
            <span class="ptbadge" style="background:{{ $pjBadge[1] }};color:#fff;">{{ $pjBadge[0] }}</span>
          </div>
          <div class="pj-info">
            <div class="pj-name">{{ $pjTitle }}</div>
            <div class="pj-loc"><i class="fa-solid fa-location-dot"></i> {{ $pjLoc ?: 'India' }}</div>
            <div class="pj-price">Price on Request</div>
          </div>
        </div>
      @empty
        <p style="color:#6B7280;font-size:14px;grid-column:1/-1;">No projects yet.</p>
      @endforelse
    </div>
  </div>
</section>

<!-- WHY BIGWEIN -->
<section class="section">
  <div class="container">
    <div class="sec-row"><div><h2 class="sec-title">Why Choose <span class="red">BigWein?</span></h2><p class="sec-sub">The smarter way to buy, sell and rent property in India</p></div></div>
    <div class="why-grid">
      @foreach([['fa-shield-check','Verified Listings','Every property manually verified before going live.'],['fa-indian-rupee-sign','Zero Brokerage','Zero commission. Connect directly with owners.'],['fa-comments','Direct Connect','Chat or call owners with full privacy.'],['fa-file-signature','Legal Assistance','Expert legal team for documentation and registration.']] as [$ico,$ttl,$dsc])
        <div class="why-card"><div class="why-icon"><i class="fa-solid {{ $ico }}"></i></div><h3>{{ $ttl }}</h3><p>{{ $dsc }}</p></div>
      @endforeach
    </div>
  </div>
</section>

<!-- FAQ -->
@if($faqs->count())
<section class="section bg-light" id="faq">
  <div class="container">
    <div class="faq-layout">
      <div class="faq-left">
        <h2 class="sec-title">Frequently Asked <span class="red">Questions</span></h2>
        <p class="sec-sub" style="margin-bottom:24px;">Answers for buyers, sellers, owners and agents.</p>
        <a href="/login" class="btn-red-solid"><i class="fa-solid fa-plus"></i> Post Property Free</a>
      </div>
      <div class="faq-right">
        @foreach($faqs as $fi => $faq)
          <div class="faq-item {{ $fi===0?'open':'' }}">
            <button class="faq-btn" onclick="bwFaq(this)" type="button">
              {{ $faq->translated_question ?? $faq->question ?? '' }}
              <i class="fa-solid fa-plus"></i>
            </button>
            <div class="faq-ans">{{ $faq->translated_answer ?? $faq->answer ?? '' }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif

<!-- APP CTA -->
<section class="app-cta-section">
  <div class="container">
    <div class="app-cta-inner">
      <div class="app-cta-text">
        <span class="app-tag"><i class="fa-solid fa-mobile-screen-button"></i> BigWein Mobile App</span>
        <h2>Search, Shortlist &amp; Enquire On the Go</h2>
        <p>Saved listings, direct enquiries and location-based search in your pocket.</p>
        <div class="store-btns">
          @if(!empty($s['playstore_id']))
            <a href="{{ $s['playstore_id'] }}" class="store-btn" target="_blank"><i class="fa-brands fa-google-play"></i> Google Play</a>
          @else
            <span class="store-btn" style="opacity:.5;cursor:default;"><i class="fa-brands fa-google-play"></i> Coming Soon</span>
          @endif
          @if(!empty($s['appstore_id']))
            <a href="{{ $s['appstore_id'] }}" class="store-btn" target="_blank"><i class="fa-brands fa-apple"></i> App Store</a>
          @else
            <span class="store-btn" style="opacity:.5;cursor:default;"><i class="fa-brands fa-apple"></i> Coming Soon</span>
          @endif
        </div>
      </div>
      <div class="app-phone">
        <i class="fa-solid fa-mobile-screen" style="font-size:80px;color:rgba(229,52,58,.4);display:block;text-align:center;padding:20px 0;"></i>
        <p>Property search in your pocket</p>
      </div>
    </div>
  </div>
</section>

<!-- FILTER MODAL -->
<div class="modal-back" id="filterModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-card">
    <div class="modal-hd">
      <h3><i class="fa-solid fa-sliders" style="color:var(--red);margin-right:8px;"></i>Filters</h3>
      <button class="modal-x" onclick="document.getElementById('filterModal').classList.remove('open')" type="button"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-bd">
      <div class="modal-grid">
        <div class="mf"><label>Property For</label><select id="mfType"><option value="">Any</option><option value="0">Buy / Sale</option><option value="1">Rent</option></select></div>
        <div class="mf"><label>City</label><select id="mfCity"><option value="">All Cities</option>@foreach($cities as $c)<option value="{{ $c->city ?? $c }}">{{ $c->city ?? $c }}</option>@endforeach</select></div>
        <div class="mf"><label>Min Price (₹)</label><input type="number" id="mfMin" placeholder="e.g. 500000"/></div>
        <div class="mf"><label>Max Price (₹)</label><input type="number" id="mfMax" placeholder="e.g. 10000000"/></div>
      </div>
      <div class="modal-acts">
        <button class="btn-mc" type="button" onclick="document.getElementById('filterModal').classList.remove('open')">Cancel</button>
        <button class="btn-ma" type="button" onclick="applyModal()">Apply</button>
      </div>
    </div>
  </div>
</div>

@endsection

<script>
// Hero slider handled below

// ── Search ───────────────────────────────────────────────────────────────────
let activeType = '0';
function nsbTab(tab, btn) {
  document.querySelectorAll('.nsb-tab').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.nsb-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('nsb-' + tab).classList.add('active');
}
function nsbSub(el) {
  el.closest('.nsb-pills').querySelectorAll('.nsb-pill').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
}
function nsbToggle(id, e) {
  e.stopPropagation();
  document.querySelectorAll('.nsb-dd').forEach(d => { if (d.id !== id) d.classList.remove('open'); });
  document.getElementById(id).classList.toggle('open');
}
function nsbUpdLbl(ddId, lblId, ph) {
  const ck = document.querySelectorAll('#' + ddId + ' input:checked');
  const lb = document.getElementById(lblId);
  lb.textContent = ck.length === 0 ? ph : ck.length <= 2 ? Array.from(ck).map(c => c.parentElement.textContent.trim()).join(', ') : ck.length + ' selected';
  lb.style.color = ck.length > 0 ? 'var(--navy)' : '';
}
function nsbSearch(tab) {
  const p = new URLSearchParams();
  if (tab === 'buy') {
    p.set('propery_type','0');
    const sub = document.querySelector('#nsb-buy .nsb-pill.active')?.textContent.trim();
    if (sub === 'Land / Plot') p.set('category_id','{{ $searchCats["plot_id"] ?? 2 }}');
    const bhks = [...document.querySelectorAll('#nsb-dd-bhk input:checked')].map(c => c.parentElement.textContent.trim());
    if (bhks.length) p.set('bhk', bhks.join(','));
    const st = document.getElementById('nsb-buy-status')?.value; if (st) p.set('prop_status', st);
    const chip = document.querySelector('#nsb-buy .nsb-chip.active');
    if (chip) { const [mn,mx] = chip.dataset.val.split('-'); if(mn) p.set('min_price',mn); if(mx) p.set('max_price',mx); }
    const city = document.querySelector('#nsb-buy input[type=text]')?.value.trim(); if (city) p.set('city', city);
  } else if (tab === 'rent') {
    p.set('propery_type','1');
    const sub = document.querySelector('#nsb-rent .nsb-pill.active')?.textContent.trim();
    if (sub === 'PG / Hostel') p.set('category_id','{{ $searchCats["pg_id"] ?? 5 }}');
    const bhks = [...document.querySelectorAll('#nsb-dd-rent-bhk input:checked')].map(c => c.parentElement.textContent.trim());
    if (bhks.length) p.set('bhk', bhks.join(','));
    const bud = document.getElementById('nsb-rent-budget')?.value;
    if (bud) { const [mn,mx]=bud.split('-'); if(mn)p.set('min_price',mn); if(mx)p.set('max_price',mx); }
    const city = document.querySelector('#nsb-rent input[type=text]')?.value.trim(); if (city) p.set('city', city);
  } else if (tab === 'commercial') {
    const sub = document.querySelector('#nsb-commercial .nsb-pill.active')?.textContent.trim();
    p.set('propery_type', sub === 'Buy' ? '0' : '1');
    p.set('category_id','{{ $searchCats["commercial_id"] ?? 4 }}');
    const types = [...document.querySelectorAll('#nsb-dd-comm input:checked')].map(c => c.parentElement.textContent.trim());
    if (types.length) p.set('comm_types', types.join(','));
    const bud = document.getElementById('nsb-comm-budget')?.value;
    if (bud) { const [mn,mx]=bud.split('-'); if(mn)p.set('min_price',mn); if(mx)p.set('max_price',mx); }
    const city = document.querySelector('#nsb-commercial input[type=text]')?.value.trim(); if (city) p.set('city', city);
  } else if (tab === 'plots') {
    p.set('category_id','{{ $searchCats["plot_id"] ?? 2 }}');
    p.set('propery_type','0');
    const city = document.getElementById('nsb-plot-city')?.value.trim(); if (city) p.set('city', city);
    const bud = document.getElementById('nsb-plot-budget')?.value;
    if (bud) { const [mn,mx]=bud.split('-'); if(mn)p.set('min_price',mn); if(mx)p.set('max_price',mx); }
  } else if (tab === 'projects') {
    const city = document.getElementById('nsb-proj-city')?.value.trim();
    const st = document.getElementById('nsb-proj-status')?.value;
    window.location.href = '/projects' + (city ? '?city=' + encodeURIComponent(city) : '') + (st ? (city?'&':'?') + 'prop_status=' + encodeURIComponent(st) : '');
    return;
  }
  window.location.href = '/properties?' + p.toString();
}
document.addEventListener('click', () => document.querySelectorAll('.nsb-dd').forEach(d => d.classList.remove('open')));
function setTab(btn) {}
function doSearch() { nsbSearch('buy'); }
function applyModal(){
  const p = new URLSearchParams();
  const t=document.getElementById('mfType').value;
  const c=document.getElementById('mfCity').value;
  const mn=document.getElementById('mfMin').value;
  const mx=document.getElementById('mfMax').value;
  if(t) p.set('type',t);
  if(c) p.set('city',c);
  if(mn) p.set('min_price',mn);
  if(mx) p.set('max_price',mx);
  document.getElementById('filterModal').classList.remove('open');
  window.location.href = '/properties?' + p.toString();
}
document.getElementById('sLoc').addEventListener('keypress',e=>{ if(e.key==='Enter') doSearch(); });

// ── Property carousel ────────────────────────────────────────────────────────
let propPg = 0;
function scrollProp(d){
  const car = document.getElementById('propCar');
  if(!car) return;
  const cw  = car.parentElement.offsetWidth/4 + 5;
  const cnt = car.querySelectorAll('.prop-card').length;
  propPg = Math.max(0, Math.min(propPg+d, cnt-4));
  car.style.transform = `translateX(-${propPg*cw}px)`;
}
document.addEventListener('DOMContentLoaded',()=>{
  const car = document.getElementById('propCar');
  if(car){ const c = car.querySelectorAll('.prop-card').length; if(c>4) car.style.gridTemplateColumns=`repeat(${c},calc(25% - 15px))`; }
});

// ── Projects page carousel ───────────────────────────────────────────────────
let projPg = 0;
function scrollProj(d){
  const cards = document.querySelectorAll('#projGrid .proj-card');
  projPg = Math.max(0, Math.min(projPg+d, Math.ceil(cards.length/4)-1));
  cards.forEach((c,i)=>{ c.style.display=(i>=projPg*4&&i<(projPg+1)*4)?'':'none'; });
}
</script>

<script>
(function(){
  var D=4000,s=document.querySelectorAll('#bwHero .hero-slide'),
      dt=document.querySelectorAll('#hwDots .hero-dot'),
      pr=document.getElementById('hwProgress'),
      cur=0,tmr=null,ptmr=null,pw=0;
  function go(n){
    s[cur].classList.remove('active'); if(dt[cur])dt[cur].classList.remove('active');
    cur=((n%s.length)+s.length)%s.length;
    s[cur].classList.add('active'); if(dt[cur])dt[cur].classList.add('active');
    rst();
  }
  function rst(){
    clearTimeout(tmr); clearInterval(ptmr); pw=0;
    if(pr){pr.style.transition='none';pr.style.width='0%';}
    if(s.length<2)return;
    setTimeout(function(){if(pr)pr.style.transition='width '+D+'ms linear';},20);
    setTimeout(function(){if(pr)pr.style.width='100%';},30);
    ptmr=setTimeout(function(){go(cur+1);},D);
  }
  window.hwGo=function(n){go(n);}; window.hwNext=function(){go(cur+1);}; window.hwPrev=function(){go(cur-1);};
  if(s.length>1)rst();
})();
</script>
