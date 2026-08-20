@extends('frontend.owner.layouts.app')
@section('title','Project Enquiries')
@section('page-title','Project Enquiries')
@section('page-bread','Buyer interest across your projects')
@section('content')
<div style="display:grid;gap:10px">
@forelse($rows as $e)
<div style="background:#fff;border:1px solid #e7ebf1;border-radius:14px;padding:15px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px">
 <div><strong>{{ $e->project_title }}</strong><div style="font-size:10px;color:#64748b;margin-top:4px">{{ $e->reference_no }} · Buyer: {{ $e->name }} · {{ $e->mobile }} @if($e->email) · {{ $e->email }} @endif</div><div style="font-size:10px;color:#64748b;margin-top:4px">Configuration: {{ $e->configuration ?: 'Any' }} · Budget: {{ $e->budget ?: 'Not specified' }}</div>@if($e->message)<div style="margin-top:7px;padding:8px;background:#f8fafc;border-radius:8px;font-size:11px">{{ $e->message }}</div>@endif</div>
 <form method="POST" action="{{ url('/owner/project-enquiries/'.$e->id.'/status') }}">@csrf<select name="status" style="border:1px solid #dce3eb;border-radius:8px;padding:8px;font-size:10px"><option value="new" {{ $e->status==='new'?'selected':'' }}>New</option><option value="contacted" {{ $e->status==='contacted'?'selected':'' }}>Contacted</option><option value="closed" {{ $e->status==='closed'?'selected':'' }}>Closed</option></select><button style="border:0;background:#172033;color:#fff;border-radius:8px;padding:8px;margin-left:4px;font-size:10px">Update</button></form>
</div>
@empty
<div style="background:#fff;padding:45px;text-align:center;border-radius:14px;color:#94a3b8">No project enquiries yet.</div>
@endforelse
</div>
<div style="margin-top:15px">{{ $rows->links() }}</div>
@endsection