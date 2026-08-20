@extends('frontend.owner.layouts.app')
@section('title','My Projects')
@section('page-title','My Projects')
@section('page-bread','Builder / Developer · Projects, approvals and enquiries')

@push('styles')
<style>
.bpstats{display:grid;grid-template-columns:repeat(5,1fr);gap:9px;margin-bottom:14px}.bpstat{background:#fff;border:1px solid #e7ebf1;border-radius:13px;padding:13px}.bpstat strong{font-size:20px;color:#172033;display:block}.bpstat span{font-size:9px;color:#94a3b8;text-transform:uppercase;font-weight:800}
.bprow{background:#fff;border:1px solid #e7ebf1;border-radius:14px;padding:15px;display:grid;grid-template-columns:84px minmax(0,1fr) auto;gap:14px;align-items:center}.bpimg{width:84px;height:70px;border-radius:10px;background:#eef2f7;overflow:hidden}.bpimg img{width:100%;height:100%;object-fit:cover}.bptitle{font-weight:800;color:#172033}.bpmeta{font-size:10px;color:#64748b;margin-top:4px}.bpn{font-size:10px;color:#9a3412;background:#fff7ed;padding:6px 8px;border-radius:8px;margin-top:6px}.bpactions{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end}.bpa{padding:8px 10px;border-radius:8px;text-decoration:none;font-size:10px;font-weight:800;background:#172033;color:#fff}.bpa.live{background:#159653}.bpa.edit{background:#e5343a}
@media(max-width:800px){.bpstats{grid-template-columns:repeat(2,1fr)}.bprow{grid-template-columns:1fr}.bpimg{width:100%;height:160px}.bpactions{justify-content:flex-start}}
</style>
@endpush

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px">
  <div><strong>Development Portfolio</strong><div style="font-size:11px;color:#94a3b8">Manage approvals, live projects and buyer enquiries.</div></div>
  <div style="display:flex;gap:7px"><a class="post-btn" style="background:#172033" href="{{ url('/owner/project-enquiries') }}"><i class="fa-solid fa-message"></i> Project Enquiries</a><a class="post-btn" href="{{ url('/owner/post-project') }}"><i class="fa-solid fa-plus"></i> Add Project</a></div>
</div>

@if(session('success'))<div style="background:#ecfdf5;color:#047857;padding:10px;border-radius:10px;margin-bottom:12px;font-size:11px">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;color:#b91c1c;padding:10px;border-radius:10px;margin-bottom:12px;font-size:11px">{{ session('error') }}</div>@endif

<div class="bpstats">
 @foreach([['total','Total Projects'],['live','Live'],['pending','Pending'],['changes','Changes Requested'],['enquiries','Enquiries']] as [$k,$label])
 <div class="bpstat"><strong>{{ $stats[$k] ?? 0 }}</strong><span>{{ $label }}</span></div>
 @endforeach
</div>

<div style="display:grid;gap:10px">
@forelse($projects as $p)
@php
 $imgPath=$p->image ? asset('images/'.trim(config('global.PROJECT_IMG_PATH','project/'),'/').'/'.$p->image) : null;
@endphp
<div class="bprow">
 <div class="bpimg">@if($imgPath)<img src="{{ $imgPath }}">@endif</div>
 <div>
  <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap"><span class="bptitle">{{ $p->title }}</span><span style="font-size:9px;padding:5px 8px;border-radius:999px;background:#f1f5f9">{{ ucwords(str_replace('_',' ',$p->request_status)) }}</span>@if($p->status==1)<span style="font-size:9px;color:#047857">● Active</span>@endif</div>
  <div class="bpmeta">{{ $p->reference_no }} · {{ $p->city }}, {{ $p->state }} · {{ $p->project_subtype ?: $p->type }} · {{ $p->available_units ?? '—' }}/{{ $p->total_units ?? '—' }} units</div>
  <div class="bpmeta"><strong>{{ $p->enquiry_count }}</strong> enquiries @if($p->new_enquiry_count) · <strong style="color:#e5343a">{{ $p->new_enquiry_count }} new</strong>@endif · RERA: {{ $p->rera_number ?: 'Not provided' }}</div>
  @if(in_array($p->request_status,['changes_requested','rejected']) && $p->admin_remarks)<div class="bpn"><strong>Admin:</strong> {{ $p->admin_remarks }}</div>@endif
 </div>
 <div class="bpactions">
   @if(in_array($p->request_status,['changes_requested','rejected']))
     <a class="bpa edit" href="{{ url('/owner/project/'.$p->id.'/edit') }}"><i class="fa-solid fa-pen"></i> Edit & Resubmit</a>
   @elseif($p->request_status==='pending')
     <span style="font-size:10px;color:#64748b"><i class="fa-solid fa-lock"></i> Locked during review</span>
   @elseif($p->request_status==='approved' && $p->status==1)
     <a class="bpa live" target="_blank" href="{{ url('/project/'.$p->slug_id) }}">View Live</a>
   @endif
 </div>
</div>
@empty
<div style="background:#fff;border:1px dashed #dce3eb;border-radius:14px;padding:50px;text-align:center;color:#94a3b8">No projects submitted yet.</div>
@endforelse
</div>
<div style="margin-top:15px">{{ $projects->links() }}</div>
@endsection