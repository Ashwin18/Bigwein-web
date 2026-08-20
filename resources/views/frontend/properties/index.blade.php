@extends('frontend.layouts.app')
@section('title','Properties')
@section('content')
@php use App\Http\Controllers\Frontend\FrontendController as FC; $currency = $s['currency_symbol'] ?? '₹'; $ptypeReq = (string) request('propery_type', request('type','')); @endphp

<div class="page-hero"><div class="container"><h1>Properties</h1>
  <div class="breadcrumb"><a href="/">Home</a><i class="fa-solid fa-chevron-right fa-xs"></i><b>Properties</b></div>
</div></div>

<div style="background:#fff;border-bottom:1px solid var(--border);padding:14px 0;">
  <div class="container">
    <form method="GET" action="/properties" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <div style="flex:1;min-width:200px;border:1px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-magnifying-glass" style="color:#9CA3AF;"></i>
        <input type="text" name="search" placeholder="City, state, title…" value="{{ request('search') }}" style="border:none;outline:none;font-family:'Poppins',sans-serif;font-size:14px;width:100%;"/>
      </div>
      <select name="propery_type" style="border:1px solid var(--border);border-radius:8px;padding:10px 12px;font-family:'Poppins',sans-serif;font-size:14px;">
        <option value="">Buy & Rent</option>
        <option value="0" {{ $ptypeReq==='0'?'selected':'' }}>For Sale</option>
        <option value="1" {{ $ptypeReq==='1'?'selected':'' }}>For Rent</option>
      </select>
      <button type="submit" class="btn-search-main" style="padding:10px 22px;"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
  </div>
</div>

<div class="container"><div class="properties-layout" style="padding-bottom:48px;">
  <!-- Sidebar -->
  <aside class="filter-sidebar" id="filterSidebar">
    <h3 style="display:flex;justify-content:space-between;">Filters <a href="/properties" style="font-size:12px;color:var(--red);font-weight:600;">Reset</a></h3>
    <form method="GET" action="/properties" id="fForm">
      @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}"/>@endif
      @if($ptypeReq!=='')<input type="hidden" name="propery_type" value="{{ $ptypeReq }}"/>@endif
      @foreach(['sub_type','subtype_label','bhk','prop_status','listing_purpose','comm_types'] as $keep)
        @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"/>@endif
      @endforeach
      <div class="filter-group"><label class="filter-label">For</label>
        <div class="filter-chips">
          <a href="/properties?{{ http_build_query(array_merge(request()->except(['type','propery_type']),[]))}}" class="filter-chip {{ $ptypeReq==='' ?'active':'' }}">All</a>
          <a href="/properties?{{ http_build_query(array_merge(request()->except(['type','propery_type']),['propery_type'=>'0']))}}" class="filter-chip {{ $ptypeReq==='0'?'active':'' }}">Buy</a>
          <a href="/properties?{{ http_build_query(array_merge(request()->except(['type','propery_type']),['propery_type'=>'1']))}}" class="filter-chip {{ $ptypeReq==='1'?'active':'' }}">Rent</a>
        </div>
      </div>
      <div class="filter-group"><label class="filter-label">Property Type</label>
        <div class="filter-chips" style="flex-wrap:wrap;">
          @foreach($categories as $cat)
            <a href="/properties?{{ http_build_query(array_merge(request()->all(),['category_id'=>$cat->id]))}}" class="filter-chip {{ request('category_id')==$cat->id?'active':'' }}">
              {{ $cat->translated_name ?? $cat->category }}
            </a>
          @endforeach
        </div>
      </div>
      <div class="filter-group"><label class="filter-label">Budget (₹)</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
          <input class="filter-input" type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}"/>
          <input class="filter-input" type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}"/>
        </div>
      </div>
      <div class="filter-group"><label class="filter-label">City</label>
        <select class="filter-input" name="city" onchange="this.form.submit()">
          <option value="">All Cities</option>
          @foreach($cities as $c)<option value="{{ $c }}" {{ request('city')===$c?'selected':'' }}>{{ $c }}</option>@endforeach
        </select>
      </div>
      <div class="filter-group"><label class="filter-label">Sort By</label>
        <select class="filter-input" name="sort" onchange="this.form.submit()">
          <option value="">Relevance</option>
          <option value="price_asc"  {{ request('sort')==='price_asc'?'selected':'' }}>Price: Low to High</option>
          <option value="price_desc" {{ request('sort')==='price_desc'?'selected':'' }}>Price: High to Low</option>
          <option value="newest"     {{ request('sort')==='newest'?'selected':'' }}>Newest First</option>
        </select>
      </div>
      <button type="submit" class="btn-apply"><i class="fa-solid fa-filter"></i> Apply Filters</button>
    </form>
  </aside>

  <!-- Results -->
  <div>
    <button class="mobile-filter-btn" onclick="document.getElementById('filterSidebar').classList.toggle('open')">
      <i class="fa-solid fa-sliders"></i> Filters
    </button>

    <!-- Active filters -->
    @if(array_filter($filters ?? []))
      <div class="active-tags" style="margin-bottom:16px;">
        @php $activePType = $filters['propery_type'] ?? ($filters['type'] ?? null); @endphp
        @if($activePType !== null)
          <div class="active-tag">{{ (string)$activePType==='0'?'Buy':'Rent' }} <a href="/properties?{{ http_build_query(array_diff_key($filters,['type'=>'','propery_type'=>''])) }}">✕</a></div>
        @endif
        @if(isset($filters['subtype_label']))
          <div class="active-tag">{{ $filters['subtype_label'] }} <a href="/properties?{{ http_build_query(array_diff_key($filters,['subtype_label'=>'','sub_type'=>''])) }}">✕</a></div>
        @endif
        @if(isset($filters['bhk']))
          <div class="active-tag">{{ $filters['bhk'] }} <a href="/properties?{{ http_build_query(array_diff_key($filters,['bhk'=>''])) }}">✕</a></div>
        @endif
        @if(isset($filters['prop_status']))
          <div class="active-tag">{{ $filters['prop_status'] }} <a href="/properties?{{ http_build_query(array_diff_key($filters,['prop_status'=>''])) }}">✕</a></div>
        @endif
        @if(isset($filters['search']))
          <div class="active-tag">"{{ $filters['search'] }}" <a href="/properties?{{ http_build_query(array_diff_key($filters,['search'=>''])) }}">✕</a></div>
        @endif
        @if(isset($filters['city']))
          <div class="active-tag">{{ $filters['city'] }} <a href="/properties?{{ http_build_query(array_diff_key($filters,['city'=>''])) }}">✕</a></div>
        @endif
      </div>
    @endif

    <div class="results-toolbar">
      <p>Showing <b>{{ $properties->firstItem() }}–{{ $properties->lastItem() }}</b> of <b>{{ $properties->total() }}</b> results</p>
    </div>

    @php $badges=['FEATURED','PREMIUM','','NEW LAUNCH']; @endphp
    <div class="prop-grid-listing">
      @forelse($properties as $pi => $prop)
        @php
          $pType  = $prop->propery_type;
          $pImg   = $prop->title_image ?: '';
          $pTitle = $prop->translated_title ?? $prop->title ?? 'Property';
          $pCity  = implode(', ', array_filter([$prop->city, $prop->state]));
          $pBadge = $prop->is_premium ? 'PREMIUM' : ($badges[$pi % 4]);
          $pUrl   = '/property/'.($prop->slug_id ?? $prop->id);
          $pFav   = \DB::table('favourites')->where('user_id', optional($customer)['id'] ?? 0)->where('property_id',$prop->id)->exists();
          $pParms = collect($prop->parameters ?? []);
          $pBeds  = $pParms->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bed'));
          $pBaths = $pParms->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'bath'));
          $pArea  = $pParms->first(fn($p) => str_contains(strtolower($p['name'] ?? ''),'area') || str_contains(strtolower($p['name'] ?? ''),'sqft') || str_contains(strtolower($p['name'] ?? ''),'size'));
        @endphp
        <div class="prop-card" onclick="window.location.href='{{ $pUrl }}'">
          <div class="pci">
            @if($pImg)<img src="{{ $pImg }}" alt="{{ $pTitle }}" loading="lazy" onerror="this.style.background='#f3f4f6'"/>
            @else<div style="width:100%;height:100%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-image" style="font-size:36px;color:#d1d5db;"></i></div>@endif
            @if($pBadge)<span class="pbadge">{{ $pBadge }}</span>@endif
            <span class="pbadge" style="right:12px;left:auto;background:{{ $pType==0?'#E5343A':'#F59E0B' }};">{{ $pType==0?'Sale':'Rent' }}</span>
            <button class="pwish {{ $pFav?'liked':'' }}" onclick="event.stopPropagation();bwFav(this,{{ $prop->id }})">
              <i class="fa-{{ $pFav?'solid':'regular' }} fa-heart"></i>
            </button>
            <div class="pprice-over">
              <span class="pprice">{{ FC::fmt($prop->price) }}{{ $pType==1&&$prop->rentduration?' / '.$prop->rentduration:'' }}</span>
            </div>
          </div>
          <div class="pcb">
            <div class="pname">{{ $pTitle }}</div>
            <div class="ploc"><i class="fa-solid fa-location-dot"></i> {{ $pCity ?: 'India' }}</div>
            <div class="pspecs">
              @if($pBeds)<span class="pspec"><i class="fa-solid fa-bed"></i> {{ $pBeds['value'] ?? '-' }} Bed</span>@endif
              @if($pBaths)<span class="pspec"><i class="fa-solid fa-bath"></i> {{ $pBaths['value'] ?? '-' }} Bath</span>@endif
              @if($pArea)<span class="pspec"><i class="fa-solid fa-ruler-combined"></i> {{ $pArea['value'] ?? '-' }} sq.ft</span>@endif
            </div>
            @php $owner = $prop->customer ?? null; @endphp
            @if($owner && $owner->mobile)
            <div style="display:flex;gap:6px;margin-top:10px;" onclick="event.stopPropagation()">
              <a href="tel:{{ $owner->mobile }}"
                 style="flex:1;padding:7px;background:var(--red);color:#fff;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;gap:4px;">
                <i class="fa-solid fa-phone fa-xs"></i> Call
              </a>
              <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', ($owner->country_code ?? '91').($owner->mobile)) }}?text={{ urlencode('Hi, I am interested in: '.$prop->title) }}"
                 target="_blank"
                 style="flex:1;padding:7px;background:#25D366;color:#fff;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;gap:4px;">
                <i class="fa-brands fa-whatsapp fa-xs"></i> WhatsApp
              </a>
            </div>
            @endif
          </div>
        </div>
      @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px;color:#6B7280;">
          <i class="fa-solid fa-magnifying-glass" style="font-size:40px;opacity:.3;display:block;margin-bottom:12px;"></i>
          <p>No properties match your filters.</p>
          <a href="/properties" style="color:var(--red);font-weight:600;margin-top:10px;display:inline-block;">Clear Filters</a>
        </div>
      @endforelse
    </div>

    @if($properties->hasPages())
      <div class="pagination">
        @if(!$properties->onFirstPage())<a href="{{ $properties->previousPageUrl() }}" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>@endif
        @foreach($properties->getUrlRange(1,$properties->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="page-btn {{ $page==$properties->currentPage()?'active':'' }}">{{ $page }}</a>
        @endforeach
        @if($properties->hasMorePages())<a href="{{ $properties->nextPageUrl() }}" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>@endif
      </div>
    @endif
  </div>
</div></div>
@endsection
