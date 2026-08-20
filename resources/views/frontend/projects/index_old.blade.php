@extends('frontend.layouts.app')
@section('title','Projects — New Launches & Ongoing Developments')
@section('content')
@php
  $currency = $s['currency_symbol'] ?? '₹';
  $activeType = request('type','');
  $activeCity = request('city','');
  $activeCat  = request('cat','');
  $activeSort = request('sort','latest');
@endphp

<style>
.pj-hero{background:#0F172A;padding:36px 0 32px;}
.pj-hero-inner{display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;}
.pj-hero-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(229,52,58,.2);border:1px solid rgba(229,52,58,.4);color:#E5343A;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.6px;margin-bottom:12px;}
.pj-hero h1{font-size:clamp(22px,3vw,36px);font-weight:900;color:#fff;line-height:1.2;margin-bottom:8px;}
.pj-hero-sub{font-size:14px;color:rgba(255,255,255,.55);margin-bottom:20px;}
.pj-stats{display:flex;gap:28px;}
.pj-stat-val{font-size:22px;font-weight:800;color:#E5343A;}
.pj-stat-lbl{font-size:11px;color:rgba(255,255,255,.5);margin-top:2px;}
.pj-stat-cards{display:flex;gap:10px;flex-shrink:0;}
.pj-stat-card{border-radius:12px;padding:14px 18px;text-align:center;min-width:120px;}
.pj-filter-bar{background:#fff;border-bottom:1px solid var(--border);padding:14px 0;}
.pj-filter-inner{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.pj-filter-sel{padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:#374151;background:#fff;cursor:pointer;outline:none;}
.pj-filter-sel:focus{border-color:var(--red);}
.pj-tabs{background:#fff;border-bottom:1px solid var(--border);padding:0;}
.pj-tabs-inner{display:flex;gap:0;overflow-x:auto;}
.pj-tab{padding:14px 20px;font-size:14px;font-weight:500;color:#64748B;border-bottom:2px solid transparent;white-space:nowrap;text-decoration:none;transition:all .15s;}
.pj-tab.active,.pj-tab:hover{color:var(--red);border-bottom-color:var(--red);}
.pj-count{font-size:11px;background:#F1F5F9;color:#64748B;padding:2px 7px;border-radius:10px;margin-left:5px;}
.pj-tab.active .pj-count{background:#FFF1F3;color:var(--red);}
.pj-main{padding:28px 0 48px;}
.pj-top-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:10px;flex-wrap:wrap;}
.pj-results{font-size:14px;color:#64748B;}
.pj-results strong{color:#0F172A;}
.pj-view-toggle{display:flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;}
.pj-view-btn{padding:7px 11px;border:none;background:#fff;cursor:pointer;font-size:15px;color:#64748B;transition:all .15s;}
.pj-view-btn.active{background:var(--red);color:#fff;}
.pj-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
.pj-grid.list-view{grid-template-columns:1fr;}
.pj-card{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:all .2s;}
.pj-card:hover{border-color:var(--red);box-shadow:0 8px 32px rgba(229,52,58,.1);transform:translateY(-3px);}
.pj-card.new-launch{border-color:var(--red);}
.pj-card-img{position:relative;height:180px;overflow:hidden;background:#F1F5F9;}
.pj-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.pj-card:hover .pj-card-img img{transform:scale(1.05);}
.pj-card-img .placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;}
.pj-status-badge{position:absolute;top:12px;left:12px;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;color:#fff;}
.pj-type-badge{position:absolute;top:12px;right:12px;background:rgba(15,23,42,.75);color:#fff;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:600;}
.pj-card-body{padding:16px;}
.pj-card-body h3{font-size:15px;font-weight:700;color:#0F172A;margin-bottom:6px;line-height:1.3;}
.pj-card-loc{font-size:12px;color:#64748B;margin-bottom:8px;display:flex;align-items:center;gap:4px;}
.pj-price{font-size:17px;font-weight:800;color:var(--red);margin-bottom:10px;}
.pj-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;}
.pj-tag{font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;}
.pj-card-footer{border-top:1px solid var(--border);padding-top:12px;display:flex;align-items:center;justify-content:space-between;}
.pj-builder{font-size:12px;color:#64748B;}
.pj-builder strong{color:#0F172A;font-weight:600;}
.pj-enquire-btn{padding:7px 16px;background:var(--red);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .2s;}
.pj-enquire-btn:hover{background:#C4272D;color:#fff;}
.pj-empty{text-align:center;padding:80px 20px;grid-column:1/-1;}
.pj-pagination{display:flex;justify-content:center;gap:6px;margin-top:36px;}
.pj-page-btn{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;border:1px solid var(--border);text-decoration:none;color:#374151;transition:all .15s;}
.pj-page-btn.active,.pj-page-btn:hover{background:var(--red);border-color:var(--red);color:#fff;}
/* List view card */
.pj-grid.list-view .pj-card{display:grid;grid-template-columns:260px 1fr;}
.pj-grid.list-view .pj-card-img{height:100%;}
@media(max-width:900px){.pj-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:600px){.pj-grid{grid-template-columns:1fr;}.pj-grid.list-view .pj-card{grid-template-columns:1fr;}.pj-hero-inner{flex-direction:column;}.pj-stat-cards{flex-direction:row;}.pj-tabs-inner{gap:0;}}
</style>

<!-- HERO BANNER -->
<section class="pj-hero">
  <div class="container">
    <div class="pj-hero-inner">
      <div>
        <div class="pj-hero-badge"><i class="fa-solid fa-city fa-xs"></i> NEW LAUNCHES & ONGOING DEVELOPMENTS</div>
        <h1>Discover Premium<br/>Real Estate Projects</h1>
        <p class="pj-hero-sub">Handpicked residential & commercial developments across India</p>
        <div class="pj-stats">
          <div><div class="pj-stat-val">{{ $stats['total'] }}</div><div class="pj-stat-lbl">Total Projects</div></div>
          <div><div class="pj-stat-val">{{ $stats['cities'] }}</div><div class="pj-stat-lbl">Cities</div></div>
          <div><div class="pj-stat-val">{{ $stats['new_launch'] }}</div><div class="pj-stat-lbl">New Launches</div></div>
        </div>
      </div>
      <div class="pj-stat-cards">
        <div class="pj-stat-card" style="background:#FFF1F3;border:1px solid #FECDD3;">
          <div style="font-size:24px;font-weight:800;color:#E5343A;">{{ $stats['ready'] }}</div>
          <div style="font-size:11px;font-weight:600;color:#9F1239;margin-top:4px;">Ready to Move</div>
        </div>
        <div class="pj-stat-card" style="background:#FEF9C3;border:1px solid #FDE68A;">
          <div style="font-size:24px;font-weight:800;color:#D97706;">{{ $stats['construction'] }}</div>
          <div style="font-size:11px;font-weight:600;color:#92400E;margin-top:4px;">Under Construction</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FILTER BAR -->
<div class="pj-filter-bar">
  <div class="container">
    <form method="GET" action="/projects">
      <div class="pj-filter-inner">
        <select name="city" class="pj-filter-sel" onchange="this.form.submit()">
          <option value="">All Cities</option>
          @foreach($cities as $city)
            <option value="{{ $city }}" {{ $activeCity===$city?'selected':'' }}>{{ $city }}</option>
          @endforeach
        </select>
        <select name="cat" class="pj-filter-sel" onchange="this.form.submit()">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ $activeCat==(string)$cat->id?'selected':'' }}>{{ $cat->category }}</option>
          @endforeach
        </select>
        <select name="sort" class="pj-filter-sel" onchange="this.form.submit()">
          <option value="latest"  {{ $activeSort==='latest'?'selected':'' }}>Latest First</option>
          <option value="popular" {{ $activeSort==='popular'?'selected':'' }}>Most Popular</option>
          <option value="oldest"  {{ $activeSort==='oldest'?'selected':'' }}>Oldest First</option>
        </select>
        @if($activeCity||$activeCat||$activeSort!=='latest'||$activeType)
          <a href="/projects" style="font-size:13px;color:var(--red);font-weight:600;text-decoration:none;white-space:nowrap;">
            <i class="fa-solid fa-xmark"></i> Clear filters
          </a>
        @endif
        <div class="pj-view-toggle" style="margin-left:auto;">
          <button type="button" class="pj-view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid view">
            <i class="fa-solid fa-grip"></i>
          </button>
          <button type="button" class="pj-view-btn" id="btnList" onclick="setView('list')" title="List view">
            <i class="fa-solid fa-list"></i>
          </button>
        </div>
        @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
      </div>
    </form>
  </div>
</div>

<!-- STATUS TABS -->
<div class="pj-tabs">
  <div class="container">
    <div class="pj-tabs-inner">
      @php
        $tabs = [
          ''                    => ['label'=>'All Projects',        'count'=>$stats['total']],
          'New Launch'          => ['label'=>'New Launch',           'count'=>$stats['new_launch']],
          'Under Construction'  => ['label'=>'Under Construction',   'count'=>$stats['construction']],
          'Ready to Move'       => ['label'=>'Ready to Move',        'count'=>$stats['ready']],
        ];
      @endphp
      @foreach($tabs as $val => $tab)
        <a href="/projects{{ $val ? '?type='.urlencode($val) : '' }}{{ $activeCity ? ($val?'&':'?').'city='.urlencode($activeCity) : '' }}"
           class="pj-tab {{ $activeType===$val ? 'active' : '' }}">
          {{ $tab['label'] }}
          <span class="pj-count">{{ $tab['count'] }}</span>
        </a>
      @endforeach
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<section class="pj-main">
  <div class="container">
    <div class="pj-top-bar">
      <p class="pj-results">
        Showing <strong>{{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }}</strong>
        of <strong>{{ $projects->total() }}</strong> projects
        @if($activeType) &nbsp;·&nbsp; <span style="color:var(--red);">{{ $activeType }}</span>@endif
        @if($activeCity) &nbsp;·&nbsp; <i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $activeCity }}@endif
      </p>
    </div>

    <div class="pj-grid" id="pjGrid">
      @forelse($projects as $project)
      @php
        $pjTitle = $project->translated_title ?? $project->title ?? 'Project';
        $pjLoc   = implode(', ', array_filter([$project->location, $project->city, $project->state]));
        $pjImg   = $project->image;
        $pjType  = $project->type ?? '';
        $pjCat   = $project->category->category ?? '';
        $pjSlug  = $project->slug_id ?? $project->id;
        $isNew   = $pjType === 'New Launch';

        $statusStyle = match($pjType) {
          'Ready to Move'       => 'background:#16A34A;',
          'Under Construction'  => 'background:#D97706;',
          'New Launch'          => 'background:#E5343A;',
          default               => 'background:#64748B;',
        };
        $tagBg = match($pjType) {
          'Ready to Move'      => 'background:#F0FDF4;color:#166534;',
          'Under Construction' => 'background:#FFFBEB;color:#92400E;',
          'New Launch'         => 'background:#FFF1F3;color:#9F1239;',
          default              => 'background:#F1F5F9;color:#374151;',
        };
      @endphp
      <div class="pj-card {{ $isNew ? 'new-launch' : '' }}">
        <div class="pj-card-img">
          @if($pjImg)
            <img src="{{ $pjImg }}" alt="{{ $pjTitle }}"
              onerror="this.parentElement.innerHTML='<div class=\'placeholder\'><i class=\'fa-solid fa-city\' style=\'font-size:48px;color:#D1D5DB;\'></i></div>'"/>
          @else
            <div class="placeholder"><i class="fa-solid fa-city" style="font-size:48px;color:#D1D5DB;"></i></div>
          @endif
          @if($pjType)
            <span class="pj-status-badge" style="{{ $statusStyle }}">
              @if($isNew) 🔥 @endif{{ $pjType }}
            </span>
          @endif
          @if($pjCat)
            <span class="pj-type-badge">{{ $pjCat }}</span>
          @endif
        </div>
        <div class="pj-card-body">
          <h3>{{ Str::limit($pjTitle, 50) }}</h3>
          @if($pjLoc)
          <p class="pj-card-loc"><i class="fa-solid fa-location-dot" style="color:var(--red);"></i> {{ $pjLoc }}</p>
          @endif
          <p class="pj-price">Price on Request</p>
          <div class="pj-tags">
            @if($pjType)
            <span class="pj-tag" style="{{ $tagBg }}">{{ $pjType }}</span>
            @endif
            @if($project->total_click > 0)
            <span class="pj-tag" style="background:#EFF6FF;color:#1D4ED8;">
              <i class="fa-solid fa-eye fa-xs"></i> {{ number_format($project->total_click) }} views
            </span>
            @endif
          </div>
          <div class="pj-card-footer">
            <div class="pj-builder">
              By <strong>{{ $project->customer->name ?? 'BigWein' }}</strong>
            </div>
            <a href="/project/{{ $pjSlug }}" class="pj-enquire-btn">
              {{ $isNew ? 'Enquire Now' : 'View Project' }}
            </a>
          </div>
        </div>
      </div>
      @empty
      <div class="pj-empty">
        <i class="fa-solid fa-city" style="font-size:48px;color:#D1D5DB;display:block;margin-bottom:16px;"></i>
        <h3 style="color:#374151;margin-bottom:8px;">No projects found</h3>
        <p style="color:#64748B;margin-bottom:20px;">Try adjusting your filters or check back later.</p>
        <a href="/projects" style="background:var(--red);color:#fff;padding:10px 24px;border-radius:10px;text-decoration:none;font-weight:700;">View All Projects</a>
      </div>
      @endforelse
    </div>

    <!-- PAGINATION -->
    @if($projects->lastPage() > 1)
    <div class="pj-pagination">
      @if($projects->onFirstPage())
        <span class="pj-page-btn" style="opacity:.4;">‹</span>
      @else
        <a href="{{ $projects->previousPageUrl() }}" class="pj-page-btn">‹</a>
      @endif

      @foreach($projects->getUrlRange(max(1,$projects->currentPage()-2), min($projects->lastPage(),$projects->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="pj-page-btn {{ $page==$projects->currentPage()?'active':'' }}">{{ $page }}</a>
      @endforeach

      @if($projects->hasMorePages())
        <a href="{{ $projects->nextPageUrl() }}" class="pj-page-btn">›</a>
      @else
        <span class="pj-page-btn" style="opacity:.4;">›</span>
      @endif
    </div>
    @endif
  </div>
</section>

@endsection
@section('script')
<script>
function setView(v) {
  const grid = document.getElementById('pjGrid');
  const btnG = document.getElementById('btnGrid');
  const btnL = document.getElementById('btnList');
  if (v === 'list') {
    grid.classList.add('list-view');
    btnL.classList.add('active'); btnG.classList.remove('active');
  } else {
    grid.classList.remove('list-view');
    btnG.classList.add('active'); btnL.classList.remove('active');
  }
  localStorage.setItem('pj_view', v);
}
// Restore view preference
const savedView = localStorage.getItem('pj_view');
if (savedView) setView(savedView);
</script>
@endsection
