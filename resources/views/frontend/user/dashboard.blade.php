@extends('frontend.layouts.app')
@section('title', 'My Dashboard — BigWein')
@section('content')
@php $cu = $customer; $name = $cu['name'] ?? 'User'; $email = $cu['email'] ?? ''; @endphp

<style>
.ud-wrap{max-width:1100px;margin:0 auto;padding:28px 16px 60px;}
.ud-header{background:linear-gradient(135deg,#0F172A,#1E293B);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:20px;margin-bottom:24px;}
.ud-avatar{width:64px;height:64px;border-radius:50%;background:#E5343A;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;flex-shrink:0;}
.ud-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px;}
.ud-stat{background:#fff;border:1px solid #F1F5F9;border-radius:12px;padding:20px;text-align:center;}
.ud-stat-val{font-size:32px;font-weight:800;color:#0F172A;}
.ud-stat-lbl{font-size:12px;color:#64748B;margin-top:4px;}
.ud-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
.ud-nav a{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;color:#374151;transition:all .15s;}
.ud-nav a:hover{border-color:var(--red);color:var(--red);}
.ud-nav a.active{background:var(--red);color:#fff;border-color:var(--red);}
.ud-section{background:#fff;border:1px solid #F1F5F9;border-radius:14px;overflow:hidden;margin-bottom:20px;}
.ud-sec-hdr{padding:16px 20px;border-bottom:1px solid #F8FAFC;display:flex;align-items:center;justify-content:space-between;}
.ud-sec-title{font-size:15px;font-weight:700;color:#0F172A;}
.ud-prop-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;padding:16px 20px;}
.ud-prop-card{border:1px solid #F1F5F9;border-radius:12px;overflow:hidden;position:relative;}
.ud-prop-img{height:130px;object-fit:cover;width:100%;}
.ud-prop-body{padding:12px;}
.ud-enq-item{padding:14px 20px;border-bottom:1px solid #F8FAFC;display:flex;align-items:center;gap:12px;}
.ud-enq-item:last-child{border-bottom:none;}
.ud-enq-img{width:52px;height:52px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#F1F5F9;}
.ud-enq-badge{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:8px;}
@media(max-width:768px){.ud-prop-grid{grid-template-columns:1fr;}.ud-stats{grid-template-columns:1fr 1fr 1fr;}.ud-header{padding:20px;}}
@media(max-width:480px){.ud-stats{grid-template-columns:1fr 1fr;}.ud-header{flex-direction:column;text-align:center;}.ud-prop-grid{grid-template-columns:1fr;}}
</style>

<div class="ud-wrap">
  {{-- Header --}}
  <div class="ud-header">
    <div class="ud-avatar">{{ strtoupper(substr($name,0,1)) }}</div>
    <div>
      <div style="font-size:20px;font-weight:800;color:#fff;">Hello, {{ explode(' ',$name)[0] }}! 👋</div>
      <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:3px;">{{ $email }}</div>
      <div style="margin-top:10px;display:flex;gap:8px;">
        <a href="/user/profile" style="background:rgba(255,255,255,.12);color:#fff;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(255,255,255,.2);">
          <i class="fa-solid fa-pen-to-square fa-xs"></i> Edit Profile
        </a>
        <a href="#" onclick="bwLogout()" style="background:rgba(229,52,58,.3);color:#fff;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(229,52,58,.5);">
          <i class="fa-solid fa-right-from-bracket fa-xs"></i> Logout
        </a>
      </div>
    </div>
  </div>

  {{-- Stats --}}
  <div class="ud-stats">
    <div class="ud-stat">
      <div class="ud-stat-val" style="color:#E5343A;">{{ $stats['saved'] }}</div>
      <div class="ud-stat-lbl"><i class="fa-solid fa-heart" style="color:#E5343A;"></i> Saved</div>
    </div>
    <div class="ud-stat">
      <div class="ud-stat-val" style="color:#16A34A;">{{ $stats['enquiries'] }}</div>
      <div class="ud-stat-lbl"><i class="fa-solid fa-message" style="color:#16A34A;"></i> Enquiries</div>
    </div>
    <div class="ud-stat">
      <div class="ud-stat-val" style="color:#1D4ED8;">{{ $stats['views'] ?: '—' }}</div>
      <div class="ud-stat-lbl"><i class="fa-solid fa-eye" style="color:#1D4ED8;"></i> Views</div>
    </div>
  </div>

  {{-- Nav --}}
  <div class="ud-nav">
    <a href="/user/dashboard" class="active"><i class="fa-solid fa-grid-2 fa-xs"></i> Dashboard</a>
    <a href="/user/saved"><i class="fa-solid fa-heart fa-xs"></i> Saved Properties</a>
    <a href="/user/enquiries"><i class="fa-solid fa-message fa-xs"></i> My Enquiries</a>
    <a href="/user/profile"><i class="fa-solid fa-user fa-xs"></i> Profile</a>
  </div>

  {{-- Saved Properties --}}
  <div class="ud-section">
    <div class="ud-sec-hdr">
      <div class="ud-sec-title"><i class="fa-solid fa-heart" style="color:var(--red);margin-right:6px;"></i> Saved Properties</div>
      <a href="/user/saved" style="font-size:12px;color:var(--red);font-weight:600;text-decoration:none;">View All →</a>
    </div>
    @if($savedProps->isEmpty())
      <div style="padding:40px;text-align:center;color:#94A3B8;">
        <i class="fa-regular fa-heart" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        No saved properties yet. <a href="/properties" style="color:var(--red);">Browse properties →</a>
      </div>
    @else
    <div class="ud-prop-grid">
      @foreach($savedProps as $prop)
      @php $currency = $s['currency_symbol'] ?? '₹'; @endphp
      <div class="ud-prop-card">
        <a href="/property/{{ $prop->slug_id }}">
          @if($prop->title_image)
          <img src="{{ $prop->title_image }}" class="ud-prop-img" alt="{{ $prop->title }}"
            onerror="this.style.display='none'"/>
          @else
          <div style="height:130px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-building" style="font-size:28px;color:#CBD5E1;"></i>
          </div>
          @endif
        </a>
        <button onclick="removeSaved({{ $prop->id }}, this)"
          style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.5);border:none;border-radius:50%;width:28px;height:28px;color:#fff;cursor:pointer;font-size:12px;">
          <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="ud-prop-body">
          <div style="font-size:13px;font-weight:700;color:#0F172A;margin-bottom:4px;">
            <a href="/property/{{ $prop->slug_id }}" style="color:inherit;text-decoration:none;">{{ Str::limit($prop->title,40) }}</a>
          </div>
          <div style="font-size:11px;color:#64748B;margin-bottom:6px;"><i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $prop->city }}</div>
          <div style="font-size:14px;font-weight:800;color:var(--red);">{{ $currency }}{{ number_format($prop->price) }}</div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Recent Enquiries --}}
  <div class="ud-section">
    <div class="ud-sec-hdr">
      <div class="ud-sec-title"><i class="fa-solid fa-message" style="color:#16A34A;margin-right:6px;"></i> Recent Enquiries</div>
      <a href="/user/enquiries" style="font-size:12px;color:var(--red);font-weight:600;text-decoration:none;">View All →</a>
    </div>
    @if($enquiries->isEmpty())
      <div style="padding:40px;text-align:center;color:#94A3B8;">
        <i class="fa-solid fa-message" style="font-size:36px;display:block;margin-bottom:12px;"></i>
        No enquiries sent yet.
      </div>
    @else
      @foreach($enquiries as $enq)
      <div class="ud-enq-item">
        @if($enq->title_image)
          <img src="{{ $enq->title_image }}" class="ud-enq-img" alt="{{ $enq->title }}"
            onerror="this.parentElement.querySelector('.ud-enq-img').style.display='none'"/>
        @else
          <div class="ud-enq-img" style="display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-building" style="color:#CBD5E1;"></i>
          </div>
        @endif
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:#0F172A;">
            <a href="/property/{{ $enq->slug_id }}" style="color:inherit;text-decoration:none;">{{ Str::limit($enq->title ?? 'Property',45) }}</a>
          </div>
          <div style="font-size:11px;color:#64748B;margin-top:2px;">
            {{ $enq->city }} &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($enq->created_at)->diffForHumans() }}
          </div>
          <div style="margin-top:4px;">
            <span class="ud-enq-badge" style="background:#EFF6FF;color:#1D4ED8;">{{ $enq->type ?? 'Message' }}</span>
          </div>
        </div>
        <a href="/property/{{ $enq->slug_id }}" style="font-size:12px;color:var(--red);font-weight:600;text-decoration:none;flex-shrink:0;">View →</a>
      </div>
      @endforeach
    @endif
  </div>
</div>
@endsection
@section('script')
<script>
async function removeSaved(id, btn) {
  if (!confirm('Remove from saved?')) return;
  const res = await fetch('/user/remove-saved', {
    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
    body: JSON.stringify({property_id: id})
  });
  const d = await res.json();
  if (!d.error) { btn.closest('.ud-prop-card').remove(); toastr.success('Removed!'); }
}
</script>
@endsection
