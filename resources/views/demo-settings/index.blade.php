@extends('layouts.main')
@section('title') Demo Data Settings @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-database-fill-gear me-2" style="color:#e30620;"></i>Demo Data Settings</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Demo Data</li>
      </ol></nav>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">
<div class="row justify-content-center">
<div class="col-xl-8">

  {{-- ═══ MASTER TOGGLE ═══ --}}
  <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);border-left:4px solid {{ $enabled ? '#16A34A' : '#e30620' }}!important;">
    <div class="card-body p-4">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h5 style="font-weight:800;color:#111;margin-bottom:6px;">
            <i class="bi bi-database-fill me-2" style="color:{{ $enabled ? '#16A34A' : '#e30620' }};"></i>
            Demo Data Mode
          </h5>
          <div id="mode-status">
            @if($enabled)
            <span style="color:#16A34A;font-size:13px;font-weight:600;">
              <i class="bi bi-check-circle-fill me-1"></i>
              <strong>ENABLED</strong> — Entire platform is using demo seed data
            </span>
            @else
            <span style="color:#E30620;font-size:13px;font-weight:600;">
              <i class="bi bi-slash-circle me-1"></i>
              <strong>DISABLED</strong> — Entire platform is using real/live data
            </span>
            @endif
          </div>
        </div>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" id="demoToggle" role="switch"
            {{ $enabled ? 'checked' : '' }}
            onchange="toggleDemo(this.checked)"
            style="cursor:pointer;width:56px;height:28px;">
        </div>
      </div>
    </div>
  </div>


  {{-- ═══ V5 DATA SEPARATION ═══ --}}
  <div class="card border-0 mb-4" style="border-radius:16px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;box-shadow:0 8px 26px rgba(15,23,42,.12);">
    <div class="card-body p-4">
      <div class="d-flex align-items-start gap-3">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(16,185,129,.15);display:flex;align-items:center;justify-content:center;color:#6ee7b7;font-size:19px;flex:0 0 auto;"><i class="bi bi-shield-check"></i></div>
        <div>
          <div style="font-size:14px;font-weight:800;">Admin Analytics Stay Live</div>
          <div style="font-size:12px;color:#cbd5e1;margin-top:4px;line-height:1.6;">Demo Mode is now a global data switch. When enabled, dashboard, frontend, search, approvals, notifications and analytics use only records tagged <code style="color:#fecdd3;">demo_seed</code>. When disabled, those areas exclude demo seed records and show live data only.</div>
          <div class="d-flex gap-2 flex-wrap mt-2">
            <span style="font-size:10px;font-weight:700;background:rgba(16,185,129,.14);color:#a7f3d0;padding:4px 9px;border-radius:20px;"><i class="bi bi-database-check me-1"></i> One global data mode</span>
            <span style="font-size:10px;font-weight:700;background:rgba(59,130,246,.14);color:#bfdbfe;padding:4px 9px;border-radius:20px;"><i class="bi bi-arrow-repeat me-1"></i> Admin + frontend synchronized</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══ STATS ═══ --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-3">
          <div style="font-size:32px;font-weight:900;color:#111;" id="stat-total">{{ $demoCount }}</div>
          <div style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.4px;">Demo Properties</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-3">
          <div style="font-size:32px;font-weight:900;color:#1D4ED8;">{{ $projectCount }}</div>
          <div style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.4px;">Demo Projects</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-3">
          <div style="font-size:32px;font-weight:900;color:#16A34A;">{{ $activeCount }}</div>
          <div style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.4px;">Published</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-3">
          <div style="font-size:32px;font-weight:900;color:{{ $enabled ? '#16A34A' : '#E30620' }};">
            {{ $enabled ? 'ON' : 'OFF' }}
          </div>
          <div style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.4px;">Global Platform Mode</div>
        </div>
      </div>
    </div>
  </div>


  @if(($businessCount ?? 0) > 0)
  <div class="mb-4 p-3" style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:12px;font-size:12px;color:#6b21a8;">
    <i class="bi bi-shop me-2"></i><strong>{{ $businessCount }} demo Business-for-Sale listing{{ $businessCount == 1 ? '' : 's' }}</strong> detected. These are also excluded from live admin analytics.
  </div>
  @endif

  {{-- ═══ BREAKDOWN ═══ --}}
  @if($demoCount > 0)
  <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
    <div class="card-header bg-white" style="border-radius:16px 16px 0 0;padding:16px 22px;">
      <h6 style="font-weight:800;margin:0;"><i class="bi bi-bar-chart-fill me-2" style="color:#e30620;"></i>Demo Properties by Category</h6>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0" style="font-size:13px;">
        <thead style="background:#F9FAFB;">
          <tr>
            <th class="px-4 py-3" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Category</th>
            <th class="px-4 py-3 text-center" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Total</th>
            <th class="px-4 py-3 text-center" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">For Sale</th>
            <th class="px-4 py-3 text-center" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">For Rent</th>
          </tr>
        </thead>
        <tbody>
          @foreach($breakdown as $row)
          <tr>
            <td class="px-4 py-3" style="font-weight:600;">{{ $row->cat_name ?? 'Unknown' }}</td>
            <td class="px-4 py-3 text-center"><span style="font-weight:800;font-size:15px;">{{ $row->total }}</span></td>
            <td class="px-4 py-3 text-center"><span style="background:#EFF6FF;color:#2563EB;padding:2px 10px;border-radius:6px;font-size:11px;font-weight:700;">{{ $row->for_sale }}</span></td>
            <td class="px-4 py-3 text-center"><span style="background:#FFFBEB;color:#D97706;padding:2px 10px;border-radius:6px;font-size:11px;font-weight:700;">{{ $row->for_rent }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- ═══ DEMO PROJECTS BREAKDOWN ═══ --}}
  @if($projectCount > 0)
  <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
    <div class="card-header bg-white d-flex align-items-center" style="border-radius:16px 16px 0 0;padding:16px 22px;">
      <h6 style="font-weight:800;margin:0;"><i class="bi bi-kanban-fill me-2" style="color:#1D4ED8;"></i>Demo Projects by Category</h6>
      <span class="ms-auto" style="font-size:11px;font-weight:700;background:#EFF6FF;color:#1D4ED8;padding:3px 10px;border-radius:10px;">{{ $projectCount }} total</span>
    </div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0" style="font-size:13px;">
        <thead style="background:#F9FAFB;">
          <tr>
            <th class="px-4 py-3" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Category</th>
            <th class="px-4 py-3 text-center" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Total</th>
            <th class="px-4 py-3 text-center" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($projectBreakdown as $row)
          @php $t = $row->project_type ?? ''; @endphp
          <tr>
            <td class="px-4 py-3" style="font-weight:600;">{{ $row->cat_name ?? 'Uncategorised' }}</td>
            <td class="px-4 py-3 text-center"><span style="font-weight:800;font-size:15px;">{{ $row->total }}</span></td>
            <td class="px-4 py-3 text-center">
              <span style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:6px;
                background:{{ $t==='Ready to Move'?'#F0FDF4':($t==='New Launch'?'#FFF1F3':'#FFFBEB') }};
                color:{{ $t==='Ready to Move'?'#166534':($t==='New Launch'?'#9F1239':'#92400E') }};">
                {{ $t ?: '—' }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- ═══ ACTIONS ═══ --}}
  <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
    <div class="card-header bg-white" style="border-radius:16px 16px 0 0;padding:16px 22px;">
      <h6 style="font-weight:800;margin:0;"><i class="bi bi-tools me-2" style="color:#e30620;"></i>Actions</h6>
    </div>
    <div class="card-body p-4">

      {{-- Seed --}}
      <div class="p-3 mb-3" style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div style="font-size:14px;font-weight:700;color:#15803D;"><i class="bi bi-cloud-download-fill me-2"></i>Seed Demo Data</div>
            <div style="font-size:12px;color:#16A34A;margin-top:2px;">Creates showcase properties and demo projects with sample images when demo data has not already been seeded.</div>
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
              <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:10px;background:{{ $demoCount>0?'#DCFCE7':'#F1F5F9' }};color:{{ $demoCount>0?'#166534':'#64748B' }};">
                <i class="bi bi-{{ $demoCount>0?'check-circle-fill':'dash-circle' }} me-1"></i>
                Properties: {{ $demoCount>0 ? $demoCount.' seeded' : 'not seeded' }}
              </span>
              <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:10px;background:{{ $projectCount>0?'#DCFCE7':'#F1F5F9' }};color:{{ $projectCount>0?'#166534':'#64748B' }};">
                <i class="bi bi-{{ $projectCount>0?'check-circle-fill':'dash-circle' }} me-1"></i>
                Projects: {{ $projectCount>0 ? $projectCount.' seeded' : 'not seeded' }}
              </span>
            </div>
          </div>
          @php $allSeeded = $demoCount > 0 && $projectCount > 0; @endphp
          <button class="btn btn-success" onclick="seedDemo()" {{ $allSeeded ? 'disabled' : '' }}
            style="border-radius:10px;font-weight:700;padding:10px 22px;" id="seedBtn">
            <i class="bi bi-{{ $allSeeded?'check-circle-fill':'play-fill' }} me-1"></i>
            {{ $allSeeded ? 'All Seeded' : ($demoCount>0 ? 'Seed Projects' : 'Seed Now') }}
          </button>
        </div>
      </div>

      {{-- Clear --}}
      <div class="p-3" style="background:#FFF1F3;border:1px solid #FECDD3;border-radius:12px;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <div style="font-size:14px;font-weight:700;color:#C4272D;"><i class="bi bi-trash-fill me-2"></i>Clear Demo Properties</div>
            <div style="font-size:12px;color:#E30620;margin-top:2px;">Permanently removes all {{ $demoCount }} demo properties and {{ $projectCount }} demo projects. Real listings are unaffected.</div>
          </div>
          <button class="btn btn-danger" onclick="clearDemo()" {{ $demoCount === 0 ? 'disabled' : '' }}
            style="border-radius:10px;font-weight:700;padding:10px 22px;" id="clearBtn">
            <i class="bi bi-trash-fill me-1"></i> Clear All Demo
          </button>
        </div>
      </div>

    </div>
  </div>

  {{-- Info box --}}
  <div class="mt-3 p-3" style="background:#F0F9FF;border:1px solid #BAE6FD;border-radius:12px;font-size:12px;color:#0369A1;">
    <i class="bi bi-info-circle-fill me-2"></i>
    <strong>How it works:</strong> When Demo Mode is <strong>ON</strong>, the platform shows <strong>demo_seed data only</strong>. When <strong>OFF</strong>, demo records remain stored but are excluded and the platform shows <strong>real/live data only</strong>. The switch applies consistently to dashboard KPIs, frontend listings/search, approvals and notifications.
  </div>

</div>
</div>
</section>
@endsection

@section('script')
<script>
const CSRF = '{{ csrf_token() }}';
async function apiPost(url, data) {
  const r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json' },
    body: JSON.stringify(data || {})
  });
  return r.json();
}

async function toggleDemo(enabled) {
  const res = await apiPost('{{ url("demo-settings/toggle") }}', { enabled });
  if (res.success) {
    toastr.success(res.message);
    setTimeout(() => location.reload(), 800);
  } else {
    toastr.error(res.message || 'Failed to update.');
    document.getElementById('demoToggle').checked = !enabled;
  }
}

async function seedDemo() {
  const btn = document.getElementById('seedBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Downloading images & seeding...';
  const res = await apiPost('{{ url("demo-settings/seed") }}');
  if (res.success) {
    toastr.success(res.message);
    setTimeout(() => location.reload(), 1200);
  } else {
    toastr.error(res.message);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-play-fill me-1"></i> Seed Now';
  }
}

async function clearDemo() {
  if (!confirm('Remove all demo properties? This cannot be undone.')) return;
  const btn = document.getElementById('clearBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';
  const res = await apiPost('{{ url("demo-settings/clear") }}');
  if (res.success) {
    toastr.success(res.message);
    setTimeout(() => location.reload(), 1000);
  } else {
    toastr.error(res.message);
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Clear All Demo';
  }
}
</script>
@endsection
