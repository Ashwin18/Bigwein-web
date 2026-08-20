@php
$lang = Session::get('language');
@endphp
@extends('layouts.main')
@section('title') {{ __('Dashboard') }} @endsection

@section('css')
<style>
/* Dashboard specific styles */
.bw-dash{padding:4px 0 20px;}
.dash-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
.dash-header h1{font-size:26px;font-weight:800;color:#111827;margin-bottom:4px;}
.dash-header p{font-size:14px;color:#667085;}
.date-badge{background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:10px 16px;font-weight:600;font-size:13px;color:#344054;display:flex;align-items:center;gap:7px;}

/* KPI Grid */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px;}
.kpi-card{background:#fff;border:1px solid #edf0f5;border-radius:18px;padding:20px;box-shadow:0 4px 16px rgba(16,24,40,.05);transition:transform .2s;}
.kpi-card:hover{transform:translateY(-2px);}
.kpi-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:14px;}
.kpi-num{font-size:28px;font-weight:800;color:#111827;line-height:1;}
.kpi-label{font-size:12px;color:#667085;margin-top:4px;}
.kpi-up{font-size:12px;font-weight:700;color:#16a34a;margin-top:8px;display:flex;align-items:center;gap:3px;}
.kpi-down{color:#e30620;}

/* Main grid */
.dash-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:20px;}
.dash-panel{background:#fff;border:1px solid #edf0f5;border-radius:18px;padding:22px;box-shadow:0 4px 16px rgba(16,24,40,.05);}
.panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
.panel-head h3{font-size:16px;font-weight:700;color:#111827;}
.panel-link{color:#e30620;font-size:13px;font-weight:700;text-decoration:none;}
.chart-tabs{display:flex;gap:6px;}
.chart-tab{padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid #e4e7ec;background:#fff;cursor:pointer;color:#667085;}
.chart-tab.active{background:#e30620;color:#fff;border-color:#e30620;}

/* Type list */
.type-row{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid #eef0f5;font-size:14px;color:#344054;}
.type-row:last-child{border-bottom:none;}
.type-row b{color:#111827;font-weight:700;}
.type-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:7px;}

/* Wide bottom grid */
.dash-wide{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:20px;}
.city-table{width:100%;border-collapse:collapse;}
.city-table th{text-align:left;padding:11px 14px;font-size:11px;font-weight:700;color:#667085;text-transform:uppercase;letter-spacing:.5px;background:#fafafa;border-bottom:1px solid #eef0f5;}
.city-table td{padding:12px 14px;font-size:13px;border-bottom:1px solid #eef0f5;color:#344054;}
.city-table tr:hover td{background:#fafafa;}
.city-table .num{font-weight:700;color:#111827;}

/* Quick Actions */
.quick-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.quick-item{padding:18px 12px;border:1px solid #edf0f5;border-radius:14px;text-align:center;font-size:12px;font-weight:700;color:#344054;background:#fff;cursor:pointer;transition:all .18s;text-decoration:none;display:block;}
.quick-item:hover{border-color:#e30620;color:#e30620;background:#fff1f3;}
.quick-item .qi-icon{font-size:26px;margin-bottom:7px;display:block;}

/* Recent table */
.prop-badge-sell{background:#e8f1ff;color:#155eef;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}
.prop-badge-rent{background:#fff0df;color:#f97316;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}
.prop-badge-pending{background:#fef3c7;color:#d97706;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}
.prop-badge-approved{background:#eafaf0;color:#16a34a;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}
.prop-badge-rejected{background:#ffe6ea;color:#e30620;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;}

@media(max-width:1100px){
  .kpi-grid{grid-template-columns:repeat(2,1fr);}
  .dash-grid,.dash-wide{grid-template-columns:1fr;}
}
@media(max-width:600px){
  .kpi-grid{grid-template-columns:1fr 1fr;}
}
</style>
@endsection

@section('content')
<div class="bw-dash">

  {{-- Header --}}
  <div class="dash-header">
    <div>
      <h1>Hi, {{ Auth::user()->name ?? 'Admin' }} 👋</h1>
      <p>Here's what's happening with BigWein today.</p>
    </div>
    <div class="date-badge">
      <i class="bi bi-calendar3"></i>
      {{ now()->subDays(6)->format('d M') }} – {{ now()->format('d M Y') }}
    </div>
  </div>

  {{-- KPI Cards --}}
  <div class="kpi-grid">
    <a href="{{ url('property') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-blue">🏠</div>
        <div class="kpi-num">{{ $list['total_properties'] }}</div>
        <div class="kpi-label">Total Properties</div>
      </div>
    </a>
    <a href="{{ url('property?propery_type=0') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-red">🛒</div>
        <div class="kpi-num">{{ $list['total_sell_property'] }}</div>
        <div class="kpi-label">For Sale</div>
      </div>
    </a>
    <a href="{{ url('property?propery_type=1') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-orange">🏘</div>
        <div class="kpi-num">{{ $list['total_rant_property'] }}</div>
        <div class="kpi-label">For Rent</div>
      </div>
    </a>
    <a href="{{ url('customer') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-green">👥</div>
        <div class="kpi-num">{{ $list['total_customer'] }}</div>
        <div class="kpi-label">Total Customers</div>
      </div>
    </a>
    <a href="{{ url('categories') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-purple">📂</div>
        <div class="kpi-num">{{ $list['total_categories'] }}</div>
        <div class="kpi-label">Categories</div>
      </div>
    </a>
    <a href="{{ url('article') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-blue">📰</div>
        <div class="kpi-num">{{ $list['total_articles'] }}</div>
        <div class="kpi-label">Articles</div>
      </div>
    </a>
    @php
      $pendingCount = \App\Models\Property::where('request_status','pending')->count();
      $ownerCount   = \App\Models\Customer::whereNotNull('owner_type')->count();
    @endphp
    <a href="{{ url('property') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-orange">⏳</div>
        <div class="kpi-num">{{ $pendingCount }}</div>
        <div class="kpi-label">Pending Approval</div>
      </div>
    </a>
    <a href="{{ url('customer') }}" style="text-decoration:none;">
      <div class="kpi-card">
        <div class="kpi-icon bw-icon-red">🏢</div>
        <div class="kpi-num">{{ $ownerCount }}</div>
        <div class="kpi-label">Property Owners</div>
      </div>
    </a>
  </div>

  {{-- Chart + Type Breakdown --}}
  <div class="dash-grid">
    <div class="dash-panel">
      <div class="panel-head">
        <h3>Property Overview</h3>
        <div class="chart-tabs">
          <button class="chart-tab active" onclick="switchChart('month',this)">Monthly</button>
          <button class="chart-tab" onclick="switchChart('week',this)">Weekly</button>
          <button class="chart-tab" onclick="switchChart('day',this)">Daily</button>
        </div>
      </div>
      <canvas id="propChart" height="100"></canvas>
    </div>

    <div class="dash-panel">
      <div class="panel-head">
        <h3>Property Types</h3>
        <span style="font-size:12px;color:#667085;">Total {{ $list['total_properties'] }}</span>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#155eef;"></span>For Sale</span>
        <b>{{ $list['total_sell_property'] }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#f97316;"></span>For Rent</span>
        <b>{{ $list['total_rant_property'] }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#16a34a;"></span>Approved</span>
        <b>{{ \App\Models\Property::where('request_status','approved')->count() }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#d97706;"></span>Pending Review</span>
        <b>{{ $pendingCount }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#e30620;"></span>Rejected</span>
        <b>{{ \App\Models\Property::where('request_status','rejected')->count() }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#7c3aed;"></span>Owner Listed</span>
        <b>{{ \App\Models\Property::where('post_type',1)->count() }}</b>
      </div>
      <div class="type-row">
        <span><span class="type-dot" style="background:#0891b2;"></span>Admin Listed</span>
        <b>{{ \App\Models\Property::where('post_type',0)->count() }}</b>
      </div>
    </div>
  </div>

  {{-- City Table + Quick Actions --}}
  <div class="dash-wide">
    <div class="dash-panel">
      <div class="panel-head">
        <h3>Recent Properties</h3>
        <a href="{{ url('property') }}" class="panel-link">View All →</a>
      </div>
      <div style="overflow-x:auto;">
        <table class="city-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Property</th>
              <th>City</th>
              <th>Added By</th>
              <th>Type</th>
              <th>Price</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($list['recent_properties'] as $prop)
            <tr>
              <td class="num">{{ $prop->id }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  @if($prop->title_image)
                    <img src="{{ url('images/'.config('global.PROPERTY_TITLE_IMG_PATH','property_title_img/').$prop->title_image) }}"
                      style="width:38px;height:34px;object-fit:cover;border-radius:8px;" onerror="this.style.display='none'"/>
                  @endif
                  <div>
                    <div style="font-weight:600;color:#111827;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      {{ $prop->title }}
                    </div>
                    <div style="font-size:11px;color:#667085;">{{ $prop->category->name ?? '—' }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $prop->city ?? '—' }}</td>
              <td>{{ $prop->post_type == 1 ? 'Owner' : 'Admin' }}</td>
              <td>
                <span class="{{ $prop->propery_type == 0 ? 'prop-badge-sell' : 'prop-badge-rent' }}">
                  {{ $prop->propery_type == 0 ? 'Sale' : 'Rent' }}
                </span>
              </td>
              <td style="font-weight:700;color:#e30620;">₹{{ number_format($prop->price) }}</td>
              <td>
                <span class="prop-badge-{{ $prop->request_status }}">{{ ucfirst($prop->request_status) }}</span>
              </td>
              <td>
                <a href="{{ url('property/'.$prop->id.'/edit') }}" style="color:#e30620;font-size:14px;"><i class="bi bi-pencil-fill"></i></a>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:24px;color:#667085;">No properties found</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="dash-panel">
      <div class="panel-head"><h3>Quick Actions</h3></div>
      <div class="quick-grid">
        <a href="{{ url('property/create') }}" class="quick-item"><span class="qi-icon">🏠</span>Add Property</a>
        <a href="{{ url('customer') }}" class="quick-item"><span class="qi-icon">👤</span>Customers</a>
        <a href="{{ url('categories') }}" class="quick-item"><span class="qi-icon">📂</span>Categories</a>
        <a href="{{ url('slider') }}" class="quick-item"><span class="qi-icon">🖼</span>Banners</a>
        <a href="{{ url('package') }}" class="quick-item"><span class="qi-icon">💎</span>Packages</a>
        <a href="{{ url('notification') }}" class="quick-item"><span class="qi-icon">🔔</span>Notify Users</a>
        <a href="{{ url('article') }}" class="quick-item"><span class="qi-icon">📰</span>Articles</a>
        <a href="{{ url('system-settings') }}" class="quick-item"><span class="qi-icon">⚙️</span>Settings</a>
      </div>
    </div>
  </div>

</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthLabels = {!! json_encode(array_map(fn($m) => date('M', mktime(0,0,0,$m,1)), range(1,12))) !!};
const weekLabels  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const dayLabels   = {!! json_encode(array_map(fn($d) => date('d M', strtotime("-".($d)." days")), array_reverse(range(0,6)))) !!};

const sellMonth = {!! json_encode(array_values($sellMonthSeries ?? array_fill(0,12,0))) !!};
const rentMonth = {!! json_encode(array_values($rentMonthSeries ?? array_fill(0,12,0))) !!};
const sellWeek  = {!! json_encode(array_values($sellWeekSeries ?? array_fill(1,7,0))) !!};
const rentWeek  = {!! json_encode(array_values($rentWeekSeries ?? array_fill(1,7,0))) !!};
const sellDay   = {!! json_encode(array_values($sellDaySeries ?? array_fill(0,7,0))) !!};
const rentDay   = {!! json_encode(array_values($rentDaySeries ?? array_fill(0,7,0))) !!};

const ctx = document.getElementById('propChart').getContext('2d');
let chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: monthLabels,
    datasets: [
      { label:'For Sale', data: sellMonth, borderColor:'#e30620', backgroundColor:'rgba(227,6,32,.08)', tension:.4, fill:true, pointBackgroundColor:'#e30620', borderWidth:2.5 },
      { label:'For Rent', data: rentMonth, borderColor:'#1570ef', backgroundColor:'rgba(21,112,239,.06)', tension:.4, fill:true, pointBackgroundColor:'#1570ef', borderWidth:2.5 }
    ]
  },
  options: {
    responsive:true,
    plugins:{ legend:{ position:'bottom', labels:{ usePointStyle:true, padding:20, font:{ size:12, family:'Inter', weight:'600' }}}},
    scales:{
      x:{ grid:{ color:'#eef0f5' }, ticks:{ font:{ size:11, family:'Inter' }}},
      y:{ grid:{ color:'#eef0f5' }, ticks:{ font:{ size:11, family:'Inter' }, stepSize:1 }, beginAtZero:true }
    }
  }
});

function switchChart(type, btn) {
  document.querySelectorAll('.chart-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if(type === 'month') {
    chart.data.labels = monthLabels;
    chart.data.datasets[0].data = sellMonth;
    chart.data.datasets[1].data = rentMonth;
  } else if(type === 'week') {
    chart.data.labels = weekLabels;
    chart.data.datasets[0].data = sellWeek;
    chart.data.datasets[1].data = rentWeek;
  } else {
    chart.data.labels = dayLabels;
    chart.data.datasets[0].data = sellDay;
    chart.data.datasets[1].data = rentDay;
  }
  chart.update();
}
</script>
@endsection
