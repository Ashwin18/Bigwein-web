@extends('layouts.main')
@section('title','Business Approvals')
@section('page-title')
<div class="page-title"><h4><i class="bi bi-briefcase" style="color:#E5343A"></i> Business Approval Inbox</h4><div style="font-size:11px;color:#94A3B8">Review Business for Sale submissions without spreadsheet-style scrolling.</div></div>
@endsection
@section('content')
<div class="bw-review-page">
  @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
  @include('components.admin.summary-cards', ['items' => [
    ['label'=>'Pending','count'=>$counts['pending'],'url'=>url('/business-approvals?status=pending'),'active'=>$status==='pending','tone'=>'warning','icon'=>'bi-hourglass-split'],
    ['label'=>'Approved','count'=>$counts['approved'],'url'=>url('/business-approvals?status=approved'),'active'=>$status==='approved','tone'=>'success','icon'=>'bi-check-circle'],
    ['label'=>'Rejected','count'=>$counts['rejected'],'url'=>url('/business-approvals?status=rejected'),'active'=>$status==='rejected','tone'=>'danger','icon'=>'bi-x-circle'],
    ['label'=>'Total','count'=>array_sum($counts),'url'=>url('/business-approvals?status=all'),'active'=>$status==='all','tone'=>'info','icon'=>'bi-collection'],
  ]])
  <form method="GET" class="bw-review-filter"><input type="hidden" name="status" value="{{ $status }}"><div class="bw-review-filter__search"><i class="bi bi-search"></i><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search business, city or owner"></div><a class="btn btn-outline-secondary bw-review-filter__clear" href="{{ url('/business-approvals?status='.$status) }}"><i class="bi bi-x-lg"></i> Clear</a></form>
  <div class="bw-review-list">
    @forelse($rows as $b)
      <article class="bw-approval-card">
        <div class="bw-approval-card__left"><div class="bw-review-primary"><span class="bw-review-avatar"><i class="bi bi-shop"></i></span><div class="bw-review-primary__copy"><small>Business</small><strong>{{ $b->title }}</strong><span>{{ $b->owner_name ?: 'Owner unavailable' }}</span></div></div><div class="bw-approval-card__metadata"><span><i class="bi bi-tag"></i>{{ $b->category_name ?: 'Uncategorised' }}</span><span><i class="bi bi-geo-alt"></i>{{ $b->city ?: '—' }}</span></div></div>
        <div class="bw-approval-card__middle"><div class="bw-approval-fact"><small>Asking Price</small><strong>₹{{ number_format($b->asking_price ?? 0) }}</strong></div><div class="bw-approval-fact"><small>Submitted</small><strong>{{ $b->created_at ? \Carbon\Carbon::parse($b->created_at)->format('d M Y') : '—' }}</strong></div><div class="bw-approval-fact"><small>KYC</small><span class="bw-review-eligibility {{ $b->owner_kyc_status==='approved'?'is-eligible':'is-blocked' }}"><i class="bi {{ $b->owner_kyc_status==='approved'?'bi-unlock':'bi-lock' }}"></i> {{ $b->owner_kyc_status==='approved'?'Approved':'Pending' }}</span></div></div>
        <div class="bw-approval-card__right"><div class="bw-approval-card__badges">@include('components.admin.status-badge',['status'=>$b->request_status]) @include('components.admin.status-badge',['status'=>$b->status ? 'active':'inactive'])</div>@if($b->request_status!=='draft')<a class="btn {{ $b->request_status==='pending'?'btn-danger':'btn-outline-secondary' }}" href="{{ url('/business-approvals/'.$b->id) }}"><i class="bi bi-eye"></i> {{ $b->request_status==='pending'?'Review':'View' }}</a>@else<span class="text-muted small">Seller draft</span>@endif</div>
      </article>
    @empty
      <div class="bw-review-empty"><i class="bi bi-check2-circle"></i><strong>No business listings in this queue</strong><div class="small mt-1">Try another status or clear the search.</div></div>
    @endforelse
  </div>
  <div>{{ $rows->links() }}</div>
</div>
@endsection
