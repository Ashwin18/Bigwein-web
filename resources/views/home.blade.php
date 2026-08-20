@extends('layouts.main')
@section('title') Dashboard @endsection

@section('css')
<style>
.bw-dashboard-v2{padding:16px 20px 40px!important;display:flex!important;flex-direction:column!important;gap:18px!important}
.bw-command-hero{position:relative;overflow:hidden;display:grid!important;grid-template-columns:minmax(0,1.5fr) minmax(290px,.75fr)!important;gap:22px!important;padding:26px!important;border-radius:20px!important;background:linear-gradient(135deg,#111827 0%,#172033 58%,#252f46 100%)!important;border:1px solid rgba(255,255,255,.08)!important;box-shadow:0 18px 42px rgba(15,23,42,.16)!important}
.bw-command-hero:before{content:"";position:absolute;inset:auto -60px -110px auto;width:250px;height:250px;border-radius:50%;background:rgba(239,63,69,.16)}
.bw-command-copy,.bw-command-score{position:relative;z-index:1}.bw-command-eyebrow{display:inline-flex!important;align-items:center;gap:7px;color:#fecdd3!important;font-size:11px!important;font-weight:800!important;letter-spacing:.08em;text-transform:uppercase}.bw-command-copy h2{margin:11px 0 7px!important;color:#fff!important;font-size:29px!important;font-weight:800!important}.bw-command-copy p{margin:0!important;color:#cbd5e1!important;font-size:13px!important;max-width:640px}.bw-command-actions{display:flex!important;flex-wrap:wrap;gap:9px!important;margin-top:20px!important}.bw-command-actions a{display:inline-flex!important;align-items:center;gap:7px;padding:10px 14px!important;border-radius:10px!important;text-decoration:none!important;font-size:12px!important;font-weight:700!important}.bw-command-primary{background:#ef3f45!important;color:#fff!important}.bw-command-secondary{background:rgba(255,255,255,.09)!important;border:1px solid rgba(255,255,255,.14)!important;color:#fff!important}
.bw-command-score{background:rgba(255,255,255,.08)!important;border:1px solid rgba(255,255,255,.11)!important;border-radius:17px!important;padding:16px!important;display:grid!important;grid-template-columns:116px 1fr!important;gap:15px!important;align-items:center!important}.bw-score-ring{width:108px;height:108px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;border:9px solid rgba(255,255,255,.13);box-shadow:inset 0 0 0 2px #ef3f45}.bw-score-ring strong{font-size:24px;color:#fff}.bw-score-ring span{max-width:78px;text-align:center;color:#cbd5e1;font-size:9px;line-height:1.2}.bw-score-list{display:flex;flex-direction:column;gap:9px}.bw-score-list div{display:flex;justify-content:space-between;gap:12px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,.1);font-size:11px;color:#cbd5e1}.bw-score-list b{color:#fff;font-size:13px}
.bw-action-strip{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important}.bw-action-strip>a{display:grid!important;grid-template-columns:36px minmax(0,1fr) 18px!important;align-items:center!important;gap:10px!important;padding:14px!important;background:#fff!important;border:1px solid #e8edf5!important;border-radius:14px!important;text-decoration:none!important;color:#0f172a!important;box-shadow:0 7px 18px rgba(15,23,42,.04)!important}.bw-action-strip>a>i:first-child{width:36px;height:36px;border-radius:10px;background:#fff1f2;color:#ef3f45;display:flex;align-items:center;justify-content:center}.bw-action-strip span{display:flex;flex-direction:column;min-width:0}.bw-action-strip strong{font-size:12px;color:#0f172a}.bw-action-strip small{font-size:10px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.bw-action-strip>a>i:last-child{color:#94a3b8}

.bw-mode-banner{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 16px;border-radius:13px;background:#fff7ed;border:1px solid #fed7aa;box-shadow:0 6px 18px rgba(154,52,18,.05)}
.bw-mode-banner-left{display:flex;align-items:flex-start;gap:11px}.bw-mode-icon{width:36px;height:36px;border-radius:10px;background:#ffedd5;color:#c2410c;display:flex;align-items:center;justify-content:center;font-size:17px;flex:0 0 auto}.bw-mode-banner strong{display:block;color:#9a3412;font-size:12px}.bw-mode-banner p{margin:2px 0 0;color:#9a3412;font-size:11px}.bw-mode-chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}.bw-mode-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:20px;background:#fff;color:#9a3412;border:1px solid #fed7aa;font-size:10px;font-weight:700}.bw-mode-actions{display:flex;gap:7px;flex-wrap:wrap}.bw-mode-actions a{padding:7px 10px;border-radius:8px;text-decoration:none;font-size:11px;font-weight:700;white-space:nowrap}.bw-live-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:20px;background:#ecfdf5;color:#047857;font-size:10px;font-weight:800;letter-spacing:.02em}.bw-live-dot{width:6px;height:6px;border-radius:50%;background:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.13)}
@media(max-width:1100px){.bw-command-hero{grid-template-columns:1fr!important}.bw-action-strip{grid-template-columns:repeat(2,minmax(0,1fr))!important}.bw-kpi-grid{grid-template-columns:repeat(2,1fr)!important}}
@media(max-width:800px){.bw-mode-banner{align-items:flex-start;flex-direction:column}.bw-mode-actions{width:100%}.bw-mode-actions a{flex:1;text-align:center}}
@media(max-width:700px){.bw-dashboard-v2{padding:12px!important}.bw-command-hero{padding:19px!important;border-radius:15px!important}.bw-command-copy h2{font-size:22px!important}.bw-command-score{grid-template-columns:92px 1fr!important}.bw-score-ring{width:86px;height:86px}.bw-action-strip,.bw-kpi-grid,.bw-insight-grid{grid-template-columns:1fr!important}}
</style>
@endsection

@section('page-title')
<div class="page-title">
  <div class="row align-items-center">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4 style="display:flex;align-items:center;gap:8px;margin:0;">
        <i class="bi bi-grid-1x2" style="color:#E5343A;"></i> Dashboard
      </h4>
    </div>
    <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-md-end align-items-center gap-2">
      <select id="filterCity" class="form-select form-select-sm" style="width:auto;" onchange="applyFilter()">
        <option value="">All Cities</option>
        @foreach($cities as $city)
          <option value="{{ $city }}" {{ request('fc')===$city?'selected':'' }}>{{ $city }}</option>
        @endforeach
      </select>
      <select id="filterPeriod" class="form-select form-select-sm" style="width:auto;" onchange="applyFilter()">
        <option value="month" {{ request('fp')==='month'||!request('fp')?'selected':'' }}>This Month</option>
        <option value="quarter" {{ request('fp')==='quarter'?'selected':'' }}>This Quarter</option>
        <option value="year" {{ request('fp')==='year'?'selected':'' }}>This Year</option>
        <option value="" {{ request('fp')===''?'selected':'' }}>All Time</option>
      </select>
    </div>
  </div>
</div>
@endsection

@section('content')
@php
  try {
    $bizStats = [
      'total' => \DB::table('businesses')->count(),
      'live' => \DB::table('businesses')->where('request_status','approved')->where('status',1)->count(),
      'pending' => \DB::table('businesses')->where('request_status','pending')->count(),
      'enquiries' => \DB::table('business_enquiries')->count(),
      'new_enquiries' => \DB::table('business_enquiries')->where('status','new')->count(),
    ];
  } catch(\Throwable $e) {
    $bizStats = ['total'=>0,'live'=>0,'pending'=>0,'enquiries'=>0,'new_enquiries'=>0];
  }
@endphp
<div class="bw-dashboard-v2" style="padding:16px 20px 40px;display:flex;flex-direction:column;gap:18px;">

  <section class="bw-mode-banner" style="{{ $demoModeEnabled ? '' : 'background:#ecfdf5;border-color:#bbf7d0;color:#166534;' }}">
    <div class="bw-mode-banner-left">
      <div class="bw-mode-icon" style="{{ $demoModeEnabled ? '' : 'background:#dcfce7;color:#15803d;' }}"><i class="bi {{ $demoModeEnabled ? 'bi-database-fill-gear' : 'bi-broadcast-pin' }}"></i></div>
      <div>
        <strong>{{ $demoModeEnabled ? 'DEMO MODE — SEEDED DATA ONLY' : 'LIVE MODE — PRODUCTION DATA ONLY' }}</strong>
        <p>
          @if($demoModeEnabled)
            Dashboard, frontend listings, searches, approvals, notifications and analytics are restricted to <b>demo_seed</b> records.
          @else
            Dashboard, frontend listings, searches, approvals, notifications and analytics exclude <b>demo_seed</b> records.
          @endif
        </p>
        <div class="bw-mode-chips">
          <span class="bw-mode-chip"><i class="bi bi-houses"></i> {{ $modePropertyCount }} {{ $demoModeEnabled ? 'demo' : 'live' }} properties</span>
          <span class="bw-mode-chip"><i class="bi bi-buildings"></i> {{ $modeProjectCount }} {{ $demoModeEnabled ? 'demo' : 'live' }} projects</span>
          @if($modeBusinessCount > 0)<span class="bw-mode-chip"><i class="bi bi-shop"></i> {{ $modeBusinessCount }} {{ $demoModeEnabled ? 'demo' : 'live' }} businesses</span>@endif
        </div>
      </div>
    </div>
    <div class="bw-mode-actions">
      <a href="{{ url('/') }}" target="_blank" style="background:#fff;color:{{ $demoModeEnabled ? '#9a3412' : '#166534' }};border:1px solid {{ $demoModeEnabled ? '#fed7aa' : '#bbf7d0' }};"><i class="bi bi-box-arrow-up-right"></i> Preview Website</a>
      <a href="{{ url('demo-settings') }}" style="background:{{ $demoModeEnabled ? '#c2410c' : '#15803d' }};color:#fff;"><i class="bi bi-sliders"></i> Change Mode</a>
    </div>
  </section>

  {{-- ═══ EXECUTIVE OVERVIEW ═══ --}}
  <section class="bw-command-hero">
    <div class="bw-command-copy">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span class="bw-command-eyebrow"><i class="bi bi-stars"></i> BigWein Admin Command Center</span>
        <span class="bw-live-pill" style="{{ $demoModeEnabled ? 'background:#fff7ed;color:#c2410c;' : '' }}"><span class="bw-live-dot" style="{{ $demoModeEnabled ? 'background:#f97316;' : '' }}"></span> {{ $dataModeLabel }}</span>
      </div>
      <h2>Welcome back, {{ auth()->user()->name ?? 'Admin' }}</h2>
      <p>Track listings, approvals, owners and marketplace activity from one focused workspace.</p>
      <div class="bw-command-actions">
        <a href="{{ url('property/create') }}" class="bw-command-primary"><i class="bi bi-plus-circle"></i> Add Property</a>
        <a href="{{ url('property-approval') }}" class="bw-command-secondary"><i class="bi bi-patch-check"></i> Review {{ $pendingCount }} Approvals</a>
        <a href="{{ url('reports') }}" class="bw-command-secondary"><i class="bi bi-graph-up-arrow"></i> View Reports</a>
      </div>
    </div>
    <div class="bw-command-score">
      <div class="bw-score-ring">
        <strong>{{ $stats['listing_health'] }}%</strong>
        <span>Listing health</span>
      </div>
      <div class="bw-score-list">
        <div><span>Live inventory</span><b>{{ number_format($stats['live_inventory']) }}</b></div>
        <div><span>New this month</span><b>+{{ $stats['new_this_month'] }}</b></div>
        <div><span>Approval queue</span><b>{{ $pendingCount }}</b></div>
        <div><span>Owner approval rate</span><b>{{ $stats['approval_rate'] }}%</b></div>
      </div>
    </div>
  </section>

  <div class="bw-action-strip">
    <a href="{{ url('property') }}"><i class="bi bi-buildings"></i><span><strong>Manage Inventory</strong><small>Review all property listings</small></span><i class="bi bi-arrow-right"></i></a>
    <a href="{{ url('owner-management') }}"><i class="bi bi-person-badge"></i><span><strong>Owner Operations</strong><small>Track owners and onboarding</small></span><i class="bi bi-arrow-right"></i></a>
    <a href="{{ url('search-settings') }}"><i class="bi bi-sliders2"></i><span><strong>Search Controls</strong><small>Manage tabs and subtypes</small></span><i class="bi bi-arrow-right"></i></a>
    <a href="{{ url('demo-settings') }}"><i class="bi bi-database-check"></i><span><strong>Demo Center</strong><small>Manage seeded showcase data</small></span><i class="bi bi-arrow-right"></i></a>
  </div>

  {{-- ═══ STAT CARDS ═══ --}}
  <div class="bw-kpi-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">

    <a href="{{ url('property') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#FFF1F3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-buildings" style="font-size:20px;color:#E5343A;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ number_format($stats['total_properties']) }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">{{ $demoModeEnabled ? 'Demo Properties' : 'Real Properties' }}</div>
        <div style="font-size:11px;color:#16A34A;margin-top:2px;font-weight:600;"><i class="bi bi-arrow-up-short"></i> {{ $stats['new_this_month'] }} this month · {{ $stats['live_inventory'] }} live</div>
      </div>
    </a>

    <a href="{{ url('owner-management') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-house-door" style="font-size:20px;color:#1D4ED8;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ number_format($stats['total_owners']) }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">Active Owners</div>
        <div style="font-size:11px;color:#16A34A;margin-top:2px;font-weight:600;"><i class="bi bi-arrow-up-short"></i> {{ $stats['new_owners_today'] }} new owner{{ $stats['new_owners_today'] == 1 ? '' : 's' }} today</div>
      </div>
    </a>

    <a href="{{ url('customer') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#F0FDF4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-chat-dots" style="font-size:20px;color:#16A34A;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ number_format($stats['total_enquiries']) }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">Enquiries</div>
        <div style="font-size:11px;color:#16A34A;margin-top:2px;font-weight:600;"><i class="bi bi-arrow-up-short"></i> {{ $stats['enquiries_week'] }} this week</div>
      </div>
    </a>

    <a href="{{ url('property-approval') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#FFFBEB;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-clock-history" style="font-size:20px;color:#D97706;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ $pendingCount }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">Pending Approvals</div>
        <div style="font-size:11px;color:#D97706;margin-top:2px;font-weight:600;"><i class="bi bi-exclamation-triangle"></i> Needs review</div>
      </div>
    </a>

  </div>


  {{-- ═══ BUSINESS MARKETPLACE ═══ --}}
  <div style="background:linear-gradient(135deg,#172033 0%,#2D2435 100%);border-radius:14px;padding:18px;color:#fff;">
    <div style="display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:14px;">
      <div>
        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#fda4af;font-weight:800;">Business Marketplace</div>
        <div style="font-size:18px;font-weight:800;margin-top:3px;">Business for Sale Operations</div>
        <div style="font-size:11px;color:#cbd5e1;margin-top:4px;">Manage business listings, approvals, categories and buyer enquiries independently from Property.</div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ url('business-approvals') }}" style="background:#EF3F45;color:#fff;text-decoration:none;padding:9px 12px;border-radius:9px;font-size:11px;font-weight:800;">Review {{ $bizStats['pending'] }} Approvals</a>
        <a href="{{ url('business-enquiries-admin') }}" style="background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);color:#fff;text-decoration:none;padding:9px 12px;border-radius:9px;font-size:11px;font-weight:800;">{{ $bizStats['new_enquiries'] }} New Enquiries</a>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
      <a href="{{ url('business-approvals?status=all') }}" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px;text-decoration:none;color:#fff;">
        <div style="font-size:22px;font-weight:800;">{{ $bizStats['total'] }}</div><div style="font-size:10px;color:#cbd5e1;margin-top:3px;">Total Businesses</div>
      </a>
      <a href="{{ url('business-approvals?status=approved') }}" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px;text-decoration:none;color:#fff;">
        <div style="font-size:22px;font-weight:800;">{{ $bizStats['live'] }}</div><div style="font-size:10px;color:#cbd5e1;margin-top:3px;">Live Businesses</div>
      </a>
      <a href="{{ url('business-approvals?status=pending') }}" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px;text-decoration:none;color:#fff;">
        <div style="font-size:22px;font-weight:800;">{{ $bizStats['pending'] }}</div><div style="font-size:10px;color:#cbd5e1;margin-top:3px;">Pending Approvals</div>
      </a>
      <a href="{{ url('business-enquiries-admin') }}" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px;text-decoration:none;color:#fff;">
        <div style="font-size:22px;font-weight:800;">{{ $bizStats['enquiries'] }}</div><div style="font-size:10px;color:#cbd5e1;margin-top:3px;">Business Enquiries</div>
      </a>
    </div>
  </div>

  {{-- ═══ CITY BARS + CATEGORY BREAKDOWN ═══ --}}
  <div class="bw-insight-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

    {{-- City-wise count --}}
    <div style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;overflow:hidden;">
      <div style="padding:14px 18px;border-bottom:1px solid #F8FAFC;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:14px;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:7px;">
          <i class="bi bi-geo-alt-fill" style="color:#E5343A;"></i> City-wise Properties
        </div>
        <span style="background:#F0FDF4;color:#166534;font-size:11px;font-weight:600;padding:3px 9px;border-radius:12px;">Live count</span>
      </div>
      <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
        @php $maxCity = $cityStats->max('total') ?: 1; $barColors=['#E5343A','#1D4ED8','#16A34A','#D97706','#7C3AED','#0E7490']; @endphp
        @forelse($cityStats as $cs)
        <div>
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;">
            <span style="font-weight:600;color:#0F172A;">{{ $cs->city }}</span>
            <span style="color:#64748B;">{{ $cs->total }} properties</span>
          </div>
          <div style="background:#F1F5F9;border-radius:3px;height:7px;overflow:hidden;">
            <div style="width:{{ round(($cs->total/$maxCity)*100) }}%;height:100%;background:{{ $barColors[$loop->index % 6] }};border-radius:3px;"></div>
          </div>
          <div style="display:flex;gap:8px;margin-top:4px;font-size:11px;color:#94A3B8;">
            <span>Sale: {{ $cs->for_sale }}</span><span>·</span><span>Rent: {{ $cs->for_rent }}</span>
          </div>
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:#94A3B8;font-size:13px;">No property data yet</div>
        @endforelse
      </div>
    </div>

    {{-- Category breakdown --}}
    <div style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;overflow:hidden;">
      <div style="padding:14px 18px;border-bottom:1px solid #F8FAFC;">
        <div style="font-size:14px;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:7px;">
          <i class="bi bi-tag-fill" style="color:#E5343A;"></i> Category Breakdown <span style="font-size:10px;color:#94A3B8;font-weight:500;">(live approved listings)</span>
        </div>
      </div>
      <div style="padding:14px 18px;display:flex;flex-direction:column;gap:8px;">
        @foreach($categoryBreakdown as $cat)
        <div style="border:1px solid #F1F5F9;border-radius:9px;overflow:hidden;">
          <div style="background:#F8FAFC;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:12px;font-weight:600;color:#0F172A;display:flex;align-items:center;gap:6px;">
              <i class="{{ $cat['icon'] }}" style="color:{{ $cat['color'] }};font-size:14px;"></i> {{ $cat['name'] }}
            </div>
            <span style="font-size:13px;font-weight:700;color:#0F172A;">{{ $cat['total'] }}</span>
          </div>
          @if(!empty($cat['subs']))
          <div style="padding:7px 12px;display:flex;gap:6px;flex-wrap:wrap;">
            @foreach($cat['subs'] as $sub)
            <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:{{ $sub['bg'] }};color:{{ $sub['color'] }};">
              {{ $sub['label'] }} {{ $sub['count'] }}
            </span>
            @endforeach
          </div>
          @endif
        </div>
        @endforeach
      </div>
    </div>

  </div>

  {{-- ═══ CITY-WISE REPORTS TABLE ═══ --}}
  <div style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #F8FAFC;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
      <div style="font-size:14px;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:7px;">
        <i class="bi bi-bar-chart-line-fill" style="color:#E5343A;"></i> City-wise Reports
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <select id="rptCity" class="form-select form-select-sm" style="width:auto;font-size:11px;">
          <option value="">All Cities</option>
          @foreach($cities as $c)<option>{{ $c }}</option>@endforeach
        </select>
        <select id="rptType" class="form-select form-select-sm" style="width:auto;font-size:11px;">
          <option value="">All Types</option><option value="0">For Sale</option><option value="1">For Rent</option>
        </select>
        <select id="rptPeriod" class="form-select form-select-sm" style="width:auto;font-size:11px;">
          <option value="month">This Month</option><option value="quarter">This Quarter</option><option value="year">This Year</option><option value="">All Time</option>
        </select>
        <button onclick="applyReportFilter()" class="btn btn-sm" style="background:#E5343A;color:#fff;border:none;font-size:11px;font-weight:600;"><i class="bi bi-funnel-fill"></i> Apply</button>
        <a href="{{ url('reports') }}" style="font-size:11px;color:#E5343A;font-weight:600;text-decoration:none;">Full Report <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
    <div style="display:flex;border-bottom:1px solid #F1F5F9;">
      @foreach(['Overview','For Sale','For Rent','Enquiries'] as $tab)
      <div onclick="bwTab(this)" style="padding:9px 16px;font-size:12px;font-weight:600;color:{{ $tab==='Overview'?'#E5343A':'#64748B' }};border-bottom:2px solid {{ $tab==='Overview'?'#E5343A':'transparent' }};margin-bottom:-1px;cursor:pointer;">{{ $tab }}</div>
      @endforeach
    </div>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:12px;table-layout:auto;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:10px 12px;text-align:left;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;letter-spacing:.4px;">City</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">Total</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">For Sale</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">For Rent</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">Enquiries</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">Owners</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">Trend</th>
            <th style="padding:10px 8px;text-align:center;font-weight:700;font-size:11px;color:#64748B;border-bottom:1px solid #F1F5F9;text-transform:uppercase;">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cityReports as $cr)
          <tr style="border-bottom:1px solid #F8FAFC;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
            <td style="padding:11px 12px;font-weight:600;color:#0F172A;"><i class="bi bi-pin-map" style="color:#E5343A;margin-right:5px;"></i>{{ $cr->city }}</td>
            <td style="padding:11px 8px;text-align:center;font-weight:700;color:#0F172A;">{{ $cr->total }}</td>
            <td style="padding:11px 8px;text-align:center;"><span style="background:#EFF6FF;color:#1D4ED8;padding:2px 8px;border-radius:8px;font-size:11px;font-weight:600;">{{ $cr->for_sale }}</span></td>
            <td style="padding:11px 8px;text-align:center;"><span style="background:#FFF7ED;color:#C2410C;padding:2px 8px;border-radius:8px;font-size:11px;font-weight:600;">{{ $cr->for_rent }}</span></td>
            <td style="padding:11px 8px;text-align:center;color:#374151;">{{ $cr->enquiries ?? 0 }}</td>
            <td style="padding:11px 8px;text-align:center;color:#374151;">{{ $cr->owners ?? 0 }}</td>
            <td style="padding:11px 8px;text-align:center;">
              @if(($cr->trend ?? 0) > 0)<span style="color:#16A34A;font-weight:700;"><i class="bi bi-arrow-up-short"></i>{{ $cr->trend }}%</span>
              @elseif(($cr->trend ?? 0) < 0)<span style="color:#E5343A;font-weight:700;"><i class="bi bi-arrow-down-short"></i>{{ abs($cr->trend) }}%</span>
              @else<span style="color:#D97706;font-weight:700;">—</span>@endif
            </td>
            <td style="padding:11px 8px;text-align:center;"><a href="{{ url('property?city='.$cr->city) }}" style="font-size:11px;color:#E5343A;font-weight:600;text-decoration:none;">View</a></td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:24px;color:#94A3B8;">No data available</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ═══ PENDING APPROVALS ═══ --}}
  <div style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #F8FAFC;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
      <div style="font-size:14px;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-clock-history" style="color:#E5343A;"></i> Pending Approvals
        @if($pendingCount > 0)
        <span style="background:#FEE2E2;color:#991B1B;font-size:11px;font-weight:700;padding:2px 9px;border-radius:12px;">{{ $pendingCount }} awaiting</span>
        @endif
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <select class="form-select form-select-sm" style="width:auto;font-size:11px;">
          <option value="">All Cities</option>
          @foreach($cities as $c)<option>{{ $c }}</option>@endforeach
        </select>
        <a href="{{ url('property-approval') }}" style="font-size:11px;color:#E5343A;font-weight:600;text-decoration:none;">View All <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
    <div style="padding:14px 18px;display:flex;flex-direction:column;gap:8px;">
      @forelse($pendingProperties as $pp)
      @php
        $catName  = $categories->firstWhere('id',$pp->category_id)?->category ?? 'Property';
        $catMap   = ['Villa'=>['bg'=>'#FFF1F3','color'=>'#9F1239','icon'=>'bi-house','border'=>'#E5343A'],'Plot'=>['bg'=>'#EFF6FF','color'=>'#1E40AF','icon'=>'bi-map','border'=>'#1D4ED8'],'Commercial'=>['bg'=>'#F0FDF4','color'=>'#166534','icon'=>'bi-building','border'=>'#16A34A'],'PG House'=>['bg'=>'#FFFBEB','color'=>'#92400E','icon'=>'bi-people','border'=>'#D97706'],'Townhouse'=>['bg'=>'#F5F3FF','color'=>'#6B21A8','icon'=>'bi-houses','border'=>'#7C3AED']];
        $cs       = $catMap[$catName] ?? ['bg'=>'#F3F4F6','color'=>'#374151','icon'=>'bi-building-fill','border'=>'#64748B'];
      @endphp
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid #F1F5F9;border-radius:10px;transition:border-color .15s;" onmouseover="this.style.borderColor='#E2E8F0'" onmouseout="this.style.borderColor='#F1F5F9'">
        <div style="width:40px;height:40px;border-radius:9px;background:#F8FAFC;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;border-left:3px solid {{ $cs['border'] }};border-radius:0;">
          <i class="bi {{ $cs['icon'] }}" style="color:{{ $cs['border'] }};"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:#0F172A;">{{ Str::limit($pp->title, 55) }}</div>
          <div style="font-size:11px;color:#94A3B8;margin-top:2px;">
            <i class="bi bi-geo-alt" style="color:#E5343A;"></i> {{ $pp->city }}, {{ $pp->state }} &nbsp;·&nbsp;
            <i class="bi bi-person"></i> {{ $pp->owner_name ?? 'Owner' }} &nbsp;·&nbsp;
            <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($pp->created_at)->diffForHumans() }}
          </div>
        </div>
        <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:12px;flex-shrink:0;background:{{ $cs['bg'] }};color:{{ $cs['color'] }};">{{ $catName }}</span>
        <div style="display:flex;gap:6px;flex-shrink:0;">
          <button class="btn btn-sm" style="background:#DCFCE7;color:#166534;border:none;font-size:11px;font-weight:600;" onclick="approveProperty({{ $pp->id }},'approved',this)">
            <i class="bi bi-check-circle"></i> Approve
          </button>
          <button class="btn btn-sm" style="background:#FEE2E2;color:#991B1B;border:none;font-size:11px;font-weight:600;" onclick="approveProperty({{ $pp->id }},'rejected',this)">
            <i class="bi bi-x-circle"></i> Reject
          </button>
        </div>
      </div>
      @empty
      <div style="text-align:center;padding:32px;color:#94A3B8;">
        <i class="bi bi-check2-circle" style="font-size:36px;display:block;margin-bottom:8px;color:#E2E8F0;"></i>
        All caught up — no pending approvals!
      </div>
      @endforelse
    </div>
  </div>

</div>
@endsection

@section('script')
<script>
const CSRF = '{{ csrf_token() }}';
function bwTab(btn) {
  btn.parentElement.querySelectorAll('div').forEach(t => {
    t.style.color='#64748B'; t.style.borderBottomColor='transparent';
  });
  btn.style.color='#E5343A'; btn.style.borderBottomColor='#E5343A';
}
function applyFilter() {
  const city = document.getElementById('filterCity')?.value;
  const period = document.getElementById('filterPeriod')?.value;
  window.location.href = '{{ url("home") }}?fc=' + encodeURIComponent(city) + '&fp=' + encodeURIComponent(period);
}
function applyReportFilter() {
  const city = document.getElementById('rptCity')?.value;
  const type = document.getElementById('rptType')?.value;
  const period = document.getElementById('rptPeriod')?.value;
  window.location.href = '{{ url("home") }}?fc=' + encodeURIComponent(city) + '&fp=' + encodeURIComponent(period);
}
async function approveProperty(id, status, btn) {
  const label = status === 'approved' ? 'Approve' : 'Reject';
  const reason = status === 'rejected' ? prompt('Rejection reason (required):') : null;
  if (status === 'rejected' && !reason) return;
  if (!confirm(label + ' this property?')) return;

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

  const body = { id: id, request_status: status };
  if (reason) body.reject_reason = reason;

  try {
    const res = await fetch('{{ url("update-property-request-status") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json'
      },
      body: JSON.stringify(body)
    });

    // Handle both JSON and non-JSON responses
    const text = await res.text();
    let data = {};
    try { data = JSON.parse(text); } catch(e) {
      // If redirect response (302), it means success
      if (res.redirected || res.ok) data = { error: false };
    }

    if (!data.error) {
      const row = btn.closest('.bw-approval-item') || btn.closest('[style*="border:1px"]');
      if (row) {
        row.style.transition = 'opacity .4s';
        row.style.opacity = '0.3';
        row.style.pointerEvents = 'none';
      }
      toastr.success(status === 'approved' ? 'Property approved — now live on frontend!' : 'Property rejected.');
      // Refresh pending count after 1.5s
      setTimeout(() => location.reload(), 1500);
    } else {
      toastr.error(data.message || 'Failed to update status');
      btn.disabled = false;
      btn.innerHTML = status === 'approved'
        ? '<i class="bi bi-check-circle"></i> Approve'
        : '<i class="bi bi-x-circle"></i> Reject';
    }
  } catch(e) {
    toastr.error('Request failed. Please try from Approvals page.');
    btn.disabled = false;
  }
}
</script>

<script>
const BWCSRF = document.querySelector('meta[name="csrf-token"]')?.content || "";
async function singleApprove(id, status, btn) {
  btn.disabled = true;
  const r = await fetch(document.querySelector('meta[name="base-url"]')?.content+"/update-property-request-status" || "/update-property-request-status", {
    method:"POST", headers:{"Content-Type":"application/json","X-CSRF-TOKEN":BWCSRF,"Accept":"application/json"},
    body: JSON.stringify({id, request_status: status})
  });
  const d = await r.json();
  if (!d.error) {
    const row = document.getElementById("aprow-"+id);
    if(row){row.style.opacity="0";setTimeout(()=>row.remove(),300);}
  } else { btn.disabled=false; }
}
function toggleSelectAll(cb){
  document.querySelectorAll(".pending-chk").forEach(c=>c.checked=cb.checked);
  updateBulkBtns();
}
function updateBulkBtns(){
  const n=document.querySelectorAll(".pending-chk:checked").length;
  const ab=document.getElementById("bulkApproveBtn");
  const rb=document.getElementById("bulkRejectBtn");
  if(ab)ab.style.display=n?"inline-flex":"none";
  if(rb)rb.style.display=n?"inline-flex":"none";
}
async function bulkAction(status){
  const ids=[...document.querySelectorAll(".pending-chk:checked")].map(c=>c.value);
  if(!ids.length)return;
  if(!confirm((status==="approved"?"Approve":"Reject")+" "+ids.length+" propert"+( ids.length>1?"ies":"y")+"?"))return;
  await Promise.all(ids.map(id=>fetch("/update-property-request-status",{
    method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":BWCSRF,"Accept":"application/json"},
    body:JSON.stringify({id,request_status:status})
  })));
  ids.forEach(id=>{const r=document.getElementById("aprow-"+id);if(r){r.style.opacity="0";setTimeout(()=>r.remove(),300);}});
  updateBulkBtns();
}
</script>
@endsection
