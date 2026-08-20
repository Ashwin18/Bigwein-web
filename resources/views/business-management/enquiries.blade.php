@extends('layouts.main')
@section('title','Business Enquiries')

@section('css')
<style>
.bwe-wrap{padding:16px 20px 40px;display:flex;flex-direction:column;gap:14px}
.bwe-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.bwe-stat{background:#fff;border:1px solid #edf1f6;border-radius:13px;padding:14px;text-decoration:none!important;color:#0f172a}
.bwe-stat.active{border-color:#ef3f45}.bwe-stat strong{display:block;font-size:22px}.bwe-stat span{font-size:10px;text-transform:uppercase;color:#64748b;font-weight:800}
.bwe-toolbar{background:#fff;border:1px solid #edf1f6;border-radius:13px;padding:12px;display:flex;gap:8px}
.bwe-toolbar input{flex:1;border:1px solid #dce3eb;border-radius:9px;padding:9px 11px}.bwe-toolbar button{border:0;border-radius:9px;background:#ef3f45;color:#fff;padding:0 16px;font-weight:800}
.bwe-card{background:#fff;border:1px solid #e7ebf1;border-radius:14px;padding:15px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:15px}
.bwe-title{font-weight:800;color:#0f172a}.bwe-meta{font-size:11px;color:#64748b;margin-top:4px}.bwe-msg{font-size:12px;color:#475569;margin-top:8px;background:#f8fafc;border-radius:9px;padding:9px}
.bwe-action{display:flex;flex-direction:column;gap:7px;min-width:150px}.bwe-action select,.bwe-action button{height:36px;border-radius:8px;font-size:11px}.bwe-action select{border:1px solid #dce3eb;padding:0 8px}.bwe-action button{border:0;background:#172033;color:#fff;font-weight:800}
@media(max-width:760px){.bwe-stats{grid-template-columns:repeat(2,1fr)}.bwe-card{grid-template-columns:1fr}.bwe-action{min-width:0}}
</style>
@endsection

@section('page-title')
<div class="page-title">
  <h4><i class="bi bi-chat-square-text" style="color:#E5343A"></i> Business Enquiries</h4>
  <div style="font-size:11px;color:#94A3B8">Track buyer interest across approved business listings.</div>
</div>
@endsection

@section('content')
<div class="bwe-wrap">
  <div class="bwe-stats">
    @foreach(['new'=>'New','contacted'=>'Contacted','closed'=>'Closed','all'=>'All'] as $key=>$label)
      <a class="bwe-stat {{ $status===$key?'active':'' }}" href="{{ url('/business-enquiries-admin?status='.$key) }}">
        <strong>{{ $counts[$key] ?? 0 }}</strong><span>{{ $label }}</span>
      </a>
    @endforeach
  </div>

  <form class="bwe-toolbar" method="GET">
    <input type="hidden" name="status" value="{{ $status }}">
    <input name="search" value="{{ request('search') }}" placeholder="Search buyer, mobile, email, business or seller...">
    <button>Search</button>
  </form>

  @forelse($rows as $e)
  <div class="bwe-card">
    <div>
      <div class="bwe-title">{{ $e->business_title }}</div>
      <div class="bwe-meta">{{ $e->reference_no }} · {{ $e->city }}, {{ $e->state }} · Seller: {{ $e->seller_name ?: '—' }}</div>
      <div class="bwe-meta" style="margin-top:8px"><strong>{{ $e->name }}</strong> · {{ $e->mobile }} @if($e->email) · {{ $e->email }} @endif</div>
      <div class="bwe-meta">Buyer Type: {{ $e->buyer_type ?: 'Not specified' }} · Budget: {{ $e->investment_budget ?: 'Not specified' }} · {{ \Carbon\Carbon::parse($e->created_at)->format('d M Y, h:i A') }}</div>
      @if($e->message)<div class="bwe-msg">{{ $e->message }}</div>@endif
    </div>
    <form class="bwe-action" method="POST" action="{{ url('/business-enquiries-admin/'.$e->id.'/status') }}">@csrf
      <select name="status">
        <option value="new" {{ $e->status==='new'?'selected':'' }}>New</option>
        <option value="contacted" {{ $e->status==='contacted'?'selected':'' }}>Contacted</option>
        <option value="closed" {{ $e->status==='closed'?'selected':'' }}>Closed</option>
      </select>
      <button>Update Status</button>
    </form>
  </div>
  @empty
  <div style="background:#fff;border:1px dashed #dce3eb;border-radius:14px;padding:50px;text-align:center;color:#94a3b8">No business enquiries found.</div>
  @endforelse

  {{ $rows->links() }}
</div>
@endsection