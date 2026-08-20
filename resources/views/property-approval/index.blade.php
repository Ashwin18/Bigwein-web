@extends('layouts.main')
@section('title') Approval Inbox @endsection
@section('page-title')
<div class="page-title approval-page-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="mb-1"><i class="bi bi-patch-check-fill me-2 text-danger"></i>Approval Inbox</h4>
            <div class="text-muted small">Review owner-submitted listings before they go live.</div>
        </div>
        <a href="{{ url('property') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="bi bi-list-ul me-1"></i> All Properties</a>
    </div>
</div>
@endsection

@section('content')
<style>
.approval-shell{--bw-red:#ef3d3d;--bw-ink:#111827;--bw-muted:#64748b;--bw-border:#e5e7eb;--bw-bg:#f7f9fc}
.approval-kpi{background:#fff;border:1px solid var(--bw-border);border-radius:16px;padding:18px 20px;text-decoration:none;color:inherit;display:block;box-shadow:0 6px 22px rgba(15,23,42,.05);transition:.18s ease}
.approval-kpi:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.08);color:inherit}
.approval-kpi.active{border-color:var(--bw-red);box-shadow:0 0 0 3px rgba(239,61,61,.08)}
.approval-kpi .value{font-size:30px;font-weight:800;line-height:1}.approval-kpi .label{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--bw-muted);margin-top:7px}
.approval-toolbar{background:#fff;border:1px solid var(--bw-border);border-radius:16px;padding:14px;box-shadow:0 6px 22px rgba(15,23,42,.04)}
.approval-card{background:#fff;border:1px solid var(--bw-border);border-radius:18px;overflow:hidden;box-shadow:0 7px 24px rgba(15,23,42,.05);height:100%;transition:.18s ease}
.approval-card:hover{transform:translateY(-2px);box-shadow:0 14px 32px rgba(15,23,42,.09)}
.approval-cover{height:170px;background:#eef2f7;position:relative;overflow:hidden}.approval-cover img{width:100%;height:100%;object-fit:cover}.approval-cover .placeholder{height:100%;display:flex;align-items:center;justify-content:center;font-size:42px;color:#94a3b8}
.approval-status{position:absolute;left:12px;top:12px;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:800;background:#fff;box-shadow:0 3px 12px rgba(0,0,0,.12)}
.approval-card-body{padding:16px}.approval-title{font-size:16px;font-weight:800;color:var(--bw-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.approval-meta{font-size:12px;color:var(--bw-muted)}
.approval-price{font-size:19px;font-weight:900;color:var(--bw-red)}.approval-owner{border-top:1px solid #f1f5f9;padding-top:12px;margin-top:12px;display:flex;align-items:center;gap:9px}.owner-avatar{width:34px;height:34px;border-radius:50%;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-weight:800}
.approval-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px;margin-top:14px}.approval-actions .btn{font-size:12px;font-weight:700;border-radius:9px;padding:8px 7px}
.empty-approval{background:#fff;border:1px dashed #cbd5e1;border-radius:18px;padding:55px 20px;text-align:center;color:#64748b}.empty-approval i{font-size:44px;color:#cbd5e1}
@media(max-width:767px){.approval-actions{grid-template-columns:1fr}.approval-cover{height:190px}}
</style>

<section class="section approval-shell">
    <div class="row g-3 mb-4">
        @foreach([
          ['pending','Pending Review',$pending,'bi-hourglass-split'],
          ['approved','Approved',$approved,'bi-check-circle-fill'],
          ['rejected','Rejected',$rejected,'bi-x-circle-fill'],
        ] as $tab)
        <div class="col-md-4">
            <a class="approval-kpi {{ $status===$tab[0] ? 'active' : '' }}" href="{{ url('property-approval?status='.$tab[0]) }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div><div class="value">{{ $tab[2] }}</div><div class="label">{{ $tab[1] }}</div></div>
                    <i class="bi {{ $tab[3] }} fs-2 {{ $tab[0]==='pending'?'text-warning':($tab[0]==='approved'?'text-success':'text-danger') }}"></i>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <form class="approval-toolbar mb-4" method="GET" action="{{ url('property-approval') }}">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="row g-2 align-items-center">
            <div class="col-lg-6"><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search title, city, owner name or email"></div></div>
            <div class="col-lg-3"><select name="category_id" class="form-select"><option value="">All Categories</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected((string)request('category_id')===(string)$cat->id)>{{ $cat->category }}</option>@endforeach</select></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-danger flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button><a class="btn btn-outline-secondary" href="{{ url('property-approval?status='.$status) }}">Reset</a></div>
        </div>
    </form>

    @if($items->count())
    <div class="row g-3">
        @foreach($items as $item)
        @php
            $titleBase = 'images/'.trim(config('global.PROPERTY_TITLE_IMG_PATH','property_title_img/'),'/');
            $titleUrl = !empty($item->title_image) ? url($titleBase.'/'.$item->title_image) : null;
            $ownerName = $item->owner_name ?: 'Owner';
        @endphp
        <div class="col-xl-4 col-lg-6">
            <div class="approval-card">
                <a href="{{ url('property-approval/'.$item->id.'/detail') }}" class="text-decoration-none">
                    <div class="approval-cover">
                        @if($titleUrl)<img src="{{ $titleUrl }}" alt="{{ $item->title }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><div class="placeholder" style="display:none"><i class="bi bi-building"></i></div>@else<div class="placeholder"><i class="bi bi-building"></i></div>@endif
                        <span class="approval-status {{ $item->request_status==='pending'?'text-warning':($item->request_status==='approved'?'text-success':'text-danger') }}">{{ ucfirst($item->request_status) }}</span>
                    </div>
                </a>
                <div class="approval-card-body">
                    <div class="d-flex justify-content-between gap-2 align-items-start">
                        <div class="min-w-0"><div class="approval-title" title="{{ $item->title }}">{{ $item->title }}</div><div class="approval-meta mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $item->city ?: 'City not set' }}{{ $item->state?', '.$item->state:'' }}</div></div>
                        <div class="approval-price">₹{{ number_format($item->price) }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-light text-dark border">{{ $item->category_name ?: 'Uncategorised' }}</span>
                        <span class="badge bg-light text-dark border">{{ $item->propery_type==0?'For Sale':'For Rent' }}</span>
                        @if($item->sub_type)<span class="badge bg-light text-dark border">{{ $item->sub_type }}</span>@endif
                        <span class="badge bg-light text-dark border"><i class="bi bi-images me-1"></i>{{ $item->gallery_count }}</span>
                        <span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-text me-1"></i>{{ $item->document_count }}</span>
                    </div>
                    <div class="approval-owner"><div class="owner-avatar">{{ strtoupper(substr($ownerName,0,1)) }}</div><div><div class="fw-bold small">{{ $ownerName }}</div><div class="approval-meta">{{ $item->owner_email ?: 'No email' }} · {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</div></div></div>
                    <div class="approval-actions">
                        <a href="{{ url('property-approval/'.$item->id.'/detail') }}" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Review</a>
                        @if($item->request_status==='pending')
                        <button type="button" class="btn btn-success" onclick="approveProperty({{ $item->id }}, this)"><i class="bi bi-check-lg me-1"></i>Approve</button>
                        <button type="button" class="btn btn-outline-danger" onclick="openReject({{ $item->id }}, @js($item->title))"><i class="bi bi-x-lg me-1"></i>Reject</button>
                        @else
                        <a href="{{ url('property-approval/'.$item->id.'/detail') }}" class="btn btn-outline-secondary" style="grid-column:span 2"><i class="bi bi-info-circle me-1"></i>View Decision</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @else
    <div class="empty-approval"><i class="bi bi-inbox"></i><h5 class="mt-3 mb-1 fw-bold">No {{ $status }} listings</h5><div>There are no owner listings in this queue.</div></div>
    @endif
</section>

<div class="modal fade" id="rejectModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0"><div class="modal-header border-0"><h5 class="modal-title fw-bold">Reject Property</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="rejectTitle" class="alert alert-light border small"></div><label class="form-label fw-bold small">Reason for rejection</label><textarea id="rejectReason" class="form-control" rows="4" maxlength="300" placeholder="Explain what the owner needs to correct"></textarea></div><div class="modal-footer border-0"><button class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button id="rejectConfirm" class="btn btn-danger">Reject Listing</button></div></div></div></div>
@endsection

@section('script')
<script>
let rejectId=null;
async function approvalPost(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify(payload)});
 const data=await r.json(); if(!r.ok) throw new Error(data.message||'Request failed'); return data;
}
async function approveProperty(id,btn){
 if(!confirm('Approve and publish this property?')) return;
 const original=btn?btn.innerHTML:''; if(btn){btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';}
 try{const d=await approvalPost('{{ url('property-approval/approve') }}',{id});toastr.success(d.message||'Approved');setTimeout(()=>location.reload(),700)}catch(e){toastr.error(e.message)}finally{if(btn){btn.disabled=false;btn.innerHTML=original}}
}
function openReject(id,title){rejectId=id;document.getElementById('rejectTitle').textContent=title;document.getElementById('rejectReason').value='';bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectModal')).show();}
document.getElementById('rejectConfirm').addEventListener('click',async function(){const reason=document.getElementById('rejectReason').value.trim();if(!reason){toastr.warning('Please enter a rejection reason.');return;}this.disabled=true;try{const d=await approvalPost('{{ url('property-approval/reject') }}',{id:rejectId,reason});toastr.success(d.message||'Rejected');bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();setTimeout(()=>location.reload(),700)}catch(e){toastr.error(e.message)}finally{this.disabled=false}});
</script>
@endsection
