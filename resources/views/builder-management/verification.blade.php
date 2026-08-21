@extends('layouts.main')
@section('title','Builder Verification')
@section('page-title')<div class="page-title"><h4><i class="bi bi-building-check" style="color:#E5343A"></i> Builder / Developer Verification</h4><div style="font-size:11px;color:#94A3B8">Verify company documents before project listing access is enabled.</div></div>@endsection
@section('content')
<div class="bw-review-page">
  @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="alert alert-danger mb-0">{{ session('error') }}</div>@endif
  @include('components.admin.summary-cards', ['items' => [
    ['label'=>'Pending','count'=>$counts['submitted'],'url'=>url('/builder-verification-admin?status=submitted'),'active'=>$status==='submitted','tone'=>'warning','icon'=>'bi-hourglass-split'],
    ['label'=>'Approved','count'=>$counts['approved'],'url'=>url('/builder-verification-admin?status=approved'),'active'=>$status==='approved','tone'=>'success','icon'=>'bi-check-circle'],
    ['label'=>'Rejected','count'=>$counts['rejected'],'url'=>url('/builder-verification-admin?status=rejected'),'active'=>$status==='rejected','tone'=>'danger','icon'=>'bi-x-circle'],
    ['label'=>'Total','count'=>array_sum($counts),'url'=>url('/builder-verification-admin?status=all'),'active'=>$status==='all','tone'=>'info','icon'=>'bi-collection'],
  ]])
  <form method="GET" class="bw-review-filter"><input type="hidden" name="status" value="{{ $status }}"><div class="bw-review-filter__search"><i class="bi bi-search"></i><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search company, contact person, email or mobile"></div><a class="btn btn-outline-secondary" href="{{ url('/builder-verification-admin?status='.$status) }}"><i class="bi bi-x-lg"></i> Clear</a></form>
  <div class="bw-review-list">
  @forelse($rows as $b)
    <article class="bw-kyc-card">
      <div class="bw-review-row" style="border:0;box-shadow:none">
        <div class="bw-review-primary"><span class="bw-review-avatar"><i class="bi bi-building"></i></span><div class="bw-review-primary__copy"><small>Company</small><strong>{{ $b->company_name }}</strong><span>{{ $b->owner_name }} · {{ $b->mobile }}</span></div></div>
        <div class="bw-review-meta"><small>User Type</small><strong>Builder / Developer</strong><span class="bw-review-eligibility {{ $b->personal_kyc_status==='approved'?'is-eligible':'is-blocked' }}"><i class="bi {{ $b->personal_kyc_status==='approved'?'bi-unlock':'bi-lock' }}"></i> {{ $b->personal_kyc_status==='approved'?'Personal KYC approved':'Personal KYC pending' }}</span></div>
        <div class="bw-review-meta"><small>Company Type</small><strong>{{ ucwords(str_replace('_',' ',$b->company_type)) }}</strong></div>
        <div class="bw-review-meta"><small>Submitted</small><strong>{{ $b->submitted_at ? \Carbon\Carbon::parse($b->submitted_at)->format('d M Y') : '—' }}</strong>@include('components.admin.status-badge',['status'=>$b->status])</div>
        <div class="bw-review-action"><button class="btn {{ $b->status==='submitted'?'btn-danger':'btn-outline-secondary' }}" type="button" data-bs-toggle="collapse" data-bs-target="#builder-review-{{ $b->id }}"><i class="bi bi-eye"></i> {{ $b->status==='submitted'?'Review':'View Details' }}</button></div>
      </div>
      <div class="collapse" id="builder-review-{{ $b->id }}"><div class="p-3 border-top"><div class="row g-3"><div class="col-lg-8">
        <div class="bw-review-modal__grid"><div class="bw-review-detail"><small>Email</small><strong>{{ $b->email }}</strong></div><div class="bw-review-detail"><small>PAN</small><strong>{{ $b->pan_number }}</strong></div><div class="bw-review-detail"><small>GST</small><strong>{{ $b->gst_number ?: '—' }}</strong></div><div class="bw-review-detail"><small>CIN / LLPIN</small><strong>{{ $b->cin_llpin ?: '—' }}</strong></div><div class="bw-review-detail"><small>RERA Promoter</small><strong>{{ $b->rera_promoter_number ?: '—' }}</strong></div><div class="bw-review-detail"><small>Listing Eligibility</small><strong>{{ $b->status==='approved' && $b->personal_kyc_status==='approved' ? 'Eligible to List' : 'Listing Blocked' }}</strong></div></div>
        <div class="d-flex flex-wrap gap-2 mt-3">@foreach(['pan_document'=>'PAN','gst_certificate'=>'GST','registration_certificate'=>'Registration','rera_certificate'=>'RERA','authorised_person_aadhaar'=>'Authorised Aadhaar'] as $field=>$label)@if($b->{$field})<a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ asset('images/builder_kyc/'.$b->customer_id.'/'.$b->{$field}) }}"><i class="bi bi-file-earmark"></i> {{ $label }}</a>@endif @endforeach</div>
      </div><div class="col-lg-4">@if($b->status==='submitted')<form method="POST" action="{{ url('/builder-verification-admin/'.$b->id.'/status') }}">@csrf<textarea class="form-control mb-2" name="remarks" placeholder="Required for changes or rejection"></textarea><button class="btn btn-success w-100 mb-2" name="status" value="approved">Approve Company</button><button class="btn btn-warning w-100 mb-2" name="status" value="changes_requested">Request Changes</button><button class="btn btn-danger w-100" name="status" value="rejected">Reject</button></form>@elseif($b->admin_remarks)<div class="alert alert-light small mb-0"><strong>Admin remarks</strong><br>{{ $b->admin_remarks }}</div>@endif</div></div></div></div>
    </article>
  @empty<div class="bw-review-empty"><i class="bi bi-building-check"></i><strong>No Builder / Developer verification records found.</strong></div>@endforelse
  </div><div>{{ $rows->links() }}</div>
</div>
@endsection
