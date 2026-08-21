@extends('layouts.main')
@php $details=$business->category_details ? (json_decode($business->category_details,true) ?: []) : []; @endphp
@section('title','Review Business')
@section('page-title')<div class="page-title"><h4>Review Business #{{ $business->id }}</h4><div style="font-size:11px;color:#94a3b8">{{ $business->reference_no }}</div></div>@endsection

@section('content')
<div style="padding:16px 20px 40px;max-width:1120px">
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card p-3 mb-3">
      <div style="display:flex;justify-content:space-between;gap:10px;align-items:start"><div><h4>{{ $business->title }}</h4><div class="text-muted">{{ $business->category_name }} · {{ $business->city }}, {{ $business->state }}</div></div><span class="badge bg-light text-dark">{{ ucwords(str_replace('_',' ',$business->request_status)) }}</span></div>
      <hr><p>{{ $business->description }}</p>
      <div class="row g-2">
        <div class="col-md-4"><small>Asking Price</small><strong class="d-block">₹{{ number_format($business->asking_price) }}</strong></div>
        <div class="col-md-4"><small>Monthly Revenue</small><strong class="d-block">₹{{ number_format($business->monthly_revenue) }}</strong></div>
        <div class="col-md-4"><small>Monthly Profit</small><strong class="d-block">₹{{ number_format($business->monthly_profit) }}</strong></div>
      </div>
    </div>

    @if(count($details))
    <div class="card p-3 mb-3"><h5>Category-Specific Details</h5><div class="row g-2">@foreach($details as $key=>$value)<div class="col-md-6"><div style="background:#f8fafc;border-radius:9px;padding:10px"><small style="color:#94a3b8">{{ ucwords(str_replace('_',' ',$key)) }}</small><strong class="d-block">{{ $value }}</strong></div></div>@endforeach</div></div>
    @endif

    <div class="card p-3 mb-3"><h5>Gallery</h5><div style="display:flex;flex-wrap:wrap;gap:8px">@if($business->cover_image)<img src="{{ asset('images/businesses/'.$business->id.'/'.$business->cover_image) }}" style="width:150px;height:105px;object-fit:cover;border-radius:9px">@endif @foreach($images as $img)<img src="{{ asset('images/businesses/'.$business->id.'/'.$img->image) }}" style="width:150px;height:105px;object-fit:cover;border-radius:9px">@endforeach</div></div>

    <div class="card p-3"><h5>Admin-only Documents</h5>@forelse($documents as $d)<a target="_blank" href="{{ asset('images/businesses/'.$business->id.'/'.$d->file_name) }}">{{ ucwords(str_replace('_',' ',$d->document_type)) }}</a><br>@empty<span class="text-muted">No documents uploaded.</span>@endforelse</div>
  </div>

  <div class="col-lg-4">
    <div class="card p-3" style="position:sticky;top:90px">
      <h5>Seller</h5><p>{{ $business->owner_name }}<br>{{ $business->owner_mobile }}<br>{{ $business->owner_email }}</p><div class="mb-3">@include('components.admin.status-badge',['status'=>$business->owner_kyc_status,'prefix'=>'KYC '])</div>
      @if($business->admin_remarks)<div class="alert alert-warning py-2"><small><strong>Previous remarks:</strong><br>{{ $business->admin_remarks }}</small></div>@endif

      @if($business->request_status==='pending')
      <form method="POST" action="{{ url('/business-approvals/'.$business->id.'/status') }}">@csrf
        <label style="font-size:11px;font-weight:700">Review remarks</label>
        <textarea class="form-control mb-2" name="remarks" placeholder="Required for Request Changes / Reject"></textarea>
        <button class="btn btn-success w-100 mb-2" name="status" value="approved" onclick="return confirm('Approve and publish this business?')">✓ Approve & Publish</button>
        <button class="btn btn-warning w-100 mb-2" name="status" value="changes_requested">✎ Request Changes</button>
        <button class="btn btn-danger w-100" name="status" value="rejected" onclick="return confirm('Reject this business listing?')">✕ Reject</button>
      </form>
      @elseif($business->request_status==='approved')
        <div class="alert alert-success mb-0">Business is live and seller editing is locked.</div>
      @elseif($business->request_status==='changes_requested')
        <div class="alert alert-warning mb-0">Waiting for seller to update and resubmit.</div>
      @elseif($business->request_status==='rejected')
        <div class="alert alert-danger mb-0">Rejected. Seller may edit and resubmit.</div>
      @endif
    </div>
  </div>
</div>
</div>
@endsection
