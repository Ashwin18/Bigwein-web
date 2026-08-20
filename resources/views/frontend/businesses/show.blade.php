@extends('frontend.layouts.app')

@php
  $assets = $business->assets_included ? (json_decode($business->assets_included, true) ?: []) : [];
  $isConfidential = (bool) $business->is_confidential;
  $displayTitle = $isConfidential
      ? 'Confidential '.$business->category_name.' Business for Sale'
      : $business->title;

  $location = collect([
      $business->locality ?? null,
      $business->city ?? null,
      $business->state ?? null
  ])->filter()->implode(', ');

  $revenue = (float)($business->monthly_revenue ?? 0);
  $profit = (float)($business->monthly_profit ?? 0);
  $expense = (float)($business->monthly_expense ?? 0);
@endphp

@section('title', $displayTitle)

@push('styles')
<style>
:root{
  --bw-red:#ef3f45;
  --bw-red-dark:#d83238;
  --bw-ink:#151a2d;
  --bw-muted:#6f7890;
  --bw-soft:#f6f8fc;
  --bw-line:#e6eaf1;
  --bw-card:#ffffff;
  --bw-green:#159653;
  --bw-gold:#f3a61d;
}

.bwd-page{background:#f7f9fc;min-height:100vh;padding-bottom:60px}
.bwd-wrap{max-width:1180px;margin:0 auto;padding:26px 20px 0}

.bwd-topbar{
  display:flex;align-items:center;justify-content:space-between;gap:14px;
  margin-bottom:16px;flex-wrap:wrap
}
.bwd-breadcrumb{font-size:12px;color:var(--bw-muted)}
.bwd-breadcrumb a{color:var(--bw-muted);text-decoration:none}
.bwd-actions{display:flex;gap:8px;flex-wrap:wrap}
.bwd-icon-btn{
  border:1px solid var(--bw-line);background:#fff;border-radius:10px;
  padding:9px 12px;font-size:12px;color:#30384d;text-decoration:none;
  display:inline-flex;align-items:center;gap:6px
}

.bwd-gallery{
  display:grid;grid-template-columns:minmax(0,2fr) minmax(250px,.9fr);
  gap:10px;margin-bottom:22px
}
.bwd-gallery-main{
  height:430px;border-radius:20px;overflow:hidden;background:#e9edf4;position:relative
}
.bwd-gallery-main img{width:100%;height:100%;object-fit:cover;display:block}
.bwd-gallery-overlay{
  position:absolute;inset:0;background:linear-gradient(to top,rgba(13,18,32,.52),rgba(13,18,32,.05) 55%,transparent)
}
.bwd-gallery-badge{
  position:absolute;left:18px;bottom:18px;background:rgba(255,255,255,.92);
  padding:8px 11px;border-radius:999px;font-size:11px;font-weight:800;color:var(--bw-ink)
}
.bwd-gallery-side{display:grid;grid-template-rows:1fr 1fr;gap:10px}
.bwd-thumb{
  border-radius:16px;overflow:hidden;background:#eef1f6;min-height:0;position:relative
}
.bwd-thumb img{width:100%;height:100%;object-fit:cover}
.bwd-thumb-empty{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  color:#9aa3b8;font-size:12px;background:linear-gradient(135deg,#edf1f6,#f8fafc)
}

.bwd-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:22px;align-items:start}

.bwd-main-card,.bwd-section,.bwd-side-card{
  background:var(--bw-card);border:1px solid var(--bw-line);border-radius:18px;
  box-shadow:0 8px 26px rgba(15,23,42,.045)
}
.bwd-main-card{padding:22px;margin-bottom:16px}
.bwd-section{padding:20px;margin-bottom:16px}
.bwd-side-card{padding:18px}

.bwd-category{
  display:inline-flex;align-items:center;gap:6px;background:#fff0f1;color:var(--bw-red);
  padding:6px 9px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em
}
.bwd-title{font-size:30px;line-height:1.2;margin:12px 0 7px;color:var(--bw-ink);font-weight:800}
.bwd-location{display:flex;align-items:center;gap:7px;color:var(--bw-muted);font-size:13px}
.bwd-meta-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:13px}
.bwd-meta-chip{
  background:#f8fafc;border:1px solid var(--bw-line);border-radius:999px;
  padding:7px 10px;font-size:11px;color:#465168
}
.bwd-meta-chip strong{color:var(--bw-ink)}

.bwd-kpis{
  display:grid;grid-template-columns:repeat(4,minmax(0,1fr));
  gap:10px;margin-top:18px
}
.bwd-kpi{
  border:1px solid var(--bw-line);border-radius:14px;padding:13px;background:#fbfcfe
}
.bwd-kpi-label{font-size:10px;color:#8b94a7;text-transform:uppercase;font-weight:800;letter-spacing:.03em}
.bwd-kpi-value{font-size:19px;color:var(--bw-ink);font-weight:800;margin-top:5px}
.bwd-kpi-sub{font-size:10px;color:var(--bw-muted);margin-top:3px}

.bwd-section-title{
  display:flex;align-items:center;gap:9px;font-size:16px;font-weight:800;color:var(--bw-ink);
  margin:0 0 13px
}
.bwd-section-title i{
  width:30px;height:30px;border-radius:9px;background:#fff0f1;color:var(--bw-red);
  display:flex;align-items:center;justify-content:center
}
.bwd-copy{font-size:13px;line-height:1.75;color:#4e596f;margin:0}

.bwd-highlights{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.bwd-highlight{
  border:1px solid var(--bw-line);border-radius:13px;padding:12px;background:#fbfcfe
}
.bwd-highlight-label{font-size:10px;color:#9099ab;text-transform:uppercase;font-weight:700}
.bwd-highlight-value{font-size:13px;color:var(--bw-ink);font-weight:700;margin-top:4px}

.bwd-assets{display:flex;flex-wrap:wrap;gap:8px}
.bwd-asset{
  display:inline-flex;align-items:center;gap:6px;background:#f6f8fb;border:1px solid var(--bw-line);
  color:#354057;border-radius:999px;padding:8px 10px;font-size:11px
}
.bwd-asset i{color:var(--bw-green)}

.bwd-financials{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.bwd-fin-card{
  border:1px solid var(--bw-line);border-radius:14px;padding:14px;background:#fbfcfe
}
.bwd-fin-card .lbl{font-size:10px;color:#8d96a9;font-weight:800;text-transform:uppercase}
.bwd-fin-card .val{font-size:18px;font-weight:800;color:var(--bw-ink);margin-top:5px}
.bwd-fin-card.positive .val{color:var(--bw-green)}
.bwd-fin-card.expense .val{color:#c87812}

.bwd-private{
  background:#fff8ec;border:1px solid #f8dfb3;border-radius:12px;padding:12px;
  color:#8a5a13;font-size:11px;line-height:1.6
}

.bwd-seller{
  border-top:1px solid var(--bw-line);margin-top:16px;padding-top:16px;
  display:flex;align-items:center;gap:12px
}
.bwd-avatar{
  width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#ef3f45,#9c2730);
  color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800
}
.bwd-seller small{display:block;color:#8c95a7;font-size:10px}
.bwd-seller strong{font-size:13px;color:var(--bw-ink)}
.bwd-verified{
  display:inline-flex;align-items:center;gap:5px;margin-top:3px;color:var(--bw-green);
  font-size:10px;font-weight:700
}

.bwd-sticky{position:sticky;top:90px}
.bwd-price-label{font-size:10px;color:#8f98a9;text-transform:uppercase;font-weight:800}
.bwd-price{font-size:30px;color:var(--bw-ink);font-weight:800;margin-top:5px}
.bwd-neg{font-size:10px;color:var(--bw-green);font-weight:700;margin-top:2px}
.bwd-enquiry-title{font-size:17px;font-weight:800;color:var(--bw-ink);margin:18px 0 4px}
.bwd-enquiry-sub{font-size:11px;color:var(--bw-muted);margin-bottom:13px}
.bwd-field{margin-bottom:9px}
.bwd-field label{display:block;font-size:9px;color:#7d879a;text-transform:uppercase;font-weight:800;margin-bottom:5px}
.bwd-field input,.bwd-field select,.bwd-field textarea{
  width:100%;box-sizing:border-box;border:1px solid #dce2ea;border-radius:10px;
  padding:10px 11px;font:inherit;font-size:12px;background:#fff;color:#222b3d
}
.bwd-field textarea{min-height:80px;resize:vertical}
.bwd-submit{
  width:100%;border:0;background:var(--bw-red);color:#fff;border-radius:11px;
  padding:12px;font-weight:800;font-size:12px;margin-top:2px
}
.bwd-submit:hover{background:var(--bw-red-dark)}
.bwd-trust{
  margin-top:12px;background:#f8fafc;border-radius:10px;padding:10px;
  font-size:10px;color:#64748b;line-height:1.6
}
.bwd-trust i{color:var(--bw-green);margin-right:5px}

.bwd-bottom-cta{
  margin-top:18px;background:linear-gradient(135deg,#171c31,#342131);
  border-radius:18px;padding:22px;color:#fff;display:flex;justify-content:space-between;
  align-items:center;gap:18px;flex-wrap:wrap
}
.bwd-bottom-cta h3{font-size:19px;margin:0 0 5px}
.bwd-bottom-cta p{font-size:11px;color:#cbd5e1;margin:0}
.bwd-bottom-cta a{
  background:var(--bw-red);color:#fff;text-decoration:none;border-radius:10px;
  padding:11px 15px;font-size:11px;font-weight:800
}

@media(max-width:980px){
  .bwd-layout{grid-template-columns:1fr}
  .bwd-sticky{position:static}
  .bwd-gallery{grid-template-columns:1fr}
  .bwd-gallery-main{height:390px}
  .bwd-gallery-side{grid-template-columns:1fr 1fr;grid-template-rows:none;height:180px}
}
@media(max-width:720px){
  .bwd-wrap{padding:16px 12px 0}
  .bwd-gallery-main{height:285px;border-radius:15px}
  .bwd-gallery-side{height:130px}
  .bwd-title{font-size:23px}
  .bwd-kpis{grid-template-columns:repeat(2,1fr)}
  .bwd-highlights,.bwd-financials{grid-template-columns:1fr}
  .bwd-main-card,.bwd-section,.bwd-side-card{border-radius:14px}
}
</style>
@endpush

@section('content')
<div class="bwd-page">
  <div class="bwd-wrap">

    <div class="bwd-topbar">
      <div class="bwd-breadcrumb">
        <a href="{{ url('/') }}">Home</a> &nbsp;/&nbsp;
        <a href="{{ url('/businesses') }}">Businesses for Sale</a> &nbsp;/&nbsp;
        {{ $business->category_name }}
      </div>
      <div class="bwd-actions">
        <a href="javascript:void(0)" onclick="navigator.clipboard?.writeText(window.location.href)" class="bwd-icon-btn">
          <i class="fa-solid fa-share-nodes"></i> Share
        </a>
      </div>
    </div>

    <div class="bwd-gallery">
      <div class="bwd-gallery-main">
        @if($business->cover_image)
          <img src="{{ asset('images/businesses/'.$business->id.'/'.$business->cover_image) }}" alt="{{ $displayTitle }}">
        @else
          <div class="bwd-thumb-empty">No cover image uploaded</div>
        @endif
        <div class="bwd-gallery-overlay"></div>
        <div class="bwd-gallery-badge">
          <i class="fa-solid fa-store"></i> {{ $business->category_name }}
        </div>
      </div>

      <div class="bwd-gallery-side">
        @php $sideImages = collect($images ?? [])->take(2); @endphp

        @for($i=0;$i<2;$i++)
          <div class="bwd-thumb">
            @if(isset($sideImages[$i]))
              <img src="{{ asset('images/businesses/'.$business->id.'/'.$sideImages[$i]->image) }}" alt="Business gallery">
            @else
              <div class="bwd-thumb-empty"><i class="fa-regular fa-image"></i>&nbsp; Gallery</div>
            @endif
          </div>
        @endfor
      </div>
    </div>

    <div class="bwd-layout">

      <main>
        <div class="bwd-main-card">
          <span class="bwd-category"><i class="fa-solid fa-briefcase"></i> {{ $business->category_name }}</span>

          <h1 class="bwd-title">{{ $displayTitle }}</h1>

          <div class="bwd-location">
            <i class="fa-solid fa-location-dot"></i>
            {{ $location ?: 'Location available on enquiry' }}
          </div>

          <div class="bwd-meta-row">
            <span class="bwd-meta-chip"><strong>{{ ucwords(str_replace('_',' ',$business->business_status ?? 'running')) }}</strong></span>
            @if($business->premises_type)
              <span class="bwd-meta-chip">Premises: <strong>{{ ucfirst($business->premises_type) }}</strong></span>
            @endif
            @if($business->is_confidential)
              <span class="bwd-meta-chip"><i class="fa-solid fa-user-secret"></i> <strong>Confidential Listing</strong></span>
            @endif
          </div>

          <div class="bwd-kpis">
            <div class="bwd-kpi">
              <div class="bwd-kpi-label">Asking Price</div>
              <div class="bwd-kpi-value">₹{{ number_format($business->asking_price) }}</div>
              <div class="bwd-kpi-sub">{{ $business->negotiable ? 'Negotiable' : 'Fixed / on discussion' }}</div>
            </div>
            <div class="bwd-kpi">
              <div class="bwd-kpi-label">Established</div>
              <div class="bwd-kpi-value">{{ $business->established_year ?: '—' }}</div>
              <div class="bwd-kpi-sub">Business age</div>
            </div>
            <div class="bwd-kpi">
              <div class="bwd-kpi-label">Employees</div>
              <div class="bwd-kpi-value">{{ $business->employees ?: '—' }}</div>
              <div class="bwd-kpi-sub">Current team</div>
            </div>
            <div class="bwd-kpi">
              <div class="bwd-kpi-label">Views</div>
              <div class="bwd-kpi-value">{{ number_format($business->views ?? 0) }}</div>
              <div class="bwd-kpi-sub">Listing interest</div>
            </div>
          </div>
        </div>

        <section class="bwd-section">
          <h2 class="bwd-section-title"><i class="fa-solid fa-circle-info"></i> Business Overview</h2>
          <p class="bwd-copy">{{ $business->description }}</p>

          @if($business->reason_for_sale)
            <div style="margin-top:14px;padding:11px 12px;background:#f8fafc;border-radius:10px;font-size:11px;color:#59657b">
              <strong style="color:#232b3e">Reason for Sale:</strong> {{ $business->reason_for_sale }}
            </div>
          @endif
        </section>

        @if(!empty($business->category_details))
          @php $details = json_decode($business->category_details, true) ?: []; @endphp
          @if(count($details))
          <section class="bwd-section">
            <h2 class="bwd-section-title"><i class="fa-solid fa-star"></i> Business Highlights</h2>
            <div class="bwd-highlights">
              @foreach($details as $key => $value)
                @if($value !== null && $value !== '')
                <div class="bwd-highlight">
                  <div class="bwd-highlight-label">{{ ucwords(str_replace('_',' ',$key)) }}</div>
                  <div class="bwd-highlight-value">{{ $value }}</div>
                </div>
                @endif
              @endforeach
            </div>
          </section>
          @endif
        @endif

        <section class="bwd-section">
          <h2 class="bwd-section-title"><i class="fa-solid fa-chart-line"></i> Financial Snapshot</h2>

          @if($business->financial_visibility === 'public')
            <div class="bwd-financials">
              <div class="bwd-fin-card">
                <div class="lbl">Monthly Revenue</div>
                <div class="val">₹{{ number_format($revenue) }}</div>
              </div>
              <div class="bwd-fin-card expense">
                <div class="lbl">Monthly Expenses</div>
                <div class="val">₹{{ number_format($expense) }}</div>
              </div>
              <div class="bwd-fin-card positive">
                <div class="lbl">Monthly Profit</div>
                <div class="val">₹{{ number_format($profit) }}</div>
              </div>
            </div>

            @if($business->inventory_value)
              <div style="margin-top:10px;font-size:11px;color:#64748b">
                Approx. Inventory Value: <strong style="color:#253047">₹{{ number_format($business->inventory_value) }}</strong>
              </div>
            @endif
          @else
            <div class="bwd-private">
              <i class="fa-solid fa-lock"></i>
              @if($business->financial_visibility === 'hidden')
                Detailed financial information is private and can be discussed directly with the seller.
              @else
                Detailed financial information is available to verified / serious buyers through the seller.
              @endif
            </div>
          @endif
        </section>

        @if(count($assets))
        <section class="bwd-section">
          <h2 class="bwd-section-title"><i class="fa-solid fa-box-open"></i> Included in Sale</h2>
          <div class="bwd-assets">
            @foreach($assets as $asset)
              <span class="bwd-asset"><i class="fa-solid fa-circle-check"></i> {{ $asset }}</span>
            @endforeach
          </div>
        </section>
        @endif

        <div class="bwd-bottom-cta">
          <div>
            <h3>Interested in this business?</h3>
            <p>Send an enquiry and connect with the seller for detailed discussion and due diligence.</p>
          </div>
          <a href="#business-enquiry"><i class="fa-solid fa-paper-plane"></i> Send Enquiry</a>
        </div>
      </main>

      <aside class="bwd-sticky">
        <div class="bwd-side-card" id="business-enquiry">
          <div class="bwd-price-label">Asking Price</div>
          <div class="bwd-price">₹{{ number_format($business->asking_price) }}</div>
          @if($business->negotiable)<div class="bwd-neg"><i class="fa-solid fa-circle-check"></i> Negotiable</div>@endif

          <div class="bwd-enquiry-title">Interested in this business?</div>
          <div class="bwd-enquiry-sub">Share your details. The seller can contact you regarding the opportunity.</div>

          @if(session('success'))
            <div style="background:#ecfdf5;color:#047857;border-radius:10px;padding:9px 10px;font-size:11px;margin-bottom:10px">
              {{ session('success') }}
            </div>
          @endif

          <form method="POST" action="{{ url('/business/enquiry') }}">
            @csrf
            <input type="hidden" name="business_id" value="{{ $business->id }}">

            <div class="bwd-field">
              <label>Your Name *</label>
              <input name="name" value="{{ old('name') }}" placeholder="Enter your name" required>
            </div>

            <div class="bwd-field">
              <label>Mobile *</label>
              <input name="mobile" value="{{ old('mobile') }}" placeholder="Mobile number" required>
            </div>

            <div class="bwd-field">
              <label>Email</label>
              <input name="email" type="email" value="{{ old('email') }}" placeholder="Email address">
            </div>

            <div class="bwd-field">
              <label>Buyer Type</label>
              <select name="buyer_type">
                <option value="">Select buyer type</option>
                <option>Individual Investor</option>
                <option>Existing Business Owner</option>
                <option>Company</option>
                <option>Franchise Investor</option>
              </select>
            </div>

            <div class="bwd-field">
              <label>Investment Budget</label>
              <input name="investment_budget" value="{{ old('investment_budget') }}" placeholder="e.g. ₹25L – ₹50L">
            </div>

            <div class="bwd-field">
              <label>Message</label>
              <textarea name="message" placeholder="I would like to know more about this business...">{{ old('message') }}</textarea>
            </div>

            <button class="bwd-submit" type="submit">
              <i class="fa-solid fa-paper-plane"></i> Send Enquiry
            </button>
          </form>

          <div class="bwd-trust">
            <div><i class="fa-solid fa-shield-halved"></i> Your contact details are shared only for this business enquiry.</div>
            <div style="margin-top:5px"><i class="fa-solid fa-user-check"></i> Business listing reviewed through BigWein admin workflow.</div>
          </div>

          <div class="bwd-seller">
            <div class="bwd-avatar">{{ strtoupper(substr($business->category_name ?: 'B',0,1)) }}</div>
            <div>
              <small>Business Seller</small>
              <strong>{{ $isConfidential ? 'Confidential Seller' : 'Verified Listing Owner' }}</strong>
              <div class="bwd-verified"><i class="fa-solid fa-circle-check"></i> KYC-based seller listing</div>
            </div>
          </div>
        </div>
      </aside>

    </div>
  </div>
</div>
@endsection
