@extends('layouts.main')
@section('title') Owner KYC Verification @endsection

@section('css')
<style>
.bw-kyc-admin{padding:16px 20px 40px!important;display:flex;flex-direction:column;gap:16px}
.bw-kyc-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.bw-kyc-stat{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #edf1f6;border-radius:14px;padding:14px 15px;text-decoration:none!important;color:#0f172a;box-shadow:0 6px 18px rgba(15,23,42,.035);transition:.15s}
.bw-kyc-stat:hover{border-color:#fecaca;transform:translateY(-1px)}.bw-kyc-stat.active{border-color:#ef3f45;box-shadow:0 8px 22px rgba(239,63,69,.08)}
.bw-kyc-stat-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:17px;flex:0 0 auto}
.bw-kyc-stat strong{display:block;font-size:20px;line-height:1;color:#0f172a}.bw-kyc-stat span{display:block;margin-top:4px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
.bw-kyc-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#fff;border:1px solid #edf1f6;border-radius:14px;padding:13px}
.bw-kyc-search{display:flex;gap:8px;flex:1;min-width:280px}.bw-kyc-search input,.bw-kyc-search select{height:40px;border:1px solid #dce3eb;border-radius:9px;background:#fff;padding:0 11px;font-size:12px;color:#334155;outline:none}.bw-kyc-search input{flex:1;min-width:200px}.bw-kyc-search input:focus,.bw-kyc-search select:focus{border-color:#ef3f45;box-shadow:0 0 0 3px rgba(239,63,69,.07)}
.bw-kyc-search button{height:40px;border:0;border-radius:9px;background:#ef3f45;color:#fff;padding:0 16px;font-size:11px;font-weight:800}.bw-kyc-reset{height:40px;display:inline-flex;align-items:center;padding:0 13px;border:1px solid #dce3eb;border-radius:9px;background:#fff;color:#64748b;text-decoration:none!important;font-size:11px;font-weight:700}
.bw-kyc-inbox{display:grid;gap:12px}
.bw-kyc-card{background:#fff;border:1px solid #e6ebf2;border-radius:16px;overflow:hidden;box-shadow:0 7px 20px rgba(15,23,42,.035)}
.bw-kyc-card-top{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,.8fr) auto;gap:16px;align-items:center;padding:16px 17px;border-bottom:1px solid #f0f3f7}
.bw-owner{display:flex;gap:12px;align-items:center;min-width:0}.bw-owner-avatar{width:42px;height:42px;border-radius:12px;background:#fff1f2;color:#ef3f45;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;flex:0 0 auto}.bw-owner strong{font-size:13px;color:#0f172a}.bw-owner small{display:block;color:#8490a3;font-size:10px;margin-top:2px}.bw-owner-type{display:inline-flex;margin-top:5px;padding:3px 8px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:9px;font-weight:800}
.bw-kyc-location{display:flex;gap:18px;flex-wrap:wrap}.bw-kyc-mini small{display:block;color:#94a3b8;text-transform:uppercase;font-size:9px;font-weight:800;letter-spacing:.05em}.bw-kyc-mini strong{display:block;color:#334155;font-size:11px;margin-top:3px}
.bw-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:10px;font-weight:800;white-space:nowrap}.bw-status.submitted,.bw-status.under_review{background:#eff6ff;color:#1d4ed8}.bw-status.approved{background:#ecfdf5;color:#047857}.bw-status.changes_requested{background:#fffbeb;color:#a16207}.bw-status.rejected{background:#fef2f2;color:#b91c1c}
.bw-kyc-card-body{display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,.42fr);gap:18px;padding:16px 17px}
.bw-doc-panel{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.bw-doc{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #edf1f6;border-radius:11px;padding:11px 12px;background:#fafbfc}.bw-doc-left{display:flex;gap:9px;align-items:center}.bw-doc-icon{width:32px;height:32px;border-radius:9px;background:#fff1f2;color:#ef3f45;display:flex;align-items:center;justify-content:center}.bw-doc small{display:block;color:#94a3b8;font-size:9px}.bw-doc strong{display:block;color:#334155;font-size:11px;margin-top:2px}.bw-doc a{display:inline-flex;align-items:center;gap:5px;padding:7px 9px;border-radius:8px;background:#fff;color:#ef3f45;border:1px solid #fecaca;text-decoration:none!important;font-size:10px;font-weight:800}
.bw-kyc-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-top:10px}.bw-detail{padding:9px 11px;background:#f8fafc;border-radius:9px;border:1px solid #f0f3f7}.bw-detail small{display:block;color:#94a3b8;font-size:9px;font-weight:800;text-transform:uppercase}.bw-detail strong{display:block;color:#334155;font-size:11px;margin-top:3px;word-break:break-word}
.bw-kyc-actionbox{border-left:1px solid #eef2f6;padding-left:17px}.bw-kyc-actionbox label{display:block;color:#64748b;font-size:10px;font-weight:800;margin-bottom:6px}.bw-kyc-actionbox textarea{width:100%;min-height:72px;border:1px solid #dce3eb;border-radius:9px;padding:9px;font-size:11px;resize:vertical;outline:none}.bw-kyc-actionbox textarea:focus{border-color:#ef3f45;box-shadow:0 0 0 3px rgba(239,63,69,.06)}.bw-action-btns{display:grid;gap:7px;margin-top:8px}.bw-action-btn{width:100%;border:0;border-radius:9px;padding:9px 10px;color:#fff;font-size:10px;font-weight:800;cursor:pointer}.bw-approve{background:#2f9e5b}.bw-change{background:#f59e0b}.bw-reject{background:#e44747}.bw-action-note{font-size:10px;line-height:1.5;color:#64748b;padding:10px 11px;border-radius:9px;background:#f8fafc}
.bw-empty{background:#fff;border:1px dashed #dce3eb;border-radius:16px;padding:55px 20px;text-align:center;color:#94a3b8}.bw-empty i{display:block;font-size:34px;color:#dce3eb;margin-bottom:8px}
.bw-pagination{background:#fff;border:1px solid #edf1f6;border-radius:12px;padding:10px 14px}
@media(max-width:1100px){.bw-kyc-summary{grid-template-columns:repeat(2,1fr)}.bw-kyc-card-body{grid-template-columns:1fr}.bw-kyc-actionbox{border-left:0;border-top:1px solid #eef2f6;padding-left:0;padding-top:14px}}
@media(max-width:800px){.bw-kyc-admin{padding:12px!important}.bw-kyc-card-top{grid-template-columns:1fr}.bw-kyc-search{width:100%;flex-wrap:wrap}.bw-kyc-search input{width:100%;flex-basis:100%}.bw-doc-panel,.bw-kyc-detail-grid{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.bw-kyc-summary,.bw-doc-panel,.bw-kyc-detail-grid{grid-template-columns:1fr}.bw-kyc-search select{flex:1}.bw-kyc-reset{flex:1;justify-content:center}}
</style>
@endsection

@section('page-title')
<div class="page-title">
  <div class="row align-items-center">
    <div class="col-12 col-md-8">
      <h4 style="display:flex;align-items:center;gap:8px;margin:0;">
        <i class="bi bi-person-vcard" style="color:#E5343A;"></i> Owner KYC Verification
      </h4>
      <div style="font-size:11px;color:#94A3B8;margin-top:4px;">
        Review Seller / Owner and Builder / Developer identity verification before posting access is enabled.
      </div>
    </div>
    <div class="col-12 col-md-4 d-flex justify-content-md-end mt-2 mt-md-0">
      <a href="{{ url('owner-management') }}" class="btn btn-sm" style="border:1px solid #DCE3EB;background:#fff;color:#475569;font-size:11px;font-weight:700;">
        <i class="bi bi-people"></i> Owner Management
      </a>
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="bw-kyc-admin">

  <div class="bw-kyc-summary">
    <a href="{{ url('owner-kyc-admin?status=submitted') }}" class="bw-kyc-stat {{ $status==='submitted'?'active':'' }}">
      <div class="bw-kyc-stat-icon" style="background:#fff7ed;color:#c2410c;"><i class="bi bi-hourglass-split"></i></div>
      <div><strong>{{ $counts['submitted'] }}</strong><span>Pending Review</span></div>
    </a>
    <a href="{{ url('owner-kyc-admin?status=approved') }}" class="bw-kyc-stat {{ $status==='approved'?'active':'' }}">
      <div class="bw-kyc-stat-icon" style="background:#ecfdf5;color:#047857;"><i class="bi bi-patch-check"></i></div>
      <div><strong>{{ $counts['approved'] }}</strong><span>Approved</span></div>
    </a>
    <a href="{{ url('owner-kyc-admin?status=changes_requested') }}" class="bw-kyc-stat {{ $status==='changes_requested'?'active':'' }}">
      <div class="bw-kyc-stat-icon" style="background:#fffbeb;color:#a16207;"><i class="bi bi-pencil-square"></i></div>
      <div><strong>{{ $counts['changes_requested'] ?? 0 }}</strong><span>Changes Requested</span></div>
    </a>
    <a href="{{ url('owner-kyc-admin?status=rejected') }}" class="bw-kyc-stat {{ $status==='rejected'?'active':'' }}">
      <div class="bw-kyc-stat-icon" style="background:#fef2f2;color:#b91c1c;"><i class="bi bi-x-octagon"></i></div>
      <div><strong>{{ $counts['rejected'] }}</strong><span>Rejected</span></div>
    </a>
  </div>

  <form class="bw-kyc-toolbar" method="GET" action="{{ url('owner-kyc-admin') }}">
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="bw-kyc-search">
      <input type="search" name="search" value="{{ request('search') }}" placeholder="Search owner name, email or mobile...">
      <select name="owner_type">
        <option value="">All Owner Types</option>
        <option value="seller" {{ request('owner_type')==='seller'?'selected':'' }}>Seller / Owner</option>
        <option value="builder" {{ request('owner_type')==='builder'?'selected':'' }}>Builder / Developer</option>
      </select>
      <button type="submit"><i class="bi bi-search"></i> Search</button>
      <a class="bw-kyc-reset" href="{{ url('owner-kyc-admin?status='.$status) }}">Reset</a>
    </div>
    <a class="bw-kyc-reset {{ $status==='all'?'active':'' }}" href="{{ url('owner-kyc-admin?status=all') }}">
      <i class="bi bi-list-ul me-1"></i> View All
    </a>
  </form>

  <div class="bw-kyc-inbox">
    @forelse($rows as $r)
      @php
        $isBuilder = $r->owner_type === 'builder';
        $statusLabel = $r->status === 'submitted' || $r->status === 'under_review'
          ? 'Under Review'
          : ucwords(str_replace('_',' ',$r->status));
        $location = trim(implode(', ', array_filter([$r->city ?? null, $r->state ?? null])));
      @endphp

      <article class="bw-kyc-card">
        <div class="bw-kyc-card-top">
          <div class="bw-owner">
            <div class="bw-owner-avatar">{{ strtoupper(substr($r->name ?: 'O',0,1)) }}</div>
            <div style="min-width:0;">
              <strong>{{ $r->name ?: 'Owner' }}</strong>
              <small>{{ $r->email ?: 'No email' }} · {{ $r->mobile ?: 'No mobile' }}</small>
              <span class="bw-owner-type">{{ $isBuilder ? 'Builder / Developer' : 'Seller / Owner' }}</span>
            </div>
          </div>

          <div class="bw-kyc-location">
            <div class="bw-kyc-mini">
              <small>Location</small>
              <strong>{{ $location ?: 'Not provided' }}</strong>
            </div>
            <div class="bw-kyc-mini">
              <small>Submitted</small>
              <strong>{{ $r->submitted_at ? \Carbon\Carbon::parse($r->submitted_at)->format('d M Y, h:i A') : '—' }}</strong>
            </div>
          </div>

          <span class="bw-status {{ $r->status }}"><i class="bi bi-circle-fill" style="font-size:6px;"></i> {{ $statusLabel }}</span>
        </div>

        <div class="bw-kyc-card-body">
          <div>
            <div class="bw-doc-panel">
              <div class="bw-doc">
                <div class="bw-doc-left">
                  <div class="bw-doc-icon"><i class="bi bi-person-vcard"></i></div>
                  <div><small>Aadhaar Front</small><strong>{{ $r->aadhaar_front ? 'Document submitted' : 'Not available' }}</strong></div>
                </div>
                @if($r->aadhaar_front)
                  <a target="_blank" rel="noopener" href="{{ asset('images/customer_kyc/'.$r->customer_id.'/'.$r->aadhaar_front) }}">View <i class="bi bi-box-arrow-up-right"></i></a>
                @endif
              </div>

              <div class="bw-doc">
                <div class="bw-doc-left">
                  <div class="bw-doc-icon"><i class="bi bi-person-vcard-fill"></i></div>
                  <div><small>Aadhaar Back</small><strong>{{ $r->aadhaar_back ? 'Document submitted' : 'Optional / not submitted' }}</strong></div>
                </div>
                @if($r->aadhaar_back)
                  <a target="_blank" rel="noopener" href="{{ asset('images/customer_kyc/'.$r->customer_id.'/'.$r->aadhaar_back) }}">View <i class="bi bi-box-arrow-up-right"></i></a>
                @endif
              </div>
            </div>

            <div class="bw-kyc-detail-grid">
              <div class="bw-detail">
                <small>Aadhaar</small>
                <strong>XXXX XXXX {{ substr((string)$r->aadhaar_number,-4) }}</strong>
              </div>
              <div class="bw-detail">
                <small>Owner Type</small>
                <strong>{{ $isBuilder ? 'Builder / Developer' : 'Seller / Owner' }}</strong>
              </div>
              <div class="bw-detail">
                <small>Company</small>
                <strong>{{ $r->company_name ?: 'Not applicable' }}</strong>
              </div>
            </div>

            @if(!empty($r->remarks))
              <div style="margin-top:10px;padding:9px 11px;border-radius:9px;background:#fff7ed;color:#9a3412;font-size:10px;">
                <strong>Admin Remarks:</strong> {{ $r->remarks }}
              </div>
            @endif
          </div>

          <div class="bw-kyc-actionbox">
            @if($r->status==='submitted' || $r->status==='under_review')
              <form method="POST" action="{{ url('/owner-kyc-admin/'.$r->id.'/status') }}">
                @csrf
                <label>Review remarks</label>
                <textarea name="remarks" placeholder="Required when requesting changes or rejecting..."></textarea>
                <div class="bw-action-btns">
                  <button class="bw-action-btn bw-approve" name="status" value="approved" onclick="return confirm('Approve this owner KYC and enable posting access?')">
                    <i class="bi bi-check-circle"></i> Approve KYC
                  </button>
                  <button class="bw-action-btn bw-change" name="status" value="changes_requested">
                    <i class="bi bi-pencil-square"></i> Request Changes
                  </button>
                  <button class="bw-action-btn bw-reject" name="status" value="rejected" onclick="return confirm('Reject this KYC submission?')">
                    <i class="bi bi-x-circle"></i> Reject
                  </button>
                </div>
              </form>
            @elseif($r->status==='approved')
              <div class="bw-action-note" style="background:#ecfdf5;color:#047857;">
                <strong><i class="bi bi-patch-check"></i> KYC Approved</strong><br>
                Posting access is enabled for this owner.
              </div>
            @elseif($r->status==='changes_requested')
              <div class="bw-action-note" style="background:#fffbeb;color:#a16207;">
                <strong><i class="bi bi-pencil-square"></i> Waiting for Owner</strong><br>
                The owner can edit the requested KYC details and resubmit.
              </div>
            @else
              <div class="bw-action-note" style="background:#fef2f2;color:#b91c1c;">
                <strong><i class="bi bi-x-octagon"></i> KYC Rejected</strong><br>
                The owner can correct the KYC details and submit again.
              </div>
            @endif
          </div>
        </div>
      </article>
    @empty
      <div class="bw-empty">
        <i class="bi bi-person-check"></i>
        <strong style="display:block;color:#475569;font-size:13px;">No KYC records found</strong>
        <span style="font-size:11px;">There are no owner KYC submissions matching this filter.</span>
      </div>
    @endforelse
  </div>

  @if($rows->hasPages())
    <div class="bw-pagination">{{ $rows->links() }}</div>
  @endif
</div>
@endsection
