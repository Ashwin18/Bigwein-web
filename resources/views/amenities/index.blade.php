@extends('layouts.main')
@section('title') Property Amenities @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-stars me-2" style="color:#e30620;"></i>Property Amenities</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Amenities</li>
      </ol></nav>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">

  {{-- Stats --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-4">
          <div style="font-size:36px;font-weight:900;color:#111;">{{ $total }}</div>
          <div style="font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;">Total Amenities</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-4">
          <div style="font-size:36px;font-weight:900;color:#16A34A;">{{ $active }}</div>
          <div style="font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;">Active (shown to owners)</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 text-center" style="border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.06);">
        <div class="card-body p-4">
          <div style="font-size:36px;font-weight:900;color:#E5343A;">{{ $total - $active }}</div>
          <div style="font-size:12px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;">Disabled</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">

    {{-- LEFT: Add new --}}
    <div class="col-md-4">
      <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
        <div class="card-header bg-white" style="border-radius:16px 16px 0 0;padding:16px 20px;">
          <h6 style="font-weight:800;margin:0;"><i class="bi bi-plus-circle-fill me-2" style="color:#e30620;"></i>Add New Amenity</h6>
        </div>
        <div class="card-body p-4">
          <div class="mb-3">
            <label style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.4px;">Amenity Name</label>
            <input type="text" id="newAmenityName" class="form-control mt-1"
              placeholder="e.g. Rooftop Terrace" style="border-radius:10px;font-size:13px;"
              onkeydown="if(event.key==='Enter') addAmenity()"/>
            <div style="font-size:11px;color:#9CA3AF;margin-top:5px;">Press Enter or click Add</div>
          </div>
          <button class="btn btn-danger w-100" onclick="addAmenity()" style="border-radius:10px;font-weight:700;">
            <i class="bi bi-plus-lg me-1"></i> Add Amenity
          </button>

          <hr class="my-4"/>

          <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;text-transform:uppercase;letter-spacing:.4px;">Quick Add</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;">
            @foreach(['Solar Panels','EV Charging','Concierge','Valet Parking','Business Center','Spa','Sauna','Tennis Court','Basketball Court','Amphitheater'] as $quick)
            <span onclick="quickAdd('{{ $quick }}')"
              style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600;color:#374151;cursor:pointer;transition:all .15s;"
              onmouseover="this.style.borderColor='#e30620';this.style.color='#e30620';"
              onmouseout="this.style.borderColor='#E5E7EB';this.style.color='#374151';">
              + {{ $quick }}
            </span>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- RIGHT: Amenities list --}}
    <div class="col-md-8">
      <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
        <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:16px 20px;">
          <h6 style="font-weight:800;margin:0;"><i class="bi bi-list-check me-2" style="color:#e30620;"></i>All Amenities</h6>
          <span style="font-size:12px;color:#9CA3AF;">Toggle to show/hide in owner form</span>
        </div>
        <div class="card-body p-3">

          {{-- Preview --}}
          <div style="background:#F9FAFB;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
            <div style="font-size:11px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px;">
              <i class="bi bi-eye me-1"></i> Preview — How it looks to owners
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:7px;" id="previewChips">
              @foreach($amenities->where('is_active',1) as $am)
              <span style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:8px;border:1.5px solid #E2E8F0;font-size:12px;font-weight:600;color:#374151;background:#fff;">
                <i class="bi bi-check-circle-fill" style="color:#16A34A;font-size:11px;"></i>
                {{ $am->name }}
              </span>
              @endforeach
            </div>
          </div>

          {{-- Amenity rows --}}
          <div id="amenitiesList">
            @forelse($amenities as $am)
            <div class="amenity-row d-flex align-items-center gap-3 p-2 mb-1" id="am-{{ $am->id }}"
              style="background:{{ $am->is_active ? '#fff' : '#F9FAFB' }};border-radius:10px;border:1px solid #F3F4F6;transition:all .15s;">
              <i class="bi bi-grip-vertical" style="color:#D1D5DB;cursor:grab;font-size:16px;flex-shrink:0;"></i>
              <div style="flex:1;font-size:13px;font-weight:{{ $am->is_active ? '600' : '400' }};color:{{ $am->is_active ? '#111' : '#9CA3AF' }};" id="am-name-{{ $am->id }}">
                {{ $am->name }}
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                {{-- Edit --}}
                <button onclick="editAmenity({{ $am->id }},'{{ addslashes($am->name) }}')"
                  class="btn btn-sm btn-outline-secondary" style="border-radius:8px;padding:4px 9px;font-size:12px;">
                  <i class="bi bi-pencil-fill"></i>
                </button>
                {{-- Toggle --}}
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" {{ $am->is_active ? 'checked' : '' }}
                    onchange="toggleAmenity({{ $am->id }}, this.checked ? 1 : 0)"
                    style="cursor:pointer;width:36px;height:18px;">
                </div>
                {{-- Delete --}}
                <button onclick="deleteAmenity({{ $am->id }})"
                  class="btn btn-sm btn-outline-danger" style="border-radius:8px;padding:4px 9px;font-size:12px;">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted">No amenities yet. Add one from the left panel.</div>
            @endforelse
          </div>

        </div>
      </div>
    </div>
  </div>

</section>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header border-0"><h5 class="modal-title" style="font-weight:800;">Edit Amenity</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="editAmenityId"/>
        <label style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">Amenity Name</label>
        <input type="text" id="editAmenityName" class="form-control mt-1" style="border-radius:10px;font-size:13px;"/>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="saveEdit()" style="border-radius:10px;font-weight:700;">Save Changes</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
const CSRF = '{{ csrf_token() }}';
async function apiCall(url, data) {
  const r = await fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
    body: JSON.stringify(data)
  });
  return r.json();
}

async function addAmenity() {
  const name = document.getElementById('newAmenityName').value.trim();
  if (!name) { toastr.warning('Please enter an amenity name.'); return; }
  const res = await apiCall('{{ url("amenities/store") }}', {name});
  if (!res.error) { toastr.success(res.message); setTimeout(()=>location.reload(), 600); }
  else toastr.error(res.message);
}

function quickAdd(name) {
  document.getElementById('newAmenityName').value = name;
  addAmenity();
}

async function toggleAmenity(id, status) {
  const res = await apiCall('{{ url("amenities/toggle") }}', {id, status});
  if (!res.error) {
    toastr.success(res.message);
    const row = document.getElementById('am-'+id);
    if (row) {
      row.style.background = status ? '#fff' : '#F9FAFB';
      const nameEl = document.getElementById('am-name-'+id);
      if (nameEl) { nameEl.style.fontWeight = status ? '600' : '400'; nameEl.style.color = status ? '#111' : '#9CA3AF'; }
    }
    setTimeout(()=>location.reload(), 500);
  } else toastr.error(res.message);
}

function editAmenity(id, name) {
  document.getElementById('editAmenityId').value = id;
  document.getElementById('editAmenityName').value = name;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

async function saveEdit() {
  const id   = document.getElementById('editAmenityId').value;
  const name = document.getElementById('editAmenityName').value.trim();
  if (!name) { toastr.warning('Name cannot be empty.'); return; }
  const res = await apiCall('{{ url("amenities") }}/'+id+'/update', {name});
  if (!res.error) {
    toastr.success(res.message);
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    setTimeout(()=>location.reload(), 600);
  } else toastr.error(res.message);
}

async function deleteAmenity(id) {
  if (!confirm('Delete this amenity?')) return;
  const res = await apiCall('{{ url("amenities") }}/'+id+'/delete', {});
  if (!res.error) { toastr.success(res.message); document.getElementById('am-'+id)?.remove(); }
  else toastr.error(res.message);
}
</script>
@endsection
