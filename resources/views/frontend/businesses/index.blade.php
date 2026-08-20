@extends('frontend.layouts.app')
@section('title','Businesses For Sale — BigWein')
@section('meta_desc','Browse verified businesses for sale across India. Restaurants, retail stores, franchises and more. Zero brokerage.')

@php
  $currency = $s['currency_symbol'] ?? '₹';
  $selectedCategory = request('category');
  $selectedType = request('type') ?: request('btype');

  $min = request('min_price', request('price_min'));
  $max = request('max_price', request('price_max'));

  $budgetValue = '';
  if ($min !== null && $min !== '') {
      $budgetValue = (string)$min.'-'.(string)($max ?? '');
  }

  $formatMoney = function($amount) use ($currency) {
      $amount = (float)$amount;
      if ($amount >= 10000000) return $currency.number_format($amount / 10000000, 2).' Cr';
      if ($amount >= 100000) return $currency.number_format($amount / 100000, 2).' L';
      return $currency.number_format($amount);
  };
@endphp

@push('styles')
<style>
.bwl-page{background:#f7f9fc;min-height:65vh}
.bwl-hero{background:linear-gradient(135deg,#0a0a14,#1c1126);padding:45px 0 38px;position:relative;overflow:hidden}
.bwl-hero:after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 20% 20%,rgba(229,52,58,.12),transparent 26%),radial-gradient(circle at 80% 30%,rgba(229,52,58,.08),transparent 22%)}
.bwl-hero .container{position:relative;z-index:1}
.bwl-crumb{font-size:12px;color:rgba(255,255,255,.5);margin-bottom:9px}.bwl-crumb a{color:inherit;text-decoration:none}
.bwl-title{font-size:34px;font-weight:800;color:#fff;margin:0}.bwl-sub{font-size:13px;color:rgba(255,255,255,.62);margin-top:8px}
.bwl-filter-wrap{background:#fff;border-bottom:1px solid #e5eaf1;position:sticky;top:0;z-index:90}
.bwl-filter{display:grid;grid-template-columns:1.35fr 1fr 1fr auto auto;gap:9px;padding:14px 0}
.bwl-filter input,.bwl-filter select{width:100%;border:1px solid #dfe5ed;border-radius:10px;padding:10px 12px;font-size:12px;background:#fff;color:#354057;box-sizing:border-box}
.bwl-search{border:0;background:#e5343a;color:#fff;border-radius:10px;padding:0 20px;font-size:12px;font-weight:800}
.bwl-clear{display:flex;align-items:center;color:#e5343a;text-decoration:none;font-size:11px;font-weight:700;white-space:nowrap}
.bwl-content{padding:30px 0 55px}
.bwl-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.bwl-card{background:#fff;border:1px solid #e5eaf1;border-radius:16px;overflow:hidden;text-decoration:none;color:inherit;transition:.2s;display:flex;flex-direction:column}
.bwl-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(15,23,42,.09)}
.bwl-img{height:205px;background:#edf1f6;position:relative;overflow:hidden}
.bwl-img img{width:100%;height:100%;object-fit:cover;display:block}
.bwl-noimg{height:100%;display:flex;align-items:center;justify-content:center;color:#b1bac9;font-size:32px}
.bwl-badge{position:absolute;top:12px;left:12px;background:rgba(255,255,255,.94);color:#20273a;border-radius:999px;padding:6px 9px;font-size:9px;font-weight:800}
.bwl-featured{position:absolute;top:12px;right:12px;background:#e5343a;color:#fff;border-radius:999px;padding:6px 9px;font-size:9px;font-weight:800}
.bwl-body{padding:15px;display:flex;flex:1;flex-direction:column}
.bwl-price{font-size:19px;font-weight:800;color:#e5343a;margin-bottom:6px}
.bwl-name{font-size:16px;font-weight:800;color:#141a2c;margin:0 0 7px;line-height:1.35}
.bwl-loc{font-size:11px;color:#768095;margin-bottom:10px}
.bwl-desc{font-size:11px;color:#566176;line-height:1.55;margin:0 0 12px}
.bwl-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin-top:auto}
.bwl-m{background:#f8fafc;border:1px solid #edf0f4;border-radius:9px;padding:8px}
.bwl-m small{display:block;color:#9aa3b3;font-size:8px;text-transform:uppercase;font-weight:800}.bwl-m strong{display:block;color:#273046;font-size:10px;margin-top:3px}
.bwl-foot{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f0f2f5;margin-top:13px;padding-top:12px}
.bwl-ref{font-size:9px;color:#9aa3b3}.bwl-view{background:#172033;color:#fff;border-radius:8px;padding:8px 10px;font-size:10px;font-weight:800}
.bwl-empty{background:#fff;border:1px solid #e8ecf2;border-radius:16px;padding:65px 20px;text-align:center}.bwl-empty i{font-size:40px;color:#dbe2ec}.bwl-empty h3{font-size:18px;color:#263047;margin:13px 0 5px}.bwl-empty p{font-size:12px;color:#94a3b8}.bwl-empty a{display:inline-block;margin-top:10px;background:#e5343a;color:#fff;border-radius:9px;padding:10px 14px;text-decoration:none;font-size:11px;font-weight:800}
@media(max-width:980px){.bwl-grid{grid-template-columns:repeat(2,1fr)}.bwl-filter{grid-template-columns:1fr 1fr 1fr auto}.bwl-clear{grid-column:1/-1}}
@media(max-width:650px){.bwl-grid{grid-template-columns:1fr}.bwl-filter{grid-template-columns:1fr}.bwl-search{padding:11px}.bwl-title{font-size:27px}.bwl-content{padding-top:20px}}
</style>
@endpush

@section('content')
<div class="bwl-page">

  <section class="bwl-hero">
    <div class="container">
      <div class="bwl-crumb"><a href="{{ url('/') }}">Home</a> &nbsp;›&nbsp; Businesses For Sale</div>
      <h1 class="bwl-title">Businesses For Sale</h1>
      <div class="bwl-sub">{{ $businesses->total() }} verified {{ Str::plural('business',$businesses->total()) }} listed · Zero brokerage</div>
    </div>
  </section>

  <div class="bwl-filter-wrap">
    <div class="container">
      <form method="GET" action="{{ url('/businesses') }}" class="bwl-filter">
        <input type="text" name="city" value="{{ request('city') }}" placeholder="City or locality">

        <select name="category">
          <option value="">All Business Categories</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}"
              {{ (string)$selectedCategory === (string)$c->id || (!$selectedCategory && $selectedType && strcasecmp($selectedType,$c->name)===0) ? 'selected' : '' }}>
              {{ $c->name }}
            </option>
          @endforeach
        </select>

        <select id="bizBudget">
          <option value="">Any Budget</option>
          <option value="0-2500000" {{ $budgetValue==='0-2500000'?'selected':'' }}>Under ₹25L</option>
          <option value="2500000-5000000" {{ $budgetValue==='2500000-5000000'?'selected':'' }}>₹25L–₹50L</option>
          <option value="5000000-10000000" {{ $budgetValue==='5000000-10000000'?'selected':'' }}>₹50L–₹1Cr</option>
          <option value="10000000-20000000" {{ $budgetValue==='10000000-20000000'?'selected':'' }}>₹1Cr–₹2Cr</option>
          <option value="20000000-" {{ $budgetValue==='20000000-'?'selected':'' }}>Above ₹2Cr</option>
        </select>

        <input type="hidden" id="bizMin" name="min_price" value="{{ $min }}">
        <input type="hidden" id="bizMax" name="max_price" value="{{ $max }}">

        <button type="submit" class="bwl-search"><i class="fa-solid fa-magnifying-glass"></i> Search</button>

        @if(request()->anyFilled(['city','category','type','btype','min_price','max_price','price_min','price_max']))
          <a href="{{ url('/businesses') }}" class="bwl-clear">✕ Clear filters</a>
        @endif
      </form>
    </div>
  </div>

  <section class="bwl-content">
    <div class="container">
      @if($businesses->count())
      <div class="bwl-grid">
        @foreach($businesses as $biz)
          @php
            $imgSrc = $biz->cover_image ? asset('images/businesses/'.$biz->id.'/'.$biz->cover_image) : null;
            $displayTitle = $biz->is_confidential ? 'Confidential '.$biz->category_name.' Business for Sale' : $biz->title;
          @endphp

          <a href="{{ url('/business/'.$biz->slug) }}" class="bwl-card">
            <div class="bwl-img">
              @if($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ $displayTitle }}">
              @else
                <div class="bwl-noimg"><i class="fa-solid fa-store"></i></div>
              @endif

              <span class="bwl-badge">{{ $biz->category_name ?: ($biz->business_type ?: 'Business') }}</span>
              @if($biz->is_featured)<span class="bwl-featured">Featured</span>@endif
            </div>

            <div class="bwl-body">
              <div class="bwl-price">{{ $formatMoney($biz->asking_price) }}</div>
              <h3 class="bwl-name">{{ $displayTitle }}</h3>

              <div class="bwl-loc">
                <i class="fa-solid fa-location-dot" style="color:#e5343a"></i>
                {{ implode(', ',array_filter([$biz->locality,$biz->city,$biz->state])) ?: 'Location on request' }}
              </div>

              <p class="bwl-desc">{{ Str::limit($biz->description,115) }}</p>

              <div class="bwl-meta">
                <div class="bwl-m"><small>Established</small><strong>{{ $biz->established_year ?: '—' }}</strong></div>
                <div class="bwl-m"><small>Employees</small><strong>{{ $biz->employees ?: '—' }}</strong></div>
                <div class="bwl-m"><small>Status</small><strong>{{ ucwords(str_replace('_',' ',$biz->business_status ?: 'Running')) }}</strong></div>
              </div>

              <div class="bwl-foot">
                <span class="bwl-ref">{{ $biz->reference_no ?: '' }}</span>
                <span class="bwl-view">View Business →</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      @if($businesses->hasPages())
        <div style="margin-top:24px">{{ $businesses->links() }}</div>
      @endif
      @else
        <div class="bwl-empty">
          <i class="fa-solid fa-store"></i>
          <h3>No businesses match your search</h3>
          <p>Try another category, location or budget.</p>
          <a href="{{ url('/businesses') }}">View All Businesses</a>
        </div>
      @endif
    </div>
  </section>
</div>

<script>
(function(){
  const budget = document.getElementById('bizBudget');
  const min = document.getElementById('bizMin');
  const max = document.getElementById('bizMax');

  if(!budget) return;

  budget.addEventListener('change', function(){
    const v = this.value.split('-');
    min.value = v[0] || '';
    max.value = v[1] || '';
  });
})();
</script>
@endsection
