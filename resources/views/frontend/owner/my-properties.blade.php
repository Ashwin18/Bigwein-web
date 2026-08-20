@extends('frontend.owner.layouts.app')
@section('title','My Properties')
@section('page-title','My Properties')
@section('page-bread','Manage your property listings')

@section('content')

<form method="GET" action="{{ url('/owner/my-properties') }}" id="filterForm">
<div class="filter-bar">
  <div class="search-wrap">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search your properties…" oninput="document.getElementById('filterForm').submit()"/>
  </div>
  <select class="filter-select" name="type" onchange="this.form.submit()">
    <option value="">All Types</option>
    <option value="0" {{ request('type')==='0'?'selected':'' }}>For Sale</option>
    <option value="1" {{ request('type')==='1'?'selected':'' }}>For Rent</option>
  </select>
  <select class="filter-select" name="status" onchange="this.form.submit()">
    <option value="">All Status</option>
    <option value="approved"  {{ request('status')==='approved' ?'selected':'' }}>Active</option>
    <option value="pending"   {{ request('status')==='pending'  ?'selected':'' }}>Pending</option>
    <option value="rejected"  {{ request('status')==='rejected' ?'selected':'' }}>Rejected</option>
  </select>
  <a href="{{ url('/owner/post-property') }}" class="btn-add-prop"><i class="fa-solid fa-plus"></i> Add New Property</a>
</div>
</form>

@if($properties->count())
<div class="props-grid">
  @foreach($properties as $prop)
  <div class="prop-card" id="prop-{{ $prop->id }}">
    <div class="pc-img">
      @if($prop->title_image)
        <img src="{{ asset('images/'.config('global.PROPERTY_TITLE_IMG_PATH','property_title_img/').$prop->title_image) }}" alt="{{ $prop->title }}"/>
      @else
        <div style="width:100%;height:100%;background:linear-gradient(135deg,#EEF2FF,#C7D2FE);display:flex;align-items:center;justify-content:center;">
          <i class="fa-solid fa-building" style="font-size:48px;color:#A5B4FC;"></i>
        </div>
      @endif
      <span class="pc-badge {{ $prop->request_status === 'approved' ? 'active' : ($prop->request_status === 'pending' ? 'pending' : ($prop->request_status === 'rejected' ? 'expired' : 'draft')) }}">
        {{ $prop->request_status === 'approved' ? 'Active' : ucfirst($prop->request_status) }}
      </span>
      <span class="pc-type">{{ $prop->propery_type == 0 ? 'For Sale' : 'For Rent' }}</span>
      <div class="pc-views"><i class="fa-solid fa-eye"></i> {{ number_format($prop->total_click ?? 0) }}</div>
    </div>
    <div class="pc-body">
      <div class="pc-name">{{ $prop->title }}</div>
      <div class="pc-loc"><i class="fa-solid fa-location-dot"></i>{{ $prop->city }}{{ $prop->state ? ', '.$prop->state : '' }}</div>
      <div class="pc-price">₹ {{ number_format($prop->price) }}</div>
    </div>
    <div class="pc-foot">
      <div style="display:flex;gap:12px;">
        <span class="pc-stat"><i class="fa-solid fa-message"></i> {{ $prop->enquiry_count ?? 0 }}</span>
        <span class="pc-stat"><i class="fa-solid fa-heart"></i> {{ $prop->saved_count ?? 0 }}</span>
        @if($prop->gallery_count ?? 0)
        <span class="pc-stat"><i class="fa-solid fa-images"></i> {{ $prop->gallery_count }}</span>
        @endif
      </div>
      <div class="pc-actions">
        <a href="{{ url('/owner/property/'.$prop->id.'/edit') }}" class="pc-action-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
        @if($prop->slug_id)
        <a href="{{ url('/property/'.$prop->slug_id) }}" target="_blank" class="pc-action-btn" title="View Live"><i class="fa-solid fa-eye"></i></a>
        @endif
        <button class="pc-action-btn del" title="Delete" onclick="confirmDelete({{ $prop->id }}, '{{ addslashes($prop->title) }}')"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>
  </div>
  @endforeach

  {{-- Add new card --}}
  <a href="{{ url('/owner/post-property') }}" class="prop-card" style="display:flex;align-items:center;justify-content:center;min-height:280px;border:2px dashed var(--border);box-shadow:none;">
    <div style="text-align:center;padding:24px;">
      <div style="width:60px;height:60px;border-radius:50%;background:var(--red-light);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
        <i class="fa-solid fa-plus" style="font-size:22px;color:var(--red);"></i>
      </div>
      <div style="font-size:15px;font-weight:700;color:var(--navy);margin-bottom:5px;">Post New Property</div>
      <div style="font-size:12px;color:var(--gray2);">Add another listing</div>
    </div>
  </a>
</div>

{{-- Pagination --}}
<div style="display:flex;justify-content:center;margin-top:28px;">
  {{ $properties->appends(request()->all())->links() }}
</div>

@else
<div style="text-align:center;padding:64px 20px;background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);">
  <i class="fa-solid fa-building" style="font-size:56px;color:var(--gray3);margin-bottom:16px;display:block;"></i>
  <h3 style="font-size:20px;font-weight:800;color:var(--navy);margin-bottom:8px;">No Properties Yet</h3>
  <p style="font-size:14px;color:var(--gray);">Start listing your properties to reach thousands of buyers</p>
  <a href="{{ url('/owner/post-property') }}" class="btn btn-red" style="margin-top:20px;"><i class="fa-solid fa-plus"></i> Post Your First Property</a>
</div>
@endif

{{-- Delete modal --}}
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:var(--r-xl);padding:32px;width:90%;max-width:420px;box-shadow:var(--shadow-lg);">
    <div style="width:56px;height:56px;border-radius:50%;background:var(--red-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
      <i class="fa-solid fa-trash" style="font-size:22px;color:var(--red);"></i>
    </div>
    <h3 style="font-size:18px;font-weight:800;text-align:center;margin-bottom:8px;">Delete Property?</h3>
    <p style="font-size:13px;color:var(--gray);text-align:center;margin-bottom:24px;" id="deleteModalMsg">This will permanently remove the listing.</p>
    <div style="display:flex;gap:12px;">
      <button onclick="closeDeleteModal()" class="btn btn-outline" style="flex:1;justify-content:center;">Cancel</button>
      <button onclick="doDelete()" class="btn btn-red" style="flex:1;justify-content:center;" id="deleteConfirmBtn">Delete</button>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
let deleteId = null;
function confirmDelete(id, title) {
    deleteId = id;
    document.getElementById('deleteModalMsg').textContent = `"${title}" will be permanently removed.`;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
}
async function doDelete() {
    if (!deleteId) return;
    const btn = document.getElementById('deleteConfirmBtn');
    btn.textContent = 'Deleting…';
    btn.disabled = true;
    const res = await owFetch('/owner/property/' + deleteId, {method:'DELETE'});
    if (res.success) {
        document.getElementById('prop-' + deleteId)?.remove();
        closeDeleteModal();
        showToast('Property deleted.', 'success');
    } else {
        showToast('Delete failed. Please try again.', 'error');
        btn.textContent = 'Delete'; btn.disabled = false;
    }
}
</script>
@endpush
