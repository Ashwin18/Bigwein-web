@extends('layouts.main')
@section('title') Dashboard @endsection

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
<div style="padding:16px 20px 40px;display:flex;flex-direction:column;gap:18px;">

  {{-- ═══ STAT CARDS ═══ --}}
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">

    <a href="{{ url('property') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#FFF1F3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-buildings" style="font-size:20px;color:#E5343A;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ number_format($stats['total_properties']) }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">Total Properties</div>
        <div style="font-size:11px;color:#16A34A;margin-top:2px;font-weight:600;"><i class="bi bi-arrow-up-short"></i> {{ $stats['new_this_month'] }} this month</div>
      </div>
    </a>

    <a href="{{ url('owner-management') }}" style="background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;text-decoration:none;transition:border-color .15s;" onmouseover="this.style.borderColor='#E5343A'" onmouseout="this.style.borderColor='#F1F5F9'">
      <div style="width:44px;height:44px;border-radius:10px;background:#EFF6FF;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-house-door" style="font-size:20px;color:#1D4ED8;"></i>
      </div>
      <div>
        <div style="font-size:24px;font-weight:800;color:#0F172A;line-height:1;">{{ number_format($stats['total_owners']) }}</div>
        <div style="font-size:12px;color:#64748B;margin-top:3px;">Active Owners</div>
        <div style="font-size:11px;color:#16A34A;margin-top:2px;font-weight:600;"><i class="bi bi-arrow-up-short"></i> {{ $stats['new_owners_today'] }} today</div>
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

  {{-- ═══ CITY BARS + CATEGORY BREAKDOWN ═══ --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

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
          <i class="bi bi-tag-fill" style="color:#E5343A;"></i> Category Breakdown
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
  if (!confirm((status==='approved'?'Approve':'Reject') + ' this property?')) return;
  btn.disabled = true;
  try {
    const res = await fetch('{{ url("property-approval") }}/' + status, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body: JSON.stringify({property_id:id})
    });
    const data = await res.json();
    if (!data.error) {
      toastr.success(data.message || 'Property ' + status);
      btn.closest('[style*="border:1px"]').style.opacity='0.4';
      btn.closest('[style*="border:1px"]').style.pointerEvents='none';
    } else { toastr.error(data.message || 'Error'); btn.disabled=false; }
  } catch(e) { toastr.error('Request failed'); btn.disabled=false; }
}
</script>
@endsection
