@extends('layouts.main')

@section('title', 'Property Review')

@section('content')
@php
    $status = strtolower((string) ($prop->request_status ?? 'pending'));
    $statusClass = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
    $currency = system_setting('currency_symbol') ?: '₹';
    $listingFor = ((string) ($prop->propery_type ?? '0')) === '1' ? 'Rent' : 'Sale';
    $location = trim(implode(', ', array_filter([$prop->city ?? null, $prop->state ?? null])));
@endphp

<style>
.bw-review-wrap{padding:20px 8px 40px}.bw-review-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;margin-bottom:18px}.bw-review-title h2{font-size:26px;font-weight:800;margin:0 0 7px;color:#172033}.bw-review-meta{display:flex;gap:8px;flex-wrap:wrap}.bw-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border-radius:999px;font-size:12px;font-weight:700;background:#f3f6fa;color:#526079}.bw-pill.warning{background:#fff7dc;color:#9a6b00}.bw-pill.success{background:#eaf8ef;color:#23834a}.bw-pill.danger{background:#fdecef;color:#bb3142}.bw-actions{display:flex;gap:8px;flex-wrap:wrap}.bw-btn{border:0;border-radius:10px;padding:10px 15px;font-weight:700;cursor:pointer;text-decoration:none!important;display:inline-flex;align-items:center;gap:7px}.bw-btn-light{background:#fff;border:1px solid #dce3ec;color:#334155}.bw-btn-success{background:#36a463;color:white}.bw-btn-danger{background:#ef4141;color:white}.bw-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.75fr);gap:18px}.bw-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:18px;box-shadow:0 7px 24px rgba(15,23,42,.035)}.bw-card-head{padding:15px 18px;border-bottom:1px solid #edf1f6;font-size:15px;font-weight:800;color:#182235}.bw-card-body{padding:18px}.bw-cover{width:100%;max-height:420px;object-fit:cover;border-radius:12px;background:#f4f6f9}.bw-empty-media{min-height:220px;border:1px dashed #d7dee8;border-radius:12px;display:flex;align-items:center;justify-content:center;text-align:center;color:#8793a5;background:#f8fafc}.bw-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}.bw-gallery-item{height:130px;border-radius:11px;overflow:hidden;border:1px solid #e5eaf1;background:#f7f9fc;display:flex;align-items:center;justify-content:center;color:#94a3b8;text-align:center}.bw-gallery-item img{width:100%;height:100%;object-fit:cover}.bw-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.bw-info{padding:12px 14px;border-radius:11px;background:#f7f9fc;border:1px solid #edf1f5}.bw-info small{display:block;text-transform:uppercase;letter-spacing:.06em;font-size:10px;font-weight:800;color:#8a96a8;margin-bottom:4px}.bw-info strong{display:block;color:#1f2937;font-size:13px;word-break:break-word}.bw-span-2{grid-column:1/-1}.bw-list{display:flex;flex-direction:column;gap:9px}.bw-list-row{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #edf1f5}.bw-list-row:last-child{border-bottom:0}.bw-list-row span:first-child{color:#6b778c}.bw-list-row strong{color:#1f2937;text-align:right}.bw-doc{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #edf1f5}.bw-doc:last-child{border-bottom:0}.bw-note{padding:12px;border-radius:10px;background:#fff5f5;color:#9b2c2c;border:1px solid #ffdede}.bw-reject-box{display:none;margin-top:12px}.bw-reject-box textarea{width:100%;min-height:95px;border:1px solid #d9e0e9;border-radius:10px;padding:10px;resize:vertical}.bw-action-status{font-size:12px;margin-top:10px;min-height:18px}.bw-muted{color:#8a96a8}.bw-desc{white-space:pre-line;color:#4b5563;line-height:1.65}.bw-sticky{position:sticky;top:95px}
@media(max-width:991px){.bw-grid{grid-template-columns:1fr}.bw-sticky{position:static}}
@media(max-width:640px){.bw-info-grid{grid-template-columns:1fr}.bw-span-2{grid-column:auto}.bw-review-title h2{font-size:21px}}
</style>

<div class="bw-review-wrap">
    <div class="bw-review-head">
        <div class="bw-review-title">
            <a href="{{ url('property?request_status=pending') }}" class="bw-btn bw-btn-light" style="margin-bottom:12px;">
                <i class="bi bi-arrow-left"></i> Back to Pending Listings
            </a>
            <h2>{{ $prop->title ?: 'Untitled Property' }}</h2>
            <div class="bw-review-meta">
                <span class="bw-pill {{ $statusClass }}">{{ ucfirst($status) }}</span>
                <span class="bw-pill"><i class="bi bi-geo-alt"></i> {{ $location ?: 'Location not provided' }}</span>
                <span class="bw-pill">{{ $listingFor }}</span>
                <span class="bw-pill">ID #{{ $prop->id }}</span>
            </div>
        </div>

        @if($status === 'pending')
            <div class="bw-actions">
                <button type="button" class="bw-btn bw-btn-success" onclick="bwApprovalAction('approved')"><i class="bi bi-check-lg"></i> Approve & Publish</button>
                <button type="button" class="bw-btn bw-btn-danger" onclick="document.getElementById('bwRejectBox').style.display='block'"><i class="bi bi-x-lg"></i> Reject</button>
            </div>
        @endif
    </div>

    <div class="bw-grid">
        <div>
            <div class="bw-card">
                <div class="bw-card-head">Property Media</div>
                <div class="bw-card-body">
                    @if(!empty($prop->title_image_exists) && !empty($prop->title_image_url))
                        <img src="{{ $prop->title_image_url }}" class="bw-cover" alt="Property cover">
                    @else
                        <div class="bw-empty-media"><div><i class="bi bi-image" style="font-size:28px;"></i><br>Cover image unavailable</div></div>
                    @endif

                    <div style="height:16px;"></div>

                    @if($gallery->count())
                        <div class="bw-gallery">
                            @foreach($gallery as $img)
                                @if(!empty($img->exists) && !empty($img->image_url))
                                    <a class="bw-gallery-item" href="{{ $img->image_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $img->image_url }}" alt="Gallery image" onerror="this.parentElement.innerHTML='Image unavailable';">
                                    </a>
                                @else
                                    <div class="bw-gallery-item"><div><i class="bi bi-image"></i><br>File unavailable<br><small>{{ $img->image ?? '' }}</small></div></div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="bw-muted">No gallery images uploaded.</div>
                    @endif
                </div>
            </div>

            <div class="bw-card">
                <div class="bw-card-head">Listing Details</div>
                <div class="bw-card-body">
                    <div class="bw-info-grid">
                        <div class="bw-info"><small>Category</small><strong>{{ $prop->category_name ?: 'Not specified' }}</strong></div>
                        <div class="bw-info"><small>Listing For</small><strong>{{ $listingFor }}</strong></div>
                        <div class="bw-info"><small>Subtype</small><strong>{{ $prop->sub_type ?: 'Not specified' }}</strong></div>
                        <div class="bw-info"><small>Commercial Type</small><strong>{{ $prop->commercial_type ?: 'Not applicable' }}</strong></div>
                        <div class="bw-info"><small>Property Status</small><strong>{{ $prop->prop_status ?: 'Not specified' }}</strong></div>
                        <div class="bw-info"><small>Price</small><strong>{{ $currency }}{{ number_format((float) ($prop->price ?? 0), 0) }}</strong></div>
                        <div class="bw-info"><small>Total Area</small><strong>{{ $prop->total_area ?: '—' }}</strong></div>
                        <div class="bw-info"><small>Carpet Area</small><strong>{{ $prop->carpet_area ?: '—' }}</strong></div>
                        <div class="bw-info"><small>City</small><strong>{{ $prop->city ?: '—' }}</strong></div>
                        <div class="bw-info"><small>State</small><strong>{{ $prop->state ?: '—' }}</strong></div>
                        <div class="bw-info bw-span-2"><small>Admin-only Address</small><strong>{{ $prop->address ?: ($prop->client_address ?: 'Not provided') }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="bw-card">
                <div class="bw-card-head">Description</div>
                <div class="bw-card-body bw-desc">{{ $prop->description ?: 'No description provided.' }}</div>
            </div>

            <div class="bw-card">
                <div class="bw-card-head">Parameters / Amenities</div>
                <div class="bw-card-body">
                    @if($parameters->count())
                        <div class="bw-list">
                            @foreach($parameters as $parameter)
                                <div class="bw-list-row"><span>{{ $parameter->name ?: 'Parameter' }}</span><strong>{{ is_scalar($parameter->value) ? $parameter->value : '—' }}</strong></div>
                            @endforeach
                        </div>
                    @else
                        <div class="bw-muted">No property parameters were saved.</div>
                    @endif
                </div>
            </div>

            <div class="bw-card">
                <div class="bw-card-head">Nearby Facilities</div>
                <div class="bw-card-body">
                    @if($facilities->count())
                        <div class="bw-list">
                            @foreach($facilities as $facility)
                                <div class="bw-list-row"><span>{{ $facility->name ?: 'Facility' }}</span><strong>{{ $facility->distance !== null && $facility->distance !== '' ? $facility->distance : '—' }}</strong></div>
                            @endforeach
                        </div>
                    @else
                        <div class="bw-muted">No nearby facilities were saved.</div>
                    @endif
                </div>
            </div>

            <div class="bw-card">
                <div class="bw-card-head">Documents</div>
                <div class="bw-card-body">
                    @if($documents->count())
                        @foreach($documents as $document)
                            <div class="bw-doc">
                                <div><strong>{{ $document->name ?: 'Document' }}</strong><div class="bw-muted" style="font-size:11px;">{{ strtoupper($document->type ?: '') }}</div></div>
                                @if(!empty($document->exists) && !empty($document->document_url))
                                    <a href="{{ $document->document_url }}" target="_blank" rel="noopener" class="bw-btn bw-btn-light">View</a>
                                @else
                                    <span class="bw-muted">File unavailable</span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="bw-muted">No documents uploaded.</div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="bw-sticky">
                <div class="bw-card">
                    <div class="bw-card-head">Owner Details</div>
                    <div class="bw-card-body">
                        <div class="bw-list">
                            <div class="bw-list-row"><span>Name</span><strong>{{ $prop->owner_name ?: 'Owner unavailable' }}</strong></div>
                            <div class="bw-list-row"><span>Type</span><strong>{{ $prop->owner_type ?: '—' }}</strong></div>
                            <div class="bw-list-row"><span>Company</span><strong>{{ $prop->company_name ?: '—' }}</strong></div>
                            <div class="bw-list-row"><span>Email</span><strong>{{ $prop->owner_email ?: '—' }}</strong></div>
                            <div class="bw-list-row"><span>Mobile</span><strong>{{ $prop->owner_mobile ?: '—' }}</strong></div>
                        </div>
                    </div>
                </div>

                @if(!empty($rejectReason))
                    <div class="bw-card"><div class="bw-card-head">Previous Rejection Reason</div><div class="bw-card-body"><div class="bw-note">{{ $rejectReason }}</div></div></div>
                @endif

                @if($status === 'pending')
                    <div class="bw-card">
                        <div class="bw-card-head">Approval Action</div>
                        <div class="bw-card-body">
                            <button type="button" class="bw-btn bw-btn-success" style="width:100%;justify-content:center;" onclick="bwApprovalAction('approved')"><i class="bi bi-check-lg"></i> Approve & Publish</button>
                            <button type="button" class="bw-btn bw-btn-danger" style="width:100%;justify-content:center;margin-top:9px;" onclick="document.getElementById('bwRejectBox').style.display='block'"><i class="bi bi-x-lg"></i> Reject Listing</button>

                            <div id="bwRejectBox" class="bw-reject-box">
                                <textarea id="bwRejectReason" maxlength="300" placeholder="Enter rejection reason for the owner..."></textarea>
                                <button type="button" class="bw-btn bw-btn-danger" style="width:100%;justify-content:center;margin-top:8px;" onclick="bwApprovalAction('rejected')">Confirm Rejection</button>
                            </div>

                            <div id="bwActionStatus" class="bw-action-status"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function bwApprovalAction(status) {
    var reason = '';
    var statusBox = document.getElementById('bwActionStatus');

    if (status === 'rejected') {
        reason = (document.getElementById('bwRejectReason').value || '').trim();
        if (!reason) {
            statusBox.innerHTML = '<span style="color:#dc2626">Please enter a rejection reason.</span>';
            return;
        }
    }

    if (!window.confirm(status === 'approved' ? 'Approve and publish this property?' : 'Reject this property?')) {
        return;
    }

    statusBox.innerHTML = '<span style="color:#64748b">Processing...</span>';

    fetch('{{ url('update-property-request-status') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: {{ (int) $prop->id }},
            request_status: status,
            reject_reason: reason
        })
    })
    .then(function(response) {
        return response.json().catch(function() { return {}; }).then(function(data) {
            if (!response.ok || data.error === true) {
                throw new Error(data.message || 'Unable to update property status.');
            }
            return data;
        });
    })
    .then(function() {
        statusBox.innerHTML = '<span style="color:#16803a">Updated successfully. Redirecting...</span>';
        setTimeout(function() {
            window.location.href = '{{ url('property?request_status=pending') }}';
        }, 600);
    })
    .catch(function(error) {
        statusBox.innerHTML = '<span style="color:#dc2626">' + error.message + '</span>';
    });
}
</script>
@endsection
