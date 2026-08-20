@extends('layouts.main')
@section('title','Builder Verification')
@section('page-title')<div class="page-title"><h4><i class="bi bi-building-check" style="color:#E5343A"></i> Builder / Developer Verification</h4><div style="font-size:11px;color:#94A3B8">Verify company documents before Builders can submit projects.</div></div>@endsection

@section('content')
<div style="padding:16px 20px 40px">
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
    @foreach(['submitted'=>'Pending','approved'=>'Approved','changes_requested'=>'Changes Requested','rejected'=>'Rejected','all'=>'All'] as $key=>$label)
      <a class="btn btn-sm {{ $status===$key?'btn-danger':'btn-light' }}" href="{{ url('/builder-verification-admin?status='.$key) }}">{{ $label }} @if(isset($counts[$key]))({{ $counts[$key] }})@endif</a>
    @endforeach
  </div>

  <form method="GET" style="display:flex;gap:8px;margin-bottom:14px"><input type="hidden" name="status" value="{{ $status }}"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search company, contact person, email or mobile"><button class="btn btn-dark">Search</button></form>

  <div style="display:grid;gap:12px">
  @forelse($rows as $b)
    <div class="card p-3">
      <div class="row g-3">
        <div class="col-lg-8">
          <div style="display:flex;justify-content:space-between;gap:10px"><div><h5 style="margin:0">{{ $b->company_name }}</h5><small class="text-muted">{{ $b->owner_name }} · {{ $b->email }} · {{ $b->mobile }}</small></div><span class="badge bg-light text-dark">{{ ucwords(str_replace('_',' ',$b->status)) }}</span></div>
          <hr>
          <div class="row g-2 small">
            <div class="col-md-4"><strong>Company Type</strong><br>{{ ucwords(str_replace('_',' ',$b->company_type)) }}</div>
            <div class="col-md-4"><strong>PAN</strong><br>{{ $b->pan_number }}</div>
            <div class="col-md-4"><strong>GST</strong><br>{{ $b->gst_number ?: '—' }}</div>
            <div class="col-md-4"><strong>CIN / LLPIN</strong><br>{{ $b->cin_llpin ?: '—' }}</div>
            <div class="col-md-4"><strong>RERA Promoter</strong><br>{{ $b->rera_promoter_number ?: '—' }}</div>
            <div class="col-md-4"><strong>Personal KYC</strong><br>{{ ucfirst($b->personal_kyc_status ?: 'pending') }}</div>
          </div>
          <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px">
            @foreach(['pan_document'=>'PAN','gst_certificate'=>'GST','registration_certificate'=>'Registration','rera_certificate'=>'RERA','authorised_person_aadhaar'=>'Authorised Aadhaar'] as $field=>$label)
              @if($b->{$field})<a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ asset('images/builder_kyc/'.$b->customer_id.'/'.$b->{$field}) }}">{{ $label }}</a>@endif
            @endforeach
          </div>
        </div>
        <div class="col-lg-4">
          @if($b->status==='submitted')
          <form method="POST" action="{{ url('/builder-verification-admin/'.$b->id.'/status') }}">@csrf
            <textarea class="form-control mb-2" name="remarks" placeholder="Remarks required for changes/rejection"></textarea>
            <button class="btn btn-success w-100 mb-2" name="status" value="approved">Approve Company</button>
            <button class="btn btn-warning w-100 mb-2" name="status" value="changes_requested">Request Changes</button>
            <button class="btn btn-danger w-100" name="status" value="rejected">Reject</button>
          </form>
          @else
            @if($b->admin_remarks)<div class="alert alert-light small">{{ $b->admin_remarks }}</div>@endif
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="card p-5 text-center text-muted">No Builder / Developer verification records found.</div>
  @endforelse
  </div>
  <div class="mt-3">{{ $rows->links() }}</div>
</div>
@endsection