@extends('frontend.layouts.app')
@section('title','My Enquiries — BigWein')
@section('content')
@php $currency = $s['currency_symbol'] ?? '₹'; @endphp

<style>
.ud-wrap{max-width:1100px;margin:0 auto;padding:28px 16px 60px;}
.ud-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
.ud-nav a{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;color:#374151;}
.ud-nav a.active{background:var(--red);color:#fff;border-color:var(--red);}
.enq-card{background:#fff;border:1px solid #F1F5F9;border-radius:14px;overflow:hidden;margin-bottom:12px;display:flex;align-items:center;gap:0;transition:border-color .15s;}
.enq-card:hover{border-color:var(--red);}
.enq-img{width:110px;height:90px;object-fit:cover;flex-shrink:0;}
.enq-img-placeholder{width:110px;height:90px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.enq-body{padding:14px 16px;flex:1;min-width:0;}
.enq-meta{font-size:11px;color:#94A3B8;margin-top:4px;}
.enq-type{font-size:10px;font-weight:700;padding:3px 9px;border-radius:8px;}
@media(max-width:560px){.enq-img,.enq-img-placeholder{width:80px;height:70px;}.enq-card{flex-wrap:wrap;}.enq-body{padding:12px;}}
</style>

<div class="ud-wrap">
  <div style="margin-bottom:18px;">
    <h1 style="font-size:22px;font-weight:800;color:#0F172A;margin-bottom:2px;"><i class="fa-solid fa-message" style="color:#16A34A;margin-right:8px;"></i>My Enquiries</h1>
    <p style="color:#64748B;font-size:13px;">{{ $enquiries->total() }} enquiries sent</p>
  </div>

  <div class="ud-nav">
    <a href="/user/dashboard"><i class="fa-solid fa-grid-2 fa-xs"></i> Dashboard</a>
    <a href="/user/saved"><i class="fa-solid fa-heart fa-xs"></i> Saved</a>
    <a href="/user/enquiries" class="active"><i class="fa-solid fa-message fa-xs"></i> My Enquiries</a>
    <a href="/user/profile"><i class="fa-solid fa-user fa-xs"></i> Profile</a>
  </div>

  @if($enquiries->isEmpty())
    <div style="text-align:center;padding:80px 20px;background:#fff;border-radius:16px;border:1px solid #F1F5F9;">
      <i class="fa-solid fa-message" style="font-size:48px;color:#E2E8F0;display:block;margin-bottom:16px;"></i>
      <h3 style="color:#374151;margin-bottom:8px;">No enquiries yet</h3>
      <p style="color:#94A3B8;margin-bottom:20px;">When you contact a property owner, your enquiries will appear here.</p>
      <a href="/properties" style="background:var(--red);color:#fff;padding:11px 28px;border-radius:10px;text-decoration:none;font-weight:700;">Browse Properties</a>
    </div>
  @else
    @foreach($enquiries as $enq)
    <div class="enq-card">
      @if($enq->title_image)
        <img src="{{ $enq->title_image }}" class="enq-img" alt="Property"
          onerror="this.style.display='none'"/>
      @else
        <div class="enq-img-placeholder"><i class="fa-solid fa-building" style="font-size:24px;color:#CBD5E1;"></i></div>
      @endif
      <div class="enq-body">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;flex-wrap:wrap;">
          <div style="flex:1;min-width:0;">
            <div style="font-size:14px;font-weight:700;color:#0F172A;margin-bottom:3px;">
              <a href="/property/{{ $enq->slug_id }}" style="color:inherit;text-decoration:none;">{{ Str::limit($enq->prop_title ?? 'Property',50) }}</a>
            </div>
            <div class="enq-meta">
              @if($enq->city)<i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $enq->city }} &nbsp;·&nbsp;@endif
              <i class="fa-solid fa-clock"></i> {{ \Carbon\Carbon::parse($enq->created_at)->diffForHumans() }}
              @if($enq->price) &nbsp;·&nbsp; <strong>{{ $s['currency_symbol']??'₹' }}{{ number_format($enq->price) }}</strong>@endif
            </div>
            @if($enq->message)
            <div style="font-size:12px;color:#374151;margin-top:6px;background:#F8FAFC;padding:6px 10px;border-radius:6px;border-left:3px solid var(--red);">
              "{{ Str::limit($enq->message,80) }}"
            </div>
            @endif
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0;">
            <span class="enq-type" style="background:#EFF6FF;color:#1D4ED8;">{{ $enq->type ?? 'Message' }}</span>
            @if($enq->propery_type == 0)
              <span class="enq-type" style="background:#FFF1F3;color:#9F1239;">For Sale</span>
            @else
              <span class="enq-type" style="background:#F0FDF4;color:#166534;">For Rent</span>
            @endif
            <a href="/property/{{ $enq->slug_id }}" style="font-size:11px;color:var(--red);font-weight:600;text-decoration:none;">View Property →</a>
          </div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- Pagination --}}
    @if($enquiries->lastPage() > 1)
    <div style="display:flex;justify-content:center;gap:6px;margin-top:24px;">
      @foreach($enquiries->getUrlRange(1,$enquiries->lastPage()) as $page => $url)
      <a href="{{ $url }}" style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;text-decoration:none;border:1px solid {{ $page==$enquiries->currentPage()?'var(--red)':'#E2E8F0' }};background:{{ $page==$enquiries->currentPage()?'var(--red)':'#fff' }};color:{{ $page==$enquiries->currentPage()?'#fff':'#374151' }};">{{ $page }}</a>
      @endforeach
    </div>
    @endif
  @endif
</div>
@endsection
