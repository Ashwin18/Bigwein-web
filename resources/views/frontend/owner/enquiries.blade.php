@extends('frontend.owner.layouts.app')
@section('title','Enquiries')
@section('page-title','Buyer Enquiries')
@section('page-bread','People interested in your properties')

@section('content')

@if($enquiries->count())
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <div style="font-size:14px;color:var(--gray);">
    <strong style="color:var(--navy);">{{ $enquiries->total() }}</strong> total enquiries across your properties
  </div>
</div>

<div class="card">
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg);">
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">#</th>
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Buyer</th>
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Property</th>
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Contact</th>
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Date</th>
          <th style="text-align:left;padding:13px 18px;font-size:11px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.5px;">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($enquiries as $enq)
        <tr style="border-top:1px solid var(--border);transition:background .15s;" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
          <td style="padding:13px 18px;color:var(--gray2);">{{ $loop->index + 1 }}</td>
          <td style="padding:13px 18px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--blue),#60A5FA);color:#fff;font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                {{ strtoupper(substr($enq->buyer_name, 0, 1)) }}
              </div>
              <div>
                <div style="font-weight:700;color:var(--navy);">{{ $enq->buyer_name }}</div>
                <div style="font-size:11px;color:var(--gray2);">{{ $enq->buyer_email }}</div>
              </div>
            </div>
          </td>
          <td style="padding:13px 18px;">
            <div style="font-weight:600;color:var(--navy);max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $enq->property_title }}</div>
            <div style="font-size:11px;color:var(--gray2);">{{ $enq->property_city }}</div>
          </td>
          <td style="padding:13px 18px;">
            <div style="display:flex;gap:8px;align-items:center;">
              @if($enq->buyer_mobile)
              <a href="tel:{{ $enq->buyer_mobile }}" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:var(--green-light);color:var(--green);border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;">
                <i class="fa-solid fa-phone"></i> Call
              </a>
              <a href="https://wa.me/91{{ preg_replace('/\D/','',$enq->buyer_mobile) }}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:rgba(37,211,102,.1);color:#25D366;border-radius:8px;font-size:11px;font-weight:700;text-decoration:none;">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp
              </a>
              @endif
            </div>
          </td>
          <td style="padding:13px 18px;color:var(--gray2);white-space:nowrap;">
            {{ \Carbon\Carbon::parse($enq->created_at)->format('d M Y') }}<br>
            <span style="font-size:11px;">{{ \Carbon\Carbon::parse($enq->created_at)->format('h:i A') }}</span>
          </td>
          <td style="padding:13px 18px;">
            <span class="pli-status status-{{ $enq->status === 'active' ? 'active' : 'pending' }}">
              {{ ucfirst($enq->status ?? 'New') }}
            </span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div style="display:flex;justify-content:center;margin-top:24px;">
  {{ $enquiries->links() }}
</div>

@else
<div style="text-align:center;padding:64px 20px;background:#fff;border-radius:var(--r-xl);border:1px solid var(--border);">
  <i class="fa-solid fa-message" style="font-size:56px;color:var(--gray3);margin-bottom:16px;display:block;"></i>
  <h3 style="font-size:20px;font-weight:800;color:var(--navy);margin-bottom:8px;">No Enquiries Yet</h3>
  <p style="font-size:14px;color:var(--gray);">When buyers enquire about your properties, they'll appear here with full contact details.</p>
  <a href="{{ url('/owner/post-property') }}" class="btn btn-red" style="margin-top:20px;">
    <i class="fa-solid fa-plus"></i> Post a Property to Get Enquiries
  </a>
</div>
@endif
@endsection
