@extends('layouts.main')
@section('title','Business Approvals')
@section('page-title')<div class="page-title"><h4><i class="bi bi-briefcase" style="color:#E5343A"></i> Business Approval Inbox</h4><div style="font-size:11px;color:#94A3B8">Review submitted Business for Sale listings separately from Property.</div></div>@endsection

@section('content')
<div style="padding:16px 20px 40px">
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
    @foreach(['pending'=>'Pending','approved'=>'Approved','changes_requested'=>'Changes Requested','rejected'=>'Rejected','draft'=>'Drafts','all'=>'All'] as $key=>$label)
      <a href="{{ url('/business-approvals?status='.$key) }}" class="btn btn-sm {{ $status===$key?'btn-danger':'btn-light' }}">{{ $label }} @if(isset($counts[$key]))({{ $counts[$key] }})@endif</a>
    @endforeach
  </div>
  <form method="GET" style="display:flex;gap:8px;margin-bottom:14px"><input type="hidden" name="status" value="{{ $status }}"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search business, city or owner"><button class="btn btn-dark">Search</button></form>

  <div style="display:grid;gap:10px">
  @forelse($rows as $b)
    <div style="background:#fff;border:1px solid #e7ebf1;border-radius:14px;padding:15px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:15px;align-items:center">
      <div><strong>{{ $b->title }}</strong><div style="font-size:11px;color:#64748b;margin-top:5px">{{ $b->category_name }} · {{ $b->city }}, {{ $b->state }} · Owner: {{ $b->owner_name }} · ₹{{ number_format($b->asking_price) }}</div><div style="font-size:10px;margin-top:5px;color:#94a3b8">Status: {{ ucwords(str_replace('_',' ',$b->request_status)) }}</div></div>
      @if($b->request_status!=='draft')<a class="btn btn-sm btn-danger" href="{{ url('/business-approvals/'.$b->id) }}">Review</a>@else<span style="font-size:10px;color:#94a3b8">Seller Draft</span>@endif
    </div>
  @empty
    <div class="text-center p-5 bg-white rounded">No business listings in this queue.</div>
  @endforelse
  </div>
  <div class="mt-3">{{ $rows->links() }}</div>
</div>
@endsection