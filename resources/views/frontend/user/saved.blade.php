@extends('frontend.layouts.app')
@section('title','Saved Properties — BigWein')
@section('content')
@php $currency = $s['currency_symbol'] ?? '₹'; @endphp

<style>
.ud-wrap{max-width:1100px;margin:0 auto;padding:28px 16px 60px;}
.ud-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;}
.ud-nav a{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid #E2E8F0;color:#374151;transition:all .15s;}
.ud-nav a.active{background:var(--red);color:#fff;border-color:var(--red);}
.saved-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
.sv-card{background:#fff;border:1px solid #F1F5F9;border-radius:14px;overflow:hidden;transition:all .2s;position:relative;}
.sv-card:hover{border-color:var(--red);box-shadow:0 6px 24px rgba(229,52,58,.1);transform:translateY(-2px);}
.sv-img{height:165px;object-fit:cover;width:100%;}
.sv-body{padding:14px;}
@media(max-width:900px){.saved-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:560px){.saved-grid{grid-template-columns:1fr;}}
</style>

<div class="ud-wrap">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
    <div>
      <h1 style="font-size:22px;font-weight:800;color:#0F172A;margin-bottom:2px;"><i class="fa-solid fa-heart" style="color:var(--red);margin-right:8px;"></i>Saved Properties</h1>
      <p style="color:#64748B;font-size:13px;">{{ $properties->total() }} saved properties</p>
    </div>
  </div>

  <div class="ud-nav">
    <a href="/user/dashboard"><i class="fa-solid fa-grid-2 fa-xs"></i> Dashboard</a>
    <a href="/user/saved" class="active"><i class="fa-solid fa-heart fa-xs"></i> Saved Properties</a>
    <a href="/user/enquiries"><i class="fa-solid fa-message fa-xs"></i> My Enquiries</a>
    <a href="/user/profile"><i class="fa-solid fa-user fa-xs"></i> Profile</a>
  </div>

  @if($properties->isEmpty())
    <div style="text-align:center;padding:80px 20px;background:#fff;border-radius:16px;border:1px solid #F1F5F9;">
      <i class="fa-regular fa-heart" style="font-size:48px;color:#E2E8F0;display:block;margin-bottom:16px;"></i>
      <h3 style="color:#374151;margin-bottom:8px;">No saved properties yet</h3>
      <p style="color:#94A3B8;margin-bottom:20px;">Browse properties and tap the heart icon to save them here.</p>
      <a href="/properties" style="background:var(--red);color:#fff;padding:11px 28px;border-radius:10px;text-decoration:none;font-weight:700;">Browse Properties</a>
    </div>
  @else
  <div class="saved-grid">
    @foreach($properties as $prop)
    <div class="sv-card">
      <a href="/property/{{ $prop->slug_id }}">
        @if($prop->title_image)
          <img src="{{ $prop->title_image }}" class="sv-img" alt="{{ $prop->title }}"
            onerror="this.style.display='none'"/>
        @else
          <div style="height:165px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-building" style="font-size:36px;color:#CBD5E1;"></i>
          </div>
        @endif
      </a>
      <button onclick="removeSaved({{ $prop->id }}, this)"
        title="Remove from saved"
        style="position:absolute;top:10px;right:10px;background:rgba(0,0,0,.55);border:none;border-radius:50%;width:30px;height:30px;color:#fff;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;">
        <i class="fa-solid fa-heart-crack"></i>
      </button>
      @if($prop->propery_type == 0)
        <span style="position:absolute;top:10px;left:10px;background:#1D4ED8;color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:12px;">For Sale</span>
      @else
        <span style="position:absolute;top:10px;left:10px;background:#16A34A;color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:12px;">For Rent</span>
      @endif
      <div class="sv-body">
        <h3 style="font-size:14px;font-weight:700;color:#0F172A;margin-bottom:5px;line-height:1.3;">
          <a href="/property/{{ $prop->slug_id }}" style="color:inherit;text-decoration:none;">{{ Str::limit($prop->title,45) }}</a>
        </h3>
        <p style="font-size:12px;color:#64748B;margin-bottom:8px;"><i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $prop->city }}</p>
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <span style="font-size:15px;font-weight:800;color:var(--red);">{{ $currency }}{{ number_format($prop->price) }}</span>
          <a href="/property/{{ $prop->slug_id }}" style="font-size:12px;font-weight:600;color:var(--red);text-decoration:none;">View →</a>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($properties->lastPage() > 1)
  <div style="display:flex;justify-content:center;gap:6px;margin-top:32px;">
    @foreach($properties->getUrlRange(1,$properties->lastPage()) as $page => $url)
    <a href="{{ $url }}" style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;text-decoration:none;border:1px solid {{ $page==$properties->currentPage()?'var(--red)':'#E2E8F0' }};background:{{ $page==$properties->currentPage()?'var(--red)':'#fff' }};color:{{ $page==$properties->currentPage()?'#fff':'#374151' }};">{{ $page }}</a>
    @endforeach
  </div>
  @endif
  @endif
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
  if (!d.error) { btn.closest('.sv-card').style.opacity='0.3'; toastr.success('Removed from saved!'); setTimeout(()=>location.reload(),800); }
}
</script>
@endsection
