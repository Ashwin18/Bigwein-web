@extends('layouts.main')
@section('title') Owner Management @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-building-fill-gear me-2" style="color:#e30620;"></i>Owner Management</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li><li class="breadcrumb-item active">Owners</li></ol></nav>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">

  {{-- Stats Row --}}
  <div class="row mb-4">
    @foreach([
      ['Total Owners','bi-building-fill','#2563EB','#EFF6FF',$totalOwners],
      ['Sellers','bi-person-fill-gear','#16A34A','#F0FDF4',$totalSellers],
      ['Builders','bi-buildings-fill','#7C3AED','#F5F3FF',$totalBuilders],
      ['Pending Properties','bi-hourglass-split','#E30620','#FFF1F3',$pendingProps],
    ] as $stat)
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card border-0" style="box-shadow:0 4px 20px rgba(0,0,0,.07);border-radius:16px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <p class="text-muted mb-1" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">{{ $stat[0] }}</p>
              <h3 class="mb-0" style="font-size:32px;font-weight:800;color:#111;">{{ $stat[4] }}</h3>
            </div>
            <div style="width:52px;height:52px;background:{{ $stat[3] }};border-radius:14px;display:flex;align-items:center;justify-content:center;">
              <i class="bi {{ $stat[1] }}" style="font-size:22px;color:{{ $stat[2] }};"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body py-3">
      <div class="row align-items-center g-2">
        <div class="col-md-3">
          <select class="form-select form-select-sm" id="ownerTypeFilter">
            <option value="">All Owner Types</option>
            <option value="seller">Sellers / Owners</option>
            <option value="builder">Builders / Developers</option>
          </select>
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm" id="ownerStatusFilter">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Suspended</option>
          </select>
        </div>
        <div class="col-md-3">
          <a href="{{ url('property-approval') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-hourglass-split me-1"></i> Review Pending Properties ({{ $pendingProps }})
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-body">
      <table class="table table-striped" id="ownerTable"
        data-toggle="table"
        data-url="{{ url('owner-management/list') }}"
        data-side-pagination="server"
        data-pagination="true"
        data-search="true"
        data-page-list="[10,25,50,100]"
        data-sort-name="id"
        data-sort-order="desc"
        data-query-params="ownerQueryParams"
        data-responsive="true"
        data-show-refresh="true"
        data-show-export="true"
        data-export-options='{"fileName":"owners-<?= date("d-m-y") ?>"}'>
        <thead class="thead-dark">
          <tr>
            <th data-field="id" data-sortable="true" data-width="60">#</th>
            <th data-field="name" data-sortable="true" data-formatter="ownerNameFormatter">Owner</th>
            <th data-field="owner_type" data-formatter="ownerTypeFormatter" data-align="center">Type</th>
            <th data-field="company_name" data-align="center">Company / City</th>
            <th data-field="total_props" data-align="center" data-formatter="propCountFormatter">Properties</th>
            <th data-field="plan" data-align="center" data-formatter="planFormatter">Plan</th>
            <th data-field="created_at" data-align="center">Joined</th>
            <th data-field="isActive" data-align="center" data-formatter="ownerStatusFormatter">Status</th>
            <th data-field="id" data-align="center" data-formatter="ownerActionFormatter">Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

</section>
@endsection

@section('script')
<script>
function ownerQueryParams(p) {
  return {
    sort: p.sort, order: p.order, offset: p.offset, limit: p.limit, search: p.search,
    owner_type: $('#ownerTypeFilter').val(),
    status:     $('#ownerStatusFilter').val(),
  };
}

function ownerNameFormatter(v, row) {
  var initials = row.name ? row.name.charAt(0).toUpperCase() : 'O';
  return `<div class="d-flex align-items-center gap-2">
    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#e30620,#FF6B6B);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">${initials}</div>
    <div>
      <div style="font-weight:700;font-size:13px;">${row.name}</div>
      <div style="font-size:11px;color:#6B7280;">${row.email}</div>
      <div style="font-size:11px;color:#9CA3AF;">${row.mobile || ''}</div>
    </div>
  </div>`;
}

function ownerTypeFormatter(v) {
  return v === 'builder'
    ? '<span class="badge" style="background:#EDE9FE;color:#7C3AED;font-size:11px;padding:4px 10px;border-radius:6px;"><i class="bi bi-buildings-fill me-1"></i>Builder</span>'
    : '<span class="badge" style="background:#E0F2FE;color:#0369A1;font-size:11px;padding:4px 10px;border-radius:6px;"><i class="bi bi-person-fill me-1"></i>Seller</span>';
}

function propCountFormatter(v, row) {
  var pending = row.pending_props > 0
    ? `<span class="badge bg-warning text-dark ms-1" style="font-size:10px;">${row.pending_props} pending</span>`
    : '';
  return `<span style="font-weight:700;font-size:15px;">${v}</span>${pending}`;
}

function planFormatter(v) {
  var color = v === 'Free' ? '#6B7280' : '#16A34A';
  var bg    = v === 'Free' ? '#F3F4F6' : '#DCFCE7';
  return `<span style="background:${bg};color:${color};padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;">${v}</span>`;
}

function ownerStatusFormatter(v, row) {
  var checked = v == 1 ? 'checked' : '';
  return `<div class="form-check form-switch d-flex justify-content-center">
    <input class="form-check-input" type="checkbox" ${checked} onchange="toggleOwnerStatus(${row.id}, this.checked?1:0)" style="cursor:pointer;width:36px;height:18px;">
  </div>`;
}

function ownerActionFormatter(v, row) {
  return `<div class="d-flex gap-1 justify-content-center">
    <a href="{{ url('owner-management') }}/${v}" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye-fill"></i></a>
    <a href="{{ url('property') }}?added_by=${v}" class="btn btn-sm btn-outline-secondary" title="View Properties"><i class="bi bi-building"></i></a>
  </div>`;
}

async function toggleOwnerStatus(id, status) {
  const res = await fetch('{{ url("owner-management/toggle-status") }}', {
    method: 'POST',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({id, status})
  });
  const data = await res.json();
  if (!data.error) {
    toastr.success(data.message);
  } else {
    toastr.error(data.message);
    $('#ownerTable').bootstrapTable('refresh');
  }
}

// Refresh table on filter change
$('#ownerTypeFilter, #ownerStatusFilter').on('change', function() {
  $('#ownerTable').bootstrapTable('refresh');
});
</script>
@endsection
