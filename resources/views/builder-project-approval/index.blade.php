@extends('layouts.main')
@section('title','Builder Project Approvals')
@section('page-title')
<div class="page-title"><h4><i class="bi bi-buildings" style="color:#E5343A"></i> Builder Project Approvals</h4><div style="font-size:11px;color:#94a3b8">A focused queue for reviewing projects submitted by Builders and Developers.</div></div>
@endsection
@section('content')
<div class="bw-review-page">
  @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
  @include('components.admin.summary-cards', ['items' => [
    ['label'=>'Pending','count'=>$counts['pending'],'url'=>url('/builder-project-approvals?status=pending'),'active'=>$status==='pending','tone'=>'warning','icon'=>'bi-hourglass-split'],
    ['label'=>'Approved','count'=>$counts['approved'],'url'=>url('/builder-project-approvals?status=approved'),'active'=>$status==='approved','tone'=>'success','icon'=>'bi-check-circle'],
    ['label'=>'Rejected','count'=>$counts['rejected'],'url'=>url('/builder-project-approvals?status=rejected'),'active'=>$status==='rejected','tone'=>'danger','icon'=>'bi-x-circle'],
    ['label'=>'Total','count'=>array_sum($counts),'url'=>url('/builder-project-approvals?status=all'),'active'=>$status==='all','tone'=>'info','icon'=>'bi-collection'],
  ]])
  <form method="GET" class="bw-review-filter"><input type="hidden" name="status" value="{{ $status }}"><div class="bw-review-filter__search"><i class="bi bi-search"></i><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search project, city or builder"></div><a class="btn btn-outline-secondary bw-review-filter__clear" href="{{ url('/builder-project-approvals?status='.$status) }}"><i class="bi bi-x-lg"></i> Clear</a></form>
  <div class="bw-review-list">
    @forelse($rows as $p)
      <article class="bw-review-row">
        <div class="bw-review-primary"><span class="bw-review-avatar"><i class="bi bi-buildings"></i></span><div class="bw-review-primary__copy"><small>Project</small><strong>{{ $p->title }}</strong><span>{{ $p->reference_no ?: 'Project #'.$p->id }}</span></div></div>
        <div class="bw-review-meta"><small>Builder / Developer</small><strong>{{ $p->company_name ?: $p->owner_name }}</strong><span class="bw-review-eligibility {{ $p->owner_kyc_status==='approved'?'is-eligible':'is-blocked' }}"><i class="bi {{ $p->owner_kyc_status==='approved'?'bi-unlock':'bi-lock' }}"></i> KYC {{ $p->owner_kyc_status==='approved'?'Approved':'Pending' }}</span></div>
        <div class="bw-review-meta"><small>Category · Location</small><strong>{{ $p->project_segment ? ucwords(str_replace('_',' ',$p->project_segment)).' · ' : '' }}{{ $p->city ?: '—' }}</strong></div>
        <div class="bw-review-meta"><small>Submitted</small><strong>{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y') : '—' }}</strong>@include('components.admin.status-badge',['status'=>$p->request_status])</div>
        <div class="bw-review-action"><a class="btn {{ $p->request_status==='pending'?'btn-danger':'btn-outline-secondary' }}" href="{{ url('/builder-project-approvals/'.$p->id) }}"><i class="bi bi-eye"></i> {{ $p->request_status==='pending'?'Review':'View Details' }}</a></div>
      </article>
    @empty
      <div class="bw-review-empty"><i class="bi bi-check2-circle"></i><strong>No projects in this queue</strong><div class="small mt-1">Try another status or clear the search.</div></div>
    @endforelse
  </div>
  <div>{{ $rows->links() }}</div>
</div>
@endsection
