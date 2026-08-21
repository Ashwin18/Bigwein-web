@extends('layouts.main')
@section('title','Bigwein Admin Dashboard')

@section('page-title')
<div class="bw-dashboard-header">
  <div><h3>Bigwein Admin Dashboard</h3><p>Overview of approvals, listings, users and recent activity</p></div>
  <div class="bw-dashboard-header__meta"><span><i class="bi bi-calendar3"></i> {{ now()->format('d M Y') }}</span><a href="{{ url('home') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Refresh</a></div>
</div>
@endsection

@section('content')
@php
  $dc = $dashboardData['counts'];
  $count = fn($group,$status) => (int)($dc[$group][$status] ?? 0);
  $kycPending = $count('kyc','submitted') + $count('kyc','under_review');
  $canProperty = has_permissions('read','property');
  $canCustomer = has_permissions('read','customer');
  $canProject = has_permissions('read','project');
  $isSuperAdmin = (int)(auth()->user()->type ?? 1) === 0;
@endphp
<div class="bw-command-dashboard">
  <section>
    <div class="bw-dashboard-section-title"><div><span>Action Required</span><small>Queues that need attention now</small></div><i class="bi bi-exclamation-circle"></i></div>
    <div class="bw-attention-grid">
      @foreach([
        ['Pending KYC',$kycPending,'requests',url('owner-kyc-admin'),'bi-person-vcard',$canCustomer || $isSuperAdmin],
        ['Pending Properties',$count('property','pending'),'listings',url('property-approval'),'bi-buildings',$canProperty],
        ['Pending Projects',$count('project','pending'),'projects',url('project?request_status=pending'),'bi-kanban',$canProject],
        ['Builder Projects',$dc['builder_projects_pending'],'projects',url('builder-project-approvals'),'bi-building-check',$canProject],
        ['Pending Businesses',$count('business','pending'),'listings',url('business-approvals'),'bi-briefcase',$isSuperAdmin],
      ] as [$label,$value,$unit,$url,$icon,$allowed])
        <article class="bw-attention-card"><span class="bw-attention-card__icon"><i class="bi {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value) }}</strong><span>{{ $unit }}</span></div>@if($allowed)<a href="{{ $url }}">Review <i class="bi bi-arrow-right"></i></a>@endif</article>
      @endforeach
    </div>
  </section>

  <section>
    <div class="bw-dashboard-section-title"><div><span>KPI Summary</span><small>Current platform inventory</small></div></div>
    <div class="bw-dashboard-kpis">
      @foreach([
        ['Total Properties',array_sum($dc['property']),'bi-houses'],['Approved Properties',$count('property','approved'),'bi-patch-check'],
        ['Total Projects',array_sum($dc['project']),'bi-buildings'],['Total Businesses',array_sum($dc['business']),'bi-shop'],
        ['Total Customers',$dc['total_customers'],'bi-people'],['Owners / Sellers',$dc['total_owners'],'bi-person-badge'],
        ['Builders / Developers',$dc['total_builders'],'bi-building-gear'],['Enquiries / Leads',$dc['total_enquiries'],'bi-chat-dots']
      ] as [$label,$value,$icon])
        <div class="bw-dashboard-kpi"><i class="bi {{ $icon }}"></i><div><strong>{{ number_format($value) }}</strong><span>{{ $label }}</span></div></div>
      @endforeach
    </div>
  </section>

  <section>
    <div class="bw-dashboard-section-title"><div><span>Approval Queues</span><small>Status distribution across review workflows</small></div></div>
    <div class="bw-approval-overview">
      @foreach([['Properties','property','bi-buildings'],['Projects','project','bi-kanban'],['KYC','kyc','bi-person-check'],['Businesses','business','bi-briefcase']] as [$label,$key,$icon])
        @php $pending=$key==='kyc'?$kycPending:$count($key,'pending'); $approved=$count($key,'approved'); $rejected=$count($key,'rejected'); $total=max(1,$pending+$approved+$rejected); @endphp
        <article class="bw-overview-card"><header><i class="bi {{ $icon }}"></i><strong>{{ $label }}</strong><span>{{ number_format($pending+$approved+$rejected) }}</span></header><div class="bw-overview-bar"><span style="width:{{ ($pending/$total)*100 }}%" class="pending"></span><span style="width:{{ ($approved/$total)*100 }}%" class="approved"></span><span style="width:{{ ($rejected/$total)*100 }}%" class="rejected"></span></div><div class="bw-overview-counts"><span><i class="pending"></i>Pending <b>{{ $pending }}</b></span><span><i class="approved"></i>Approved <b>{{ $approved }}</b></span><span><i class="rejected"></i>Rejected <b>{{ $rejected }}</b></span></div></article>
      @endforeach
    </div>
  </section>

  <section>
    <div class="bw-dashboard-section-title"><div><span>Recent Pending Items</span><small>Latest submissions awaiting review</small></div></div>
    <div class="bw-dashboard-panels">
      @foreach([
        ['Recent Pending KYC',$dashboardData['recent_kyc'],'bi-person-vcard','owner-kyc-admin','kyc'],
        ['Recent Pending Properties',$dashboardData['recent_properties'],'bi-house-check','property-approval','property'],
        ['Recent Pending Projects',$dashboardData['recent_projects'],'bi-kanban','project?request_status=pending','project'],
        ['Recent Pending Businesses',$dashboardData['recent_businesses'],'bi-briefcase','business-approvals','business']
      ] as [$title,$rows,$icon,$allUrl,$kind])
        @php $moduleAllowed=$kind==='kyc'?($canCustomer||$isSuperAdmin):($kind==='property'?$canProperty:($kind==='project'?$canProject:$isSuperAdmin)); @endphp
        <article class="bw-dashboard-panel"><header><div><i class="bi {{ $icon }}"></i><strong>{{ $title }}</strong></div>@if($moduleAllowed)<a href="{{ url($allUrl) }}">View all</a>@endif</header><div class="bw-dashboard-list">
          @forelse($rows as $row)
            @php
              $name=$kind==='kyc'?$row->name:$row->title;
              $meta=$kind==='kyc'?ucwords(str_replace('_',' ',$row->owner_type ?: 'Customer')):trim(($row->owner_name ?: 'Owner unavailable').' · '.(($row->category_name ?? null) ?: ($row->city ?: '—')));
              $date=($row->submitted_at ?? $row->created_at ?? null) ? \Carbon\Carbon::parse($row->submitted_at ?? $row->created_at)->format('d M Y') : '—';
              $reviewUrl=$kind==='property'?url('property-approval/'.$row->id.'/detail'):($kind==='business'?url('business-approvals/'.$row->id):url($allUrl));
            @endphp
            <div class="bw-dashboard-list-row"><span class="bw-dashboard-list-icon"><i class="bi {{ $icon }}"></i></span><div><strong>{{ $name }}</strong><small>{{ $meta }} · {{ $date }}</small></div>@include('components.admin.status-badge',['status'=>'pending'])@if($moduleAllowed)<a href="{{ $reviewUrl }}">Review</a>@endif</div>
          @empty<div class="bw-dashboard-empty"><i class="bi bi-check-circle"></i> Nothing pending</div>@endforelse
        </div></article>
      @endforeach
    </div>
  </section>

  <section class="bw-dashboard-bottom">
    <article class="bw-dashboard-panel"><header><div><i class="bi bi-person-plus"></i><strong>Recent Registrations</strong></div>@if($canCustomer)<a href="{{ url('customer') }}">View all</a>@endif</header><div class="bw-dashboard-list">
      @forelse($dashboardData['recent_users'] as $user)<div class="bw-dashboard-list-row"><span class="bw-dashboard-avatar">{{ strtoupper(substr($user->name ?: 'U',0,1)) }}</span><div><strong>{{ $user->name }}</strong><small>{{ ucwords(str_replace('_',' ',$user->owner_type ?: 'Customer')) }} · {{ $user->mobile ?: $user->email }}</small></div>@include('components.admin.status-badge',['status'=>$user->kyc_status ?: 'pending','prefix'=>'KYC '])<span class="bw-dashboard-date">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '—' }}</span></div>@empty<div class="bw-dashboard-empty">No registrations found</div>@endforelse
    </div></article>
    <article class="bw-dashboard-panel bw-quick-panel"><header><div><i class="bi bi-lightning-charge"></i><strong>Quick Actions</strong></div></header><div class="bw-dashboard-quick-actions">
      @if($canCustomer || $isSuperAdmin)<a href="{{ url('owner-kyc-admin') }}"><i class="bi bi-person-vcard"></i>Review KYC</a>@endif
      @if($canProperty)<a href="{{ url('property-approval') }}"><i class="bi bi-house-check"></i>Review Properties</a>@endif
      @if($canProject)<a href="{{ url('project?request_status=pending') }}"><i class="bi bi-kanban"></i>Review Projects</a><a href="{{ url('builder-project-approvals') }}"><i class="bi bi-building-check"></i>Builder Projects</a>@endif
      @if($isSuperAdmin)<a href="{{ url('business-approvals') }}"><i class="bi bi-briefcase"></i>Review Businesses</a>@endif
      @if(has_permissions('create','property'))<a href="{{ url('property/create') }}"><i class="bi bi-plus-circle"></i>Add Property</a>@endif
      @if(has_permissions('create','project'))<a href="{{ url('project/create') }}"><i class="bi bi-plus-square"></i>Add Project</a>@endif
      @if(has_permissions('read','facility'))<a href="{{ url('amenities') }}"><i class="bi bi-stars"></i>Manage Amenities</a>@endif
      @if(has_permissions('read','categories'))<a href="{{ url('categories') }}"><i class="bi bi-tags"></i>Manage Categories</a>@endif
    </div></article>
  </section>
</div>
@endsection
