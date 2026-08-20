@extends('frontend.owner.layouts.app')
@section('title','My Businesses')
@section('page-title','My Businesses')
@section('page-bread','Business Marketplace')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;gap:12px;flex-wrap:wrap">
  <div><strong>Business Listings</strong><div style="font-size:11px;color:#94a3b8">Draft, review, live and change-requested businesses.</div></div>
  <a href="{{ url('/owner/list-business') }}" class="post-btn"><i class="fa-solid fa-plus"></i> List Business</a>
</div>

@if(session('success'))<div style="background:#ecfdf5;color:#047857;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:12px">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;color:#b91c1c;padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:12px">{{ session('error') }}</div>@endif

<div style="display:grid;gap:11px">
@forelse($rows as $b)
@php
  $st=$b->request_status;
  $style=$st==='approved'?'background:#ecfdf5;color:#047857':($st==='pending'?'background:#eff6ff;color:#1d4ed8':($st==='changes_requested'?'background:#fff7ed;color:#c2410c':($st==='rejected'?'background:#fef2f2;color:#b91c1c':'background:#f1f5f9;color:#475569')));
@endphp
<div style="background:#fff;border:1px solid #e7ebf1;border-radius:14px;padding:15px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:15px;align-items:center">
  <div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><strong>{{ $b->title }}</strong><span style="{{ $style }};padding:5px 9px;border-radius:999px;font-size:9px;font-weight:800">{{ ucwords(str_replace('_',' ',$st)) }}</span></div>
    <div style="font-size:11px;color:#64748b;margin-top:5px">{{ $b->category_name ?: 'Category not selected' }} · {{ $b->reference_no }} · {{ $b->city ?: 'City pending' }}, {{ $b->state ?: 'State pending' }} · ₹{{ number_format($b->asking_price) }}</div>
    @if(in_array($st,['changes_requested','rejected']) && $b->admin_remarks)<div style="margin-top:7px;background:#fff7ed;color:#9a3412;padding:7px 9px;border-radius:8px;font-size:10px"><strong>Admin:</strong> {{ $b->admin_remarks }}</div>@endif
  </div>
  <div style="display:flex;gap:7px;align-items:center">
    @if(in_array($st,['draft','changes_requested','rejected']))
      <a href="{{ url('/owner/business/'.$b->id.'/edit') }}" style="text-decoration:none;background:#172033;color:#fff;padding:8px 11px;border-radius:8px;font-size:10px;font-weight:800"><i class="fa-solid fa-pen"></i> Edit</a>
    @elseif($st==='approved')
      <a href="{{ url('/business/'.$b->slug) }}" target="_blank" style="text-decoration:none;background:#2f9e5b;color:#fff;padding:8px 11px;border-radius:8px;font-size:10px;font-weight:800">View Live</a>
    @else
      <span style="font-size:10px;color:#64748b"><i class="fa-solid fa-lock"></i> Locked during review</span>
    @endif
  </div>
</div>
@empty
<div style="background:#fff;padding:45px;text-align:center;border-radius:14px;color:#94a3b8">No businesses listed yet.</div>
@endforelse
</div>
<div style="margin-top:15px">{{ $rows->links() }}</div>
@endsection