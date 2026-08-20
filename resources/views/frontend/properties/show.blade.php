@extends('frontend.layouts.app')
@php
  $seoTpl = $siteCfg['seo'] ?? [];
  $seoTitle = str_replace(
    ['{title}','{city}','{bhk}','{category}','{type}','{price}','{state}'],
    [$prop->title ?? '', $prop->city ?? '', $pBeds['value'] ?? '', $prop->category->category ?? '', $prop->propery_type == 1 ? 'Rent' : 'Sale', $prop->price ?? '', $prop->state ?? ''],
    $seoTpl['property_title'] ?? '{title} | BigWein'
  );
  $seoDesc = str_replace(
    ['{title}','{city}','{bhk}','{category}','{type}','{price}','{state}'],
    [$prop->title ?? '', $prop->city ?? '', $pBeds['value'] ?? '', $prop->category->category ?? '', $prop->propery_type == 1 ? 'Rent' : 'Sale', $prop->price ?? '', $prop->state ?? ''],
    $seoTpl['property_desc'] ?? ''
  );
@endphp
@section('title', ($prop->translated_title ?? $prop->title ?? 'Property'))
@section('meta_desc', \Illuminate\Support\Str::limit(strip_tags($prop->translated_description ?? $prop->description ?? ''),160))
@section('content')
<style>
/* BIGWEIN PROPERTY DETAIL V5.3 - critical page styles (embedded to avoid stale CSS cache) */
.feature-amenity-box{padding:24px!important;}
.feature-amenity-box .section-title-row{display:flex!important;align-items:flex-start!important;justify-content:space-between!important;gap:16px!important;margin-bottom:18px!important;}
.feature-amenity-box .section-title-row h2{margin:0 0 4px!important;font-size:18px!important;font-weight:700!important;color:#17182b!important;}
.feature-amenity-box .section-title-row p{margin:0!important;font-size:12px!important;color:#7c8597!important;}
.feature-amenity-box .amenities-grid-v2{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:10px!important;margin:0 0 22px!important;}
.feature-amenity-box .amenity-v2{display:flex!important;align-items:center!important;gap:10px!important;min-width:0!important;padding:11px 12px!important;background:#fff!important;border:1px solid #e5e7eb!important;border-radius:11px!important;box-sizing:border-box!important;}
.feature-amenity-box .amenity-v2-icon{width:30px!important;height:30px!important;flex:0 0 30px!important;border-radius:9px!important;background:#fff0f1!important;display:grid!important;place-items:center!important;color:#ef3f43!important;font-size:12px!important;}
.feature-amenity-box .amenity-v2-copy{min-width:0!important;display:flex!important;flex-direction:column!important;line-height:1.25!important;}
.feature-amenity-box .amenity-v2-copy span{display:block!important;font-size:10px!important;color:#8a94a6!important;text-transform:uppercase!important;letter-spacing:.35px!important;font-weight:600!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.feature-amenity-box .amenity-v2-copy strong{display:block!important;font-size:13px!important;color:#17182b!important;font-weight:700!important;margin-top:3px!important;white-space:normal!important;overflow-wrap:anywhere!important;}
.feature-amenity-box .property-facts-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:10px!important;padding-top:18px!important;border-top:1px solid #e7eaf0!important;}
.feature-amenity-box .property-fact{display:flex!important;align-items:center!important;gap:11px!important;min-width:0!important;padding:13px 14px!important;border-radius:10px!important;background:#f8fafc!important;border:1px solid #eef1f5!important;box-sizing:border-box!important;}
.feature-amenity-box .property-fact-wide{grid-column:1/-1!important;}
.feature-amenity-box .fact-icon{width:32px!important;height:32px!important;flex:0 0 32px!important;border-radius:9px!important;background:#fff!important;border:1px solid #eceff3!important;display:grid!important;place-items:center!important;color:#ef3f43!important;font-size:13px!important;}
.feature-amenity-box .property-fact>div{min-width:0!important;display:flex!important;flex-direction:column!important;}
.feature-amenity-box .property-fact small{display:block!important;font-size:10px!important;text-transform:uppercase!important;letter-spacing:.4px!important;color:#8a94a6!important;font-weight:600!important;margin:0 0 4px!important;line-height:1.2!important;}
.feature-amenity-box .property-fact strong{display:block!important;font-size:13px!important;line-height:1.45!important;color:#17182b!important;font-weight:700!important;overflow-wrap:anywhere!important;}
.detail-gallery .gallery-sub{overflow:hidden!important;background:#f4f5f7!important;}
.detail-gallery .gallery-sub img.gallery-photo{display:block!important;width:100%!important;height:100%!important;object-fit:cover!important;}
.detail-title-meta{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin:7px 0 5px;color:#7c8597;font-size:12px;font-weight:600;}
.detail-title-meta span{display:inline-flex;align-items:center;gap:5px;}
.detail-title-meta i{color:#ef3f43;font-size:11px;}
@media(max-width:1100px){.feature-amenity-box .amenities-grid-v2{grid-template-columns:repeat(2,minmax(0,1fr))!important;}}
@media(max-width:640px){.feature-amenity-box{padding:18px!important}.feature-amenity-box .amenities-grid-v2,.feature-amenity-box .property-facts-grid{grid-template-columns:1fr!important}.feature-amenity-box .property-fact-wide{grid-column:auto!important}}
</style>
@php
  use App\Http\Controllers\Frontend\FrontendController as FC;
  $currency = $s['currency_symbol'] ?? '₹';
  $pType    = $prop->propery_type; // 0=sell 1=rent (raw int)
  $price    = $prop->price;
  $title    = $prop->translated_title ?? $prop->title ?? 'Property';
  $desc     = $prop->translated_description ?? $prop->description ?? '';
  $city     = implode(', ', array_filter([$prop->city, $prop->state]));
  $mainImg  = $prop->title_image ?: '';  // accessor returns full URL
  $gallery  = $prop->gallery ?? collect();
  $galleryBase = 'images/' . trim(config('global.PROPERTY_GALLERY_IMG_PATH','property_gallery_img/'), '/');
  // Resolve gallery images against the actual filesystem. Some older records are flat,
  // while current seller uploads are saved inside /{property_id}/.
  $galleryUrl = function($img) use ($prop, $galleryBase) {
      if (is_object($img) && method_exists($img, 'getRawOriginal')) {
          $filename = $img->getRawOriginal('image') ?: ($img->image ?? '');
      } else {
          $filename = is_object($img) ? ($img->image ?? '') : ($img['image'] ?? '');
      }
      $filename = trim((string)$filename);
      if ($filename === '') return '';
      if (preg_match('~^https?://~i', $filename)) return $filename;
      $filename = basename($filename);
      $nested = $galleryBase.'/'.$prop->id.'/'.$filename;
      $flat   = $galleryBase.'/'.$filename;
      if (file_exists(public_path($nested))) return asset($nested);
      if (file_exists(public_path($flat)))   return asset($flat);
      return asset($nested);
  };
  $facils   = $prop->assign_facilities ?? [];  // array of arrays
  $owner    = $prop->customer;
  $phone    = $owner ? (($owner->country_code ?? '+91').' '.($owner->mobile ?? '')) : '';

  // parameters: array of arrays ['name'=>.., 'value'=>.., 'translated_name'=>..]
  $params = collect($prop->parameters ?? []);
  $pBeds  = $params->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bed'));
  $pBaths = $params->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bath'));
  $pArea  = $params->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'area') || str_contains(strtolower($p['name'] ?? ''),'sqft') || str_contains(strtolower($p['name'] ?? ''),'size'));
  $pFloor = $params->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'floor'));
@endphp

<div style="background:#fff;border-bottom:1px solid var(--border);padding:10px 0;">
  <div class="container">
    <div class="detail-share-row">
      <div class="breadcrumb">
        <a href="/">Home</a><i class="fa-solid fa-chevron-right fa-xs"></i>
        <a href="/properties">Properties</a><i class="fa-solid fa-chevron-right fa-xs"></i>
        <b>{{ \Illuminate\Support\Str::limit($title,40) }}</b>
      </div>
      <div class="share-btns">
        <button type="button" id="saveBtnTop" onclick="handleSave()">
          <i class="fa-{{ $isFav?'solid':'regular' }} fa-heart" id="saveIcon" style="{{ $isFav?'color:var(--red)':'' }}"></i>
          <span id="saveTxt">{{ $isFav?'Saved':'Save' }}</span>
        </button>
        <button type="button" onclick="navigator.share ? navigator.share({title:'{{ addslashes($title) }}',url:window.location.href}) : (navigator.clipboard.writeText(window.location.href).then(()=>bwToast('Link copied!','success')))">
          <i class="fa-solid fa-share-nodes"></i> Share
        </button>
      </div>
    </div>
  </div>
</div>

<div class="container" style="padding-top:24px;padding-bottom:60px;">
  <!-- Gallery -->
  <div class="detail-gallery">
    <div class="gallery-main">
      @if($mainImg)
        <img src="{{ $mainImg }}" alt="{{ $title }}" id="mainImg" style="width:100%;height:100%;object-fit:cover;"/>
      @else
        <div style="width:100%;height:100%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-image" style="font-size:60px;color:#d1d5db;"></i></div>
      @endif
    </div>
    @if($gallery->count() >= 1)
      <div class="gallery-sub">
        <img src="{{ $galleryUrl($gallery[0]) }}" alt="Gallery" class="gallery-photo"
             onclick="setMain(this.src)"/>
      </div>
    @endif
    @if($gallery->count() >= 2)
      <div class="gallery-sub" style="position:relative;">
        <img src="{{ $galleryUrl($gallery[1]) }}" alt="Gallery" class="gallery-photo"
             onclick="setMain(this.src)"/>
        @if($gallery->count() > 2)
          <div class="gallery-more" onclick="document.getElementById('gallModal').style.display='flex'">
            <i class="fa-regular fa-image" style="font-size:24px;margin-bottom:6px;"></i> +{{ $gallery->count()-2 }} Photos
          </div>
        @endif
      </div>
    @endif
  </div>

  @if($gallery->count())
    <div class="thumb-strip">
      @if($mainImg)<img src="{{ $mainImg }}" class="active" onclick="setMain('{{ $mainImg }}')" style="cursor:pointer;"/>@endif
      @foreach($gallery->take(8) as $gi)
        <img src="{{ $galleryUrl($gi) }}" onclick="setMain(this.src)" style="cursor:pointer;"/>
      @endforeach
    </div>
  @endif

  <div class="detail-layout">
    <!-- Left -->
    <div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid;background:rgba(229,52,58,.1);color:var(--red);border-color:rgba(229,52,58,.3);">
          {{ $pType==0 ? 'For Sale' : 'For Rent' }}
        </span>
        @if($prop->category)
          <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid rgba(26,26,46,.2);color:#1a1a2e;">
            {{ $prop->category->translated_name ?? $prop->category->category }}
          </span>
        @endif
        @if($prop->is_premium)
          <span style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;background:#7C3AED;color:#fff;">⭐ Premium</span>
        @endif
      </div>

      <h1 class="detail-h1">{{ $title }}</h1>
      <div class="detail-title-meta">
        <span><i class="fa-regular fa-eye"></i> {{ number_format((int)($prop->total_click ?? 0)) }} views</span>
        <span><i class="fa-regular fa-clock"></i> Listed {{ $prop->created_at?->diffForHumans() ?? 'recently' }}</span>
      </div>
      <p class="detail-loc"><i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $city ?: 'India' }}</p>

      <div class="detail-price-row">
        <div class="detail-price">
          <strong>{{ FC::fmt($price) }}{{ $pType==1&&$prop->rentduration?' / '.$prop->rentduration:'' }}</strong>
          @if($pArea && $price && is_numeric($pArea['value'] ?? null) && $pArea['value'] > 0)
            <small>₹ {{ number_format($price / $pArea['value'], 0) }} / sq.ft</small>
          @endif
        </div>
        <div style="display:flex;gap:8px;">
          @if($phone)
            <a href="tel:{{ str_replace(' ','',$phone) }}" style="padding:8px 16px;border:1px solid var(--border);border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:var(--navy);display:flex;align-items:center;gap:6px;text-decoration:none;">
              <i class="fa-solid fa-phone"></i> Call
            </a>
            @if($owner && $owner->mobile)
            <a href="https://wa.me/{{ preg_replace('/\D/', '', ($owner->country_code ?? '91').($owner->mobile ?? '')) }}?text={{ urlencode('Hi, I am interested in your property: '.$prop->title) }}"
               target="_blank" style="padding:8px 16px;border:1px solid #25D366;background:#25D366;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;color:#fff;display:flex;align-items:center;gap:6px;text-decoration:none;">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
            @endif
          @endif
          <button onclick="openEnquiry()" style="padding:8px 16px;background:var(--red);color:#fff;border:none;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;cursor:pointer;font-weight:600;">
            <i class="fa-solid fa-envelope"></i> Contact Owner
          </button>
        </div>
      </div>

      <div class="detail-params">
        @if($pBeds)<div class="detail-param"><i class="fa-solid fa-bed"></i><strong>{{ $pBeds['value'] ?? '-' }}</strong><span>{{ __('Bedrooms') }}</span></div>@endif
        @if($pBaths)<div class="detail-param"><i class="fa-solid fa-bath"></i><strong>{{ $pBaths['value'] ?? '-' }}</strong><span>{{ __('Bathrooms') }}</span></div>@endif
        @if($pArea)<div class="detail-param"><i class="fa-solid fa-ruler-combined"></i><strong>{{ $pArea['value'] ?? '-' }}</strong><span>sq.ft</span></div>@endif
        @if($pFloor)<div class="detail-param"><i class="fa-solid fa-building-columns"></i><strong>{{ $pFloor['value'] ?? '-' }}</strong><span>{{ __('Floor') }}</span></div>@endif
        @if(!$pBeds && !$pBaths && !$pArea)
          <div class="detail-param"><i class="fa-solid fa-circle-info"></i><strong>—</strong><span>On Request</span></div>
        @endif
      </div>

      @if($desc)
      <div class="detail-box">
        <h2>{{ __('About This Property') }}</h2>
        <p style="font-size:14px;color:#6B7280;line-height:1.8;">{!! nl2br(e($desc)) !!}</p>
      </div>
      @endif

      @if($params->count())
      <div class="detail-box feature-amenity-box">
        <div class="section-title-row">
          <div>
            <h2>Features &amp; Amenities</h2>
            <p>Key property specifications and listing information</p>
          </div>
        </div>

        <div class="amenities-grid-v2">
          @foreach($params as $param)
            @php
              $paramName = $param['translated_name'] ?? $param['name'] ?? '';
              $paramValue = $param['value'] ?? '';
              if (is_array($paramValue)) $paramValue = implode(', ', $paramValue);
            @endphp
            <div class="amenity-v2">
              <span class="amenity-v2-icon"><i class="fa-solid fa-check"></i></span>
              <div class="amenity-v2-copy">
                <span>{{ $paramName }}</span>
                <strong>{{ $paramValue !== '' ? $paramValue : 'Available' }}</strong>
              </div>
            </div>
          @endforeach
        </div>

        <div class="property-facts-grid">
          <div class="property-fact">
            <span class="fact-icon"><i class="fa-solid fa-house"></i></span>
            <div><small>Property Type</small><strong>{{ $prop->category?->category ?? '—' }}</strong></div>
          </div>
          <div class="property-fact">
            <span class="fact-icon"><i class="fa-solid fa-tag"></i></span>
            <div><small>Listing For</small><strong>{{ $pType==0?'Sale':'Rent' }}</strong></div>
          </div>
          <div class="property-fact">
            <span class="fact-icon"><i class="fa-solid fa-city"></i></span>
            <div><small>{{ __('City') }}</small><strong>{{ $prop->city ?? '—' }}</strong></div>
          </div>
          <div class="property-fact">
            <span class="fact-icon"><i class="fa-solid fa-map"></i></span>
            <div><small>{{ __('State') }}</small><strong>{{ $prop->state ?? '—' }}</strong></div>
          </div>
        </div>
      </div>
      @endif

      @if(!empty($facils))
      <div class="detail-box">
        <h2>Nearby Facilities</h2>
        <div class="outdoor-grid">
          @foreach($facils as $f)
            <div class="outdoor-item">
              <i class="fa-solid fa-location-dot"></i>
              <div>
                <span>{{ $f['translated_name'] ?? $f['name'] ?? '' }}</span>
                @if(!empty($f['distance']))<b>{{ $f['distance'] }}</b>@endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      <!-- Map -->
      <div class="detail-box">
        <h2>{{ __('Location') }}</h2>
        @if($prop->latitude && $prop->longitude && $prop->latitude != '87' && $prop->longitude != '97')
          <div style="height:220px;border-radius:12px;overflow:hidden;margin-top:10px;">
            <iframe src="https://maps.google.com/maps?q={{ $prop->latitude }},{{ $prop->longitude }}&z=15&output=embed"
              width="100%" height="220" style="border:0;" allowfullscreen loading="lazy"></iframe>
          </div>
        @else
          <div style="height:180px;background:#F3F4F6;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;margin-top:10px;">
            <i class="fa-solid fa-map-location-dot" style="font-size:32px;color:#D1D5DB;"></i>
            <span style="font-size:14px;color:#9CA3AF;">{{ $city ?: 'Location details available on request' }}</span>
          </div>
        @endif
      </div>
    </div>

    <!-- Right Sidebar -->
    <div>
      <div class="contact-card">
        <div class="contact-owner">
          <div class="owner-avatar">
            @if($owner && $owner->profile)
              <img src="{{ url('images/users/'.$owner->profile) }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" onerror="this.style.display='none'"/>
            @endif
            <span>{{ strtoupper(substr($owner?->name ?? 'B',0,1)) }}</span>
          </div>
          <div>
            <div class="owner-name">{{ $owner?->name ?? 'BigWein Advisor' }}</div>
            <div class="owner-role">Property Consultant · Verified</div>
          </div>
        </div>
        <div class="contact-badge">⭐ Direct Owner — ₹0 Brokerage</div>
        @if($phone)
          <a href="tel:{{ str_replace(' ','',$phone) }}" class="contact-btn btn-call">
            <i class="fa-solid fa-phone"></i> {{ $phone }}
          </a>
          @if($owner && $owner->mobile)
          <a href="https://wa.me/{{ preg_replace('/\D/', '', ($owner->country_code ?? '91').($owner->mobile ?? '')) }}?text={{ urlencode('Hi, I am interested in your property: '.$prop->title) }}"
             target="_blank" class="contact-btn" style="background:#25D366;color:#fff;border:none;">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp Owner
          </a>
          @endif
        @else
          <button class="contact-btn btn-call" onclick="openEnquiry()"><i class="fa-solid fa-phone"></i> Call Owner</button>
        @endif
        <button class="contact-btn btn-msg" onclick="openEnquiry()"><i class="fa-solid fa-envelope"></i> Send {{ __('Message') }}</button>
        <button class="contact-btn btn-msg" onclick="openEnquiry()" style="margin-bottom:0;"><i class="fa-regular fa-calendar"></i> Schedule Visit</button>
      </div>

      <!-- EMI Calc -->
      <div class="emi-card" style="margin-top:20px;">
        <h4><i class="fa-solid fa-calculator" style="color:var(--red);margin-right:8px;"></i>EMI Calculator</h4>
        <div class="emi-field"><label class="emi-label">Loan Amount (₹)</label>
          <input class="emi-input" id="eLoan" type="number" value="{{ round($price * 0.8) }}"/></div>
        <div class="emi-field"><label class="emi-label">Interest Rate (%)</label>
          <input class="emi-input" id="eRate" type="number" value="8.5" step="0.1"/></div>
        <div class="emi-field"><label class="emi-label">Tenure (Years)</label>
          <select class="emi-input" id="eTenure"><option>10</option><option>15</option><option selected>20</option><option>25</option><option>30</option></select></div>
        <div class="emi-result"><small>Estimated Monthly EMI</small><strong id="emiOut">₹—</strong></div>
        <a href="https://www.bankbazaar.com/home-loan.html" target="_blank" class="btn-loan">Get Loan Offers →</a>
      </div>
    </div>
  </div>

  <!-- Similar -->
  @if($similar->count())
  <div style="margin-top:48px;padding-top:40px;border-top:1px solid var(--border);">
    <div class="sec-row"><div><h2 class="sec-title">{{ __('Similar Properties') }}</h2><p class="sec-sub">You may also like</p></div><a href="/properties" class="btn-all">View All <i class="fa-solid fa-arrow-right"></i></a></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
      @foreach($similar as $sim)
        @php
          $sTitle = $sim->translated_title ?? $sim->title ?? 'Property';
          $sImg   = $sim->title_image ?: '';
          $sCity  = implode(', ', array_filter([$sim->city, $sim->state]));
          $sUrl   = '/property/'.($sim->slug_id ?? $sim->id);
        @endphp
        <div class="prop-card" onclick="window.location.href='{{ $sUrl }}'">
          <div class="pci">
            @if($sImg)<img src="{{ $sImg }}" alt="{{ $sTitle }}" loading="lazy" onerror="this.style.background='#f3f4f6'"/>
            @else<div style="width:100%;height:100%;background:#f3f4f6;"></div>@endif
            <span class="pbadge" style="background:{{ $sim->propery_type==0?'#E5343A':'#F59E0B' }};">{{ $sim->propery_type==0?'Sale':'Rent' }}</span>
            <button class="pwish" onclick="event.stopPropagation();bwFav(this,{{ $sim->id }})"><i class="fa-regular fa-heart"></i></button>
            <div class="pprice-over"><span class="pprice">{{ FC::fmt($sim->price) }}</span></div>
          </div>
          <div class="pcb">
            <div class="pname">{{ $sTitle }}</div>
            <div class="ploc"><i class="fa-solid fa-location-dot"></i> {{ $sCity ?: 'India' }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  @endif
</div>

<!-- Enquiry Modal -->
<div class="enquiry-modal" id="enqModal" onclick="if(event.target===this)closeEnq()">
  <div class="enquiry-box">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
      <div><h3>Contact Owner</h3><p style="font-size:13px;color:#6B7280;margin-top:4px;">Send a message or request a visit</p></div>
      <button onclick="closeEnq()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6B7280;">✕</button>
    </div>
    <div class="form-field"><label class="form-label">{{ __('Your Name') }}</label><input class="form-input" id="enqName" type="text" placeholder="Full name"/></div>
    <div class="form-field" style="margin-top:14px;"><label class="form-label">Mobile</label><input class="form-input" id="enqMob" type="tel" placeholder="+91 9876543210"/></div>
    <div class="form-field" style="margin-top:14px;"><label class="form-label">{{ __('Message') }}</label><textarea class="form-input" id="enqMsg" rows="3" style="resize:vertical;">I am interested in this property. Please contact me.</textarea></div>
    <button class="btn-auth" id="enqBtn" onclick="submitEnq()" style="margin-top:16px;"><i class="fa-solid fa-paper-plane"></i> Send {{ __('Message') }}</button>
  </div>
</div>

<!-- Gallery Modal -->
@if($gallery->count() > 2)
<div id="gallModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9100;align-items:center;justify-content:center;flex-direction:column;" onclick="if(event.target.id==='gallModal')this.style.display='none'">
  <button onclick="document.getElementById('gallModal').style.display='none'" style="position:absolute;top:20px;right:20px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer;">✕</button>
  <div style="display:flex;flex-wrap:wrap;gap:12px;padding:60px 24px;max-width:1000px;max-height:90vh;overflow-y:auto;justify-content:center;">
    @if($mainImg)<img src="{{ $mainImg }}" style="height:200px;object-fit:cover;border-radius:8px;cursor:pointer;" onclick="document.getElementById('mainImg').src=this.src;document.getElementById('gallModal').style.display='none'"/>@endif
    @foreach($gallery as $gi)
      <img src="{{ $gi->image_url }}" style="height:200px;object-fit:cover;border-radius:8px;cursor:pointer;" onclick="document.getElementById('mainImg').src=this.src;document.getElementById('gallModal').style.display='none'" onerror="this.style.display='none'"/>
    @endforeach
  </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const PROP_ID = {{ $prop->id }};
let isSaved   = {{ $isFav?'true':'false' }};

function galleryFallback(img){
  const fallback = img.dataset.fallback || '';
  if (fallback && img.src !== fallback) {
    img.dataset.fallback = '';
    img.src = fallback;
    return;
  }
  img.style.display = 'none';
}

function setMain(src){
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('.thumb-strip img').forEach(t=>t.classList.remove('active'));
}

// Save / unsave
async function handleSave(){
  if(!BW.isLoggedIn){ bwToast('Please login to save properties','error'); setTimeout(()=>window.location.href='/user/login',900); return; }
  const btn = document.getElementById('saveBtnTop');
  btn.disabled = true;
  const res = await bwPost('/bw-api/favourite', {property_id: PROP_ID});
  btn.disabled = false;
  if(!res.error){
    isSaved = res.action === 'added';
    document.getElementById('saveIcon').className = `fa-${isSaved?'solid':'regular'} fa-heart`;
    document.getElementById('saveIcon').style.color = isSaved ? 'var(--red)' : '';
    document.getElementById('saveTxt').textContent = isSaved ? 'Saved' : 'Save';
    bwToast(res.message,'success');
  } else { bwToast(res.message,'error'); }
}

// Enquiry
function openEnquiry(){
  if(!BW.isLoggedIn){ bwToast('Please login to contact the owner','error'); setTimeout(()=>window.location.href='/user/login',900); return; }
  const u = BW.customer;
  if(u){ document.getElementById('enqName').value=u.name||''; document.getElementById('enqMob').value=u.mobile||''; }
  document.getElementById('enqModal').classList.add('open');
}
function closeEnq(){ document.getElementById('enqModal').classList.remove('open'); }
async function submitEnq(){
  const name = document.getElementById('enqName').value.trim();
  const mob  = document.getElementById('enqMob').value.trim();
  if(!name||!mob){ bwToast('Please fill in your name and mobile number','error'); return; }
  const btn = document.getElementById('enqBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending…'; btn.disabled=true;
  const res = await bwPost('/bw-api/inquire', {property_id: PROP_ID});
  btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send {{ __('Message') }}'; btn.disabled=false;
  bwToast(res.message, res.error?'error':'success');
  if(!res.error) closeEnq();
}

// EMI
function calcEmi(){
  const loan   = parseFloat(document.getElementById('eLoan').value)||0;
  const rate   = (parseFloat(document.getElementById('eRate').value)||8.5)/12/100;
  const tenure = (parseInt(document.getElementById('eTenure').value)||20)*12;
  if(!loan){ document.getElementById('emiOut').textContent='₹—'; return; }
  const emi = rate?loan*rate*Math.pow(1+rate,tenure)/(Math.pow(1+rate,tenure)-1):loan/tenure;
  document.getElementById('emiOut').textContent='₹'+Math.round(emi).toLocaleString('en-IN');
}
['eLoan','eRate','eTenure'].forEach(id=>document.getElementById(id)?.addEventListener('input',calcEmi));
calcEmi();
</script>
@endpush
