@extends('layouts.main')
@section('title') Reports & Analytics @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-graph-up-arrow me-2" style="color:#e30620;"></i>Reports & Analytics</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Reports</li>
      </ol></nav>
    </div>
    <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-md-end align-items-center gap-2 mt-2 mt-md-0 flex-wrap">
      {{-- Export buttons --}}
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" style="border-radius:8px;font-weight:600;">
          <i class="bi bi-download me-1"></i> Export CSV
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ url('reports/export?type=properties') }}"><i class="bi bi-building me-2"></i>Properties</a></li>
          <li><a class="dropdown-item" href="{{ url('reports/export?type=customers') }}"><i class="bi bi-people me-2"></i>Customers</a></li>
          <li><a class="dropdown-item" href="{{ url('reports/export?type=enquiries') }}"><i class="bi bi-chat-dots me-2"></i>Enquiries</a></li>
        </ul>
      </div>
      <button class="btn btn-danger btn-sm" onclick="window.print()" style="border-radius:8px;font-weight:600;">
        <i class="bi bi-printer me-1"></i> Print
      </button>
    </div>
  </div>
</div>
@endsection

@section('css')
<style>
.report-card{background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);border:1px solid #F1F5F9;margin-bottom:22px;}
.report-card .rc-head{padding:18px 22px;border-bottom:1px solid #F1F5F9;display:flex;align-items:center;justify-content:space-between;}
.rc-head h6{font-weight:800;font-size:15px;color:#111;margin:0;display:flex;align-items:center;gap:8px;}
.rc-head h6 i{color:#e30620;}
.rc-head .rc-sub{font-size:12px;color:#9CA3AF;}
.report-card .rc-body{padding:18px 22px;}

/* KPI cards */
.kpi-row{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:22px;}
.kpi-box{background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1px solid #F1F5F9;transition:transform .2s;}
.kpi-box:hover{transform:translateY(-2px);}
.kpi-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:10px;}
.kpi-val{font-size:26px;font-weight:900;color:#111;line-height:1;font-family:'Plus Jakarta Sans','Inter',sans-serif;}
.kpi-lbl{font-size:11px;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-top:3px;}
.kpi-change{font-size:11px;font-weight:700;margin-top:6px;display:flex;align-items:center;gap:3px;}
.kpi-change.up{color:#16A34A;} .kpi-change.down{color:#e30620;} .kpi-change.neutral{color:#9CA3AF;}

/* Date range filter */
.range-bar{background:#fff;border-radius:14px;padding:14px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);margin-bottom:22px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.range-btn{padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;border:1.5px solid #E2E8F0;background:#fff;cursor:pointer;transition:all .2s;color:#6B7280;}
.range-btn.active,.range-btn:hover{background:#e30620;color:#fff;border-color:#e30620;}
.range-sep{color:#E2E8F0;font-size:16px;}

/* Charts grid */
.charts-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px;}
.charts-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:18px;}
.charts-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}

/* Tables */
.r-table{width:100%;border-collapse:collapse;font-size:13px;}
.r-table th{padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#9CA3AF;background:#FAFAFA;border-bottom:1px solid #F1F5F9;}
.r-table td{padding:12px 14px;border-bottom:1px solid #F9FAFB;color:#374151;vertical-align:middle;}
.r-table tr:hover td{background:#FAFAFA;}
.r-table tr:last-child td{border-bottom:none;}
.rank-num{width:28px;height:28px;border-radius:50%;background:#F1F5F9;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#374151;}
.rank-num.gold{background:#FEF9C3;color:#CA8A04;}
.rank-num.silver{background:#F1F5F9;color:#6B7280;}
.rank-num.bronze{background:#FEF3C7;color:#D97706;}
.prop-type-pill{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;}
.pill-sale{background:#EFF6FF;color:#2563EB;} .pill-rent{background:#FFFBEB;color:#D97706;}
.status-pill{padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;}
.pill-approved{background:#DCFCE7;color:#16A34A;} .pill-pending{background:#FEF9C3;color:#CA8A04;} .pill-rejected{background:#FEE2E2;color:#DC2626;}

/* Progress bars */
.prog-bar-wrap{margin-bottom:12px;}
.prog-label{display:flex;justify-content:space-between;margin-bottom:5px;font-size:12px;font-weight:600;color:#374151;}
.prog-bar-bg{height:8px;background:#F1F5F9;border-radius:10px;overflow:hidden;}
.prog-bar-fill{height:100%;border-radius:10px;transition:width .8s ease;}

@media print {
  .page-title,.sidebar,.topbar,.range-bar,button,.dropdown{display:none!important;}
  .report-card{box-shadow:none!important;border:1px solid #ddd!important;page-break-inside:avoid;}
}
@media(max-width:1100px){.kpi-row{grid-template-columns:repeat(3,1fr);}.charts-grid,.charts-grid-3{grid-template-columns:1fr;}.charts-grid-2{grid-template-columns:1fr;}}
@media(max-width:600px){.kpi-row{grid-template-columns:repeat(2,1fr);}}
</style>
@endsection

@section('content')
<section class="section">

{{-- DATE RANGE FILTER --}}
<form method="GET" action="{{ url('reports') }}" id="rangeForm">
<div class="range-bar">
  <span style="font-size:12px;font-weight:700;color:#374151;">Period:</span>
  @foreach(['7'=>'Last 7 Days','30'=>'Last 30 Days','90'=>'Last 90 Days','180'=>'Last 6 Months','365'=>'Last Year'] as $val=>$label)
  <button type="button" class="range-btn {{ $range == $val ? 'active' : '' }}" onclick="setRange('{{ $val }}')">{{ $label }}</button>
  @endforeach
  <span class="range-sep">|</span>
  <div class="d-flex align-items-center gap-2">
    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" style="border-radius:8px;font-size:12px;width:130px;">
    <span style="color:#9CA3AF;font-size:12px;">to</span>
    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" style="border-radius:8px;font-size:12px;width:130px;">
    <button type="submit" class="btn btn-sm btn-danger" style="border-radius:8px;font-weight:700;">Apply</button>
  </div>
  <input type="hidden" name="range" id="rangeInput" value="{{ $range }}">
  <span style="font-size:12px;color:#9CA3AF;margin-left:auto;">
    {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
  </span>
</div>
</form>

{{-- KPI CARDS --}}
<div class="kpi-row">
  @php
    $kpis = [
      ['total_click'=>null,'icon'=>'bi-building-fill','bg'=>'#EFF6FF','color'=>'#2563EB','val'=>number_format($totalProperties),'lbl'=>'New Listings','change'=>$prevProps>0?round(($totalProperties-$prevProps)/$prevProps*100):0],
      ['icon'=>'bi-person-fill','bg'=>'#F0FDF4','color'=>'#16A34A','val'=>number_format($totalCustomers),'lbl'=>'New Buyers','change'=>0],
      ['icon'=>'bi-building-fill-gear','bg'=>'#EDE9FE','color'=>'#7C3AED','val'=>number_format($totalOwners),'lbl'=>'New Owners','change'=>0],
      ['icon'=>'bi-chat-dots-fill','bg'=>'#FFF7ED','color'=>'#EA580C','val'=>number_format($totalEnquiries),'lbl'=>'Enquiries','change'=>$prevEnq>0?round(($totalEnquiries-$prevEnq)/$prevEnq*100):0],
      ['icon'=>'bi-eye-fill','bg'=>'#F0F9FF','color'=>'#0891B2','val'=>number_format($totalViews),'lbl'=>'Property Views','change'=>0],
      ['icon'=>'bi-currency-rupee','bg'=>'#F0FDF4','color'=>'#16A34A','val'=>'₹'.number_format($totalRevenue),'lbl'=>'Subscription Revenue','change'=>0],
    ];
  @endphp
  @foreach($kpis as $kpi)
  <div class="kpi-box">
    <div class="kpi-icon" style="background:{{ $kpi['bg'] }};"><i class="bi {{ $kpi['icon'] }}" style="color:{{ $kpi['color'] }};"></i></div>
    <div class="kpi-val">{{ $kpi['val'] }}</div>
    <div class="kpi-lbl">{{ $kpi['lbl'] }}</div>
    @if(isset($kpi['change']) && $kpi['change'] != 0)
    <div class="kpi-change {{ $kpi['change'] > 0 ? 'up' : 'down' }}">
      <i class="bi bi-arrow-{{ $kpi['change'] > 0 ? 'up' : 'down' }}-short"></i> {{ abs($kpi['change']) }}% vs prev period
    </div>
    @else
    <div class="kpi-change neutral">vs previous period</div>
    @endif
  </div>
  @endforeach
</div>

{{-- ROW 1: Property Trends + Sale vs Rent --}}
<div class="charts-grid">
  <div class="report-card">
    <div class="rc-head">
      <h6><i class="bi bi-graph-up"></i> Property Listing Trends</h6>
      <span class="rc-sub">Last 12 months</span>
    </div>
    <div class="rc-body"><canvas id="propTrendsChart" height="100"></canvas></div>
  </div>
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-pie-chart-fill"></i> Sale vs Rent</h6></div>
    <div class="rc-body" style="display:flex;flex-direction:column;align-items:center;">
      <canvas id="saleRentChart" height="200" style="max-width:200px;"></canvas>
      <div class="mt-3 w-100">
        @php $total = $saleVsRent['sale'] + $saleVsRent['rent'] ?: 1; @endphp
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
          <span><i class="bi bi-circle-fill" style="color:#2563EB;font-size:8px;"></i> For Sale</span>
          <strong>{{ $saleVsRent['sale'] }} ({{ round($saleVsRent['sale']/$total*100) }}%)</strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span><i class="bi bi-circle-fill" style="color:#F97316;font-size:8px;"></i> For Rent</span>
          <strong>{{ $saleVsRent['rent'] }} ({{ round($saleVsRent['rent']/$total*100) }}%)</strong>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ROW 2: Customer Growth + Enquiry Trends --}}
<div class="charts-grid">
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-people-fill"></i> Customer Growth</h6><span class="rc-sub">Buyers vs Owners — last 12 months</span></div>
    <div class="rc-body"><canvas id="customerGrowthChart" height="100"></canvas></div>
  </div>
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-chat-dots-fill"></i> Enquiry Trends</h6><span class="rc-sub">Last 12 months</span></div>
    <div class="rc-body"><canvas id="enquiryTrendsChart" height="100"></canvas></div>
  </div>
</div>

{{-- ROW 3: Properties by City + Category --}}
<div class="charts-grid">
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-geo-alt-fill"></i> Properties by City</h6><span class="rc-sub">Top 10 cities</span></div>
    <div class="rc-body">
      @php $maxCity = $propsByCity->max('total') ?: 1; @endphp
      @foreach($propsByCity as $city)
      <div class="prog-bar-wrap">
        <div class="prog-label"><span>{{ $city->city }}</span><span style="font-weight:800;">{{ number_format($city->total) }}</span></div>
        <div class="prog-bar-bg"><div class="prog-bar-fill" style="width:{{ round($city->total/$maxCity*100) }}%;background:linear-gradient(90deg,#e30620,#FF6B6B);"></div></div>
      </div>
      @endforeach
      @if($propsByCity->isEmpty())<div style="text-align:center;color:#9CA3AF;padding:20px;">No data available</div>@endif
    </div>
  </div>
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-grid-fill"></i> Properties by Category</h6></div>
    <div class="rc-body">
      <canvas id="categoryChart" height="220"></canvas>
      <div class="mt-3">
        @foreach($propsByCategory as $cat)
        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;padding:5px 0;border-bottom:1px solid #F9FAFB;">
          <span>{{ $cat->name ?? 'Unknown' }}</span>
          <strong>{{ $cat->total }}</strong>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- ROW 4: Owner Breakdown + Status Breakdown + Revenue --}}
<div class="charts-grid-3">
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-building-fill-gear"></i> Owner Breakdown</h6></div>
    <div class="rc-body" style="text-align:center;">
      <canvas id="ownerBreakdownChart" height="180"></canvas>
      <div class="mt-3">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
          <span><i class="bi bi-circle-fill" style="color:#2563EB;font-size:8px;"></i> Sellers</span>
          <strong>{{ $ownerBreakdown['sellers'] }}</strong>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px;">
          <span><i class="bi bi-circle-fill" style="color:#7C3AED;font-size:8px;"></i> Builders</span>
          <strong>{{ $ownerBreakdown['builders'] }}</strong>
        </div>
      </div>
    </div>
  </div>
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-check-circle-fill"></i> Property Status</h6><span class="rc-sub">Owner submitted</span></div>
    <div class="rc-body">
      @php
        $totalStatus = $statusBreakdown->sum('total') ?: 1;
        $statusColors = ['approved'=>['#DCFCE7','#16A34A'],'pending'=>['#FEF9C3','#CA8A04'],'rejected'=>['#FEE2E2','#DC2626']];
      @endphp
      @foreach($statusBreakdown as $status)
      @php $colors = $statusColors[$status->request_status] ?? ['#F1F5F9','#6B7280']; @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-radius:10px;background:{{ $colors[0] }};margin-bottom:10px;">
        <span style="font-size:13px;font-weight:600;color:{{ $colors[1] }};">{{ ucfirst($status->request_status) }}</span>
        <div style="text-align:right;">
          <div style="font-size:20px;font-weight:900;color:{{ $colors[1] }};">{{ $status->total }}</div>
          <div style="font-size:10px;color:{{ $colors[1] }};opacity:.7;">{{ round($status->total/$totalStatus*100) }}%</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="report-card">
    <div class="rc-head"><h6><i class="bi bi-currency-rupee"></i> Subscription Revenue</h6><span class="rc-sub">Last 12 months</span></div>
    <div class="rc-body"><canvas id="revenueChart" height="200"></canvas></div>
  </div>
</div>

{{-- ROW 5: Top Enquiry Cities --}}
<div class="report-card">
  <div class="rc-head"><h6><i class="bi bi-pin-map-fill"></i> Top Cities by Enquiries</h6><span class="rc-sub">Most active buyer locations</span></div>
  <div class="rc-body">
    <canvas id="cityEnquiryChart" height="80"></canvas>
  </div>
</div>

{{-- ROW 6: Most Viewed Properties --}}
<div class="charts-grid-2">
  <div class="report-card">
    <div class="rc-head">
      <h6><i class="bi bi-eye-fill"></i> Most Viewed Properties</h6>
      <a href="{{ url('reports/export?type=properties') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:11px;"><i class="bi bi-download me-1"></i>CSV</a>
    </div>
    <div class="rc-body p-0">
      <table class="r-table">
        <thead><tr><th>#</th><th>Property</th><th>City</th><th>Type</th><th>Views</th></tr></thead>
        <tbody>
          @forelse($mostViewed as $i => $prop)
          <tr>
            <td>
              <div class="rank-num {{ $i===0?'gold':($i===1?'silver':($i===2?'bronze':'')) }}">{{ $i+1 }}</div>
            </td>
            <td>
              <a href="{{ url('property/'.$prop->id.'/edit') }}" style="font-weight:600;color:#111;text-decoration:none;display:block;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $prop->title }}</a>
              <span style="font-size:11px;color:#9CA3AF;">{{ $prop->owner_name }}</span>
            </td>
            <td style="font-size:12px;color:#6B7280;">{{ $prop->city }}</td>
            <td><span class="prop-type-pill {{ $prop->propery_type==0?'pill-sale':'pill-rent' }}">{{ $prop->propery_type==0?'Sale':'Rent' }}</span></td>
            <td><strong style="color:#2563EB;">{{ number_format($prop->total_click) }}</strong></td>
          </tr>
          @empty
          <tr><td colspan="5" style="text-align:center;padding:24px;color:#9CA3AF;">No data yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="report-card">
    <div class="rc-head">
      <h6><i class="bi bi-chat-dots-fill"></i> Most Enquired Properties</h6>
      <a href="{{ url('reports/export?type=enquiries') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:11px;"><i class="bi bi-download me-1"></i>CSV</a>
    </div>
    <div class="rc-body p-0">
      <table class="r-table">
        <thead><tr><th>#</th><th>Property</th><th>City</th><th>Type</th><th>Enquiries</th></tr></thead>
        <tbody>
          @forelse($mostEnquired as $i => $prop)
          <tr>
            <td>
              <div class="rank-num {{ $i===0?'gold':($i===1?'silver':($i===2?'bronze':'')) }}">{{ $i+1 }}</div>
            </td>
            <td>
              <div style="font-weight:600;color:#111;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $prop->title }}</div>
              <span style="font-size:11px;color:#9CA3AF;">{{ $prop->category_name }}</span>
            </td>
            <td style="font-size:12px;color:#6B7280;">{{ $prop->city }}</td>
            <td><span class="prop-type-pill {{ $prop->propery_type==0?'pill-sale':'pill-rent' }}">{{ $prop->propery_type==0?'Sale':'Rent' }}</span></td>
            <td><strong style="color:#e30620;">{{ number_format($prop->enquiry_count) }}</strong></td>
          </tr>
          @empty
          <tr><td colspan="5" style="text-align:center;padding:24px;color:#9CA3AF;">No data yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

</section>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const RED    = '#e30620';
const BLUE   = '#2563EB';
const GREEN  = '#16A34A';
const ORANGE = '#F97316';
const PURPLE = '#7C3AED';
const CYAN   = '#0891B2';
const chartDefaults = {
  responsive: true,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11, weight: '600' } } }
  },
  scales: {
    x: { grid: { color: '#F1F5F9' }, ticks: { font: { size: 11 } } },
    y: { grid: { color: '#F1F5F9' }, ticks: { font: { size: 11 }, beginAtZero: true, stepSize: 1 }, beginAtZero: true }
  }
};

// 1. Property Trends
new Chart(document.getElementById('propTrendsChart'), {
  type: 'line',
  data: {
    labels: {!! json_encode($propertyTrends['labels']) !!},
    datasets: [{
      label: 'New Listings',
      data: {!! json_encode($propertyTrends['data']) !!},
      borderColor: RED, backgroundColor: 'rgba(229,6,32,.08)',
      tension: .4, fill: true, pointBackgroundColor: RED, borderWidth: 2.5
    }]
  },
  options: chartDefaults
});

// 2. Sale vs Rent Donut
new Chart(document.getElementById('saleRentChart'), {
  type: 'doughnut',
  data: {
    labels: ['For Sale', 'For Rent'],
    datasets: [{ data: [{{ $saleVsRent['sale'] }}, {{ $saleVsRent['rent'] }}], backgroundColor: [BLUE, ORANGE], borderWidth: 0, hoverOffset: 4 }]
  },
  options: { responsive: true, cutout: '70%', plugins: { legend: { display: false } } }
});

// 3. Customer Growth
const cg = {!! json_encode($customerGrowth) !!};
new Chart(document.getElementById('customerGrowthChart'), {
  type: 'bar',
  data: {
    labels: cg.labels,
    datasets: [
      { label: 'Buyers', data: cg.buyers, backgroundColor: 'rgba(37,99,235,.8)', borderRadius: 6 },
      { label: 'Owners', data: cg.owners, backgroundColor: 'rgba(229,6,32,.8)', borderRadius: 6 }
    ]
  },
  options: { ...chartDefaults, scales: { ...chartDefaults.scales, x: { ...chartDefaults.scales.x, stacked: false }, y: { ...chartDefaults.scales.y, stacked: false } } }
});

// 4. Enquiry Trends
new Chart(document.getElementById('enquiryTrendsChart'), {
  type: 'line',
  data: {
    labels: {!! json_encode($enquiryTrends['labels']) !!},
    datasets: [{
      label: 'Enquiries',
      data: {!! json_encode($enquiryTrends['data']) !!},
      borderColor: ORANGE, backgroundColor: 'rgba(249,115,22,.08)',
      tension: .4, fill: true, pointBackgroundColor: ORANGE, borderWidth: 2.5
    }]
  },
  options: chartDefaults
});

// 5. Category Donut
new Chart(document.getElementById('categoryChart'), {
  type: 'doughnut',
  data: {
    labels: {!! json_encode($propsByCategory->pluck('name')->map(fn($n)=>$n??'Unknown')) !!},
    datasets: [{ data: {!! json_encode($propsByCategory->pluck('total')) !!},
      backgroundColor: [RED, BLUE, GREEN, ORANGE, PURPLE, CYAN, '#F59E0B', '#10B981'],
      borderWidth: 0, hoverOffset: 4
    }]
  },
  options: { responsive: true, cutout: '60%', plugins: { legend: { display: false } } }
});

// 6. Owner Breakdown Donut
new Chart(document.getElementById('ownerBreakdownChart'), {
  type: 'doughnut',
  data: {
    labels: ['Sellers', 'Builders'],
    datasets: [{ data: [{{ $ownerBreakdown['sellers'] }}, {{ $ownerBreakdown['builders'] }}],
      backgroundColor: [BLUE, PURPLE], borderWidth: 0, hoverOffset: 4
    }]
  },
  options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
});

// 7. Revenue Bar
const rev = {!! json_encode($revenueMonthly) !!};
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: rev.map(r => { const m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; return m[r.month-1]+' '+String(r.year).slice(2); }),
    datasets: [{ label: 'Revenue (₹)', data: rev.map(r => r.revenue), backgroundColor: 'rgba(22,163,74,.8)', borderRadius: 6 }]
  },
  options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, legend: { display: false } } }
});

// 8. City Enquiry Bar
const ce = {!! json_encode($topCitiesEnquiries) !!};
new Chart(document.getElementById('cityEnquiryChart'), {
  type: 'bar',
  data: {
    labels: ce.map(c => c.city),
    datasets: [{ label: 'Enquiries', data: ce.map(c => c.enquiries),
      backgroundColor: ce.map((_,i) => `hsl(${i*35},70%,55%)`), borderRadius: 8
    }]
  },
  options: { ...chartDefaults, plugins: { ...chartDefaults.plugins, legend: { display: false } } }
});

// Date range helper
function setRange(val) {
  document.getElementById('rangeInput').value = val;
  document.getElementById('rangeForm').submit();
}
</script>
@endsection
