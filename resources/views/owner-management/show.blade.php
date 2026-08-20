@extends('layouts.main')
@section('title') Owner Detail @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-person-badge-fill me-2" style="color:#e30620;"></i>Owner Profile</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ url('owner-management') }}">Owners</a></li>
        <li class="breadcrumb-item active">{{ $owner->name }}</li>
      </ol></nav>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">
<div class="row">

  {{-- LEFT: Owner Profile --}}
  <div class="col-xl-3 col-md-4 mb-4">
    <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
      <div class="card-body text-center p-4">
        <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#e30620,#FF6B6B);display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:900;color:#fff;margin:0 auto 14px;">
          {{ strtoupper(substr($owner->name,0,1)) }}
        </div>
        <h5 style="font-weight:800;font-size:16px;margin-bottom:4px;">{{ $owner->name }}</h5>
        <p style="font-size:12px;color:#6B7280;margin-bottom:8px;">{{ $owner->email }}</p>
        <span style="background:{{ $owner->owner_type==='builder'?'#EDE9FE':'#E0F2FE' }};color:{{ $owner->owner_type==='builder'?'#7C3AED':'#0369A1' }};padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;">
          <i class="bi bi-{{ $owner->owner_type==='builder'?'buildings-fill':'person-fill' }} me-1"></i>
          {{ $owner->owner_type === 'builder' ? 'Builder / Developer' : 'Seller / Owner' }}
        </span>
        @if($owner->company_name)
        <div class="mt-3 p-2" style="background:#F9FAFB;border-radius:8px;font-size:13px;font-weight:600;color:#111;">
          <i class="bi bi-building me-2" style="color:#e30620;"></i>{{ $owner->company_name }}
        </div>
        @endif
        <hr/>
        <div class="text-start">
          @foreach([['envelope','Email',$owner->email],['telephone','Mobile',$owner->getRawOriginal('mobile')],['geo-alt','Location',trim(($owner->city??'').' '.($owner->state??''))],['calendar','Joined',$owner->created_at?$owner->created_at->format('d M Y'):'—']] as $info)
          <div class="d-flex align-items-start gap-2 mb-2">
            <i class="bi bi-{{ $info[0] }} mt-1" style="color:#e30620;font-size:13px;flex-shrink:0;"></i>
            <div><div style="font-size:10px;color:#9CA3AF;font-weight:600;text-transform:uppercase;">{{ $info[1] }}</div><div style="font-size:13px;font-weight:500;">{{ $info[2] ?: '—' }}</div></div>
          </div>
          @endforeach
        </div>
        <hr/>
        {{-- Status toggle --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span style="font-size:13px;font-weight:600;">Account Status</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="ownerStatusToggle" {{ $owner->isActive?'checked':'' }}
              onchange="toggleStatus({{ $owner->id }}, this.checked?1:0)" style="cursor:pointer;">
          </div>
        </div>
        {{-- Assign Plan --}}
        <div class="mb-2">
          <label style="font-size:11px;font-weight:700;color:#6B7280;display:block;margin-bottom:5px;text-transform:uppercase;">Assign Subscription Plan</label>
          <select class="form-select form-select-sm mb-2" id="planSelect">
            <option value="">Select Plan</option>
            @foreach($packages as $pkg)
            <option value="{{ $pkg->id }}">{{ $pkg->name }} — ₹{{ number_format($pkg->price) }}</option>
            @endforeach
          </select>
          <button class="btn btn-sm btn-danger w-100" onclick="assignPlan({{ $owner->id }})">
            <i class="bi bi-award-fill me-1"></i> Assign Plan
          </button>
        </div>
      </div>
    </div>

    {{-- Active Plan Card --}}
    <div class="card border-0 mt-3" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);background:{{ $activePlan?'linear-gradient(135deg,#16A34A,#15803D)':'#F9FAFB' }};">
      <div class="card-body p-3">
        @if($activePlan)
        <div style="color:#fff;">
          <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;font-weight:600;">Active Plan</div>
          <div style="font-size:18px;font-weight:800;margin-bottom:2px;"><i class="bi bi-award-fill me-2"></i>{{ $activePlan->plan_name }}</div>
          <div style="font-size:12px;opacity:.8;">Expires: {{ $activePlan->end_date ? date('d M Y',strtotime($activePlan->end_date)) : 'No expiry' }}</div>
          <div style="font-size:12px;opacity:.8;">Price: ₹{{ number_format($activePlan->price) }}</div>
        </div>
        @else
        <div style="text-align:center;color:#9CA3AF;padding:6px 0;">
          <i class="bi bi-award" style="font-size:24px;display:block;margin-bottom:6px;"></i>
          <div style="font-size:13px;font-weight:600;">Free Plan</div>
          <div style="font-size:11px;">No active subscription</div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- RIGHT: Stats + Properties + Enquiries --}}
  <div class="col-xl-9 col-md-8">

    {{-- Stats --}}
    <div class="row g-3 mb-4">
      @foreach([
        ['Total Listed',$stats['total'],'bi-building-fill','#2563EB','#EFF6FF'],
        ['Approved',$stats['approved'],'bi-check-circle-fill','#16A34A','#F0FDF4'],
        ['Pending',$stats['pending'],'bi-hourglass-split','#D97706','#FFFBEB'],
        ['Rejected',$stats['rejected'],'bi-x-circle-fill','#E30620','#FFF1F3'],
        ['Total Views',$stats['views'],'bi-eye-fill','#7C3AED','#F5F3FF'],
        ['Enquiries',$stats['enquiries'],'bi-chat-dots-fill','#0891B2','#F0F9FF'],
      ] as $s)
      <div class="col-xl-2 col-4">
        <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
          <div class="card-body p-3">
            <div style="width:40px;height:40px;background:{{ $s[4] }};border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
              <i class="bi {{ $s[2] }}" style="font-size:18px;color:{{ $s[3] }};"></i>
            </div>
            <div style="font-size:22px;font-weight:800;color:#111;line-height:1;">{{ number_format($s[1]) }}</div>
            <div style="font-size:11px;color:#9CA3AF;margin-top:2px;">{{ $s[0] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Properties --}}
    <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
      <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;">
        <h6 class="mb-0" style="font-weight:700;"><i class="bi bi-building me-2" style="color:#e30620;"></i>Property Listings</h6>
        <a href="{{ url('property-approval?owner_id='.$owner->id) }}" class="btn btn-sm btn-outline-danger">
          @if($stats['pending'] > 0) <span class="badge bg-danger me-1">{{ $stats['pending'] }}</span> @endif
          Review Pending
        </a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:13px;">
            <thead style="background:#F9FAFB;">
              <tr>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Property</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Category</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Type</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Price</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Views</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Status</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($properties as $prop)
              <tr>
                <td class="px-3 py-2">
                  <div style="font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $prop->title }}</div>
                  <div style="font-size:11px;color:#9CA3AF;">{{ $prop->city }}</div>
                </td>
                <td class="px-3 py-2">{{ $prop->category_name ?? '—' }}</td>
                <td class="px-3 py-2">
                  <span style="background:{{ $prop->propery_type==0?'#EFF6FF':'#FFFBEB' }};color:{{ $prop->propery_type==0?'#2563EB':'#D97706' }};padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;">
                    {{ $prop->propery_type == 0 ? 'Sale' : 'Rent' }}
                  </span>
                </td>
                <td class="px-3 py-2" style="font-weight:700;color:#e30620;">₹{{ number_format($prop->price) }}</td>
                <td class="px-3 py-2">{{ number_format($prop->total_click ?? 0) }}</td>
                <td class="px-3 py-2">
                  @php $sc=['approved'=>'success','pending'=>'warning','rejected'=>'danger']; @endphp
                  <span class="badge bg-{{ $sc[$prop->request_status]??'secondary' }}" style="font-size:11px;">{{ ucfirst($prop->request_status) }}</span>
                </td>
                <td class="px-3 py-2">
                  @if($prop->request_status === 'pending')
                  <a href="{{ url('property-approval/'.$prop->id.'/detail') }}" class="btn btn-sm btn-danger" style="font-size:11px;">Review</a>
                  @else
                  <a href="{{ url('property/'.$prop->id.'/edit') }}" class="btn btn-sm btn-outline-secondary" style="font-size:11px;">Edit</a>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="7" class="text-center py-4 text-muted">No properties listed yet</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Recent Enquiries --}}
    <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
      <div class="card-header bg-white" style="border-radius:16px 16px 0 0;">
        <h6 class="mb-0" style="font-weight:700;"><i class="bi bi-chat-dots me-2" style="color:#e30620;"></i>Recent Enquiries</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:13px;">
            <thead style="background:#F9FAFB;">
              <tr>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Buyer</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Property</th>
                <th class="px-3 py-2" style="font-size:11px;font-weight:700;text-transform:uppercase;color:#6B7280;">Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($enquiries as $enq)
              <tr>
                <td class="px-3 py-2">
                  <div style="font-weight:600;">{{ $enq->buyer_name }}</div>
                  <div style="font-size:11px;color:#9CA3AF;">{{ $enq->buyer_email }}</div>
                </td>
                <td class="px-3 py-2" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $enq->property_title }}</td>
                <td class="px-3 py-2" style="color:#9CA3AF;">{{ \Carbon\Carbon::parse($enq->created_at)->format('d M Y') }}</td>
              </tr>
              @empty
              <tr><td colspan="3" class="text-center py-4 text-muted">No enquiries yet</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
</section>
@endsection

@section('script')
<script>
async function toggleStatus(id, status) {
  const res = await fetch('{{ url("owner-management/toggle-status") }}', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({id, status})
  });
  const data = await res.json();
  toastr[data.error?'error':'success'](data.message);
}

async function assignPlan(ownerId) {
  const pkg = document.getElementById('planSelect').value;
  if (!pkg) { toastr.warning('Please select a plan first.'); return; }
  const res = await fetch('{{ url("owner-management/assign-plan") }}', {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({owner_id: ownerId, package_id: pkg})
  });
  const data = await res.json();
  if (!data.error) { toastr.success(data.message); setTimeout(()=>location.reload(), 1500); }
  else toastr.error(data.message);
}
</script>
@endsection
