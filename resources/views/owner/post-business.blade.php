@extends('frontend.layouts.app')
@section('title','List Your Business For Sale — BigWein')
@section('content')
<div style="background:linear-gradient(135deg,#0a0a14,#1a1025);padding:40px 0;">
  <div class="container">
    <h1 style="color:#fff;font-size:28px;font-weight:800;margin:0 0 6px;">List Your Business</h1>
    <p style="color:rgba(255,255,255,.65);margin:0;">Zero brokerage · Reach serious buyers directly</p>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;max-width:800px;">
  @if(!session('bw_customer'))
  <div style="background:#FFF1F3;border:1px solid #FECDD3;border-radius:12px;padding:16px 20px;margin-bottom:20px;">
    <i class="fa-solid fa-lock" style="color:#E5343A;margin-right:8px;"></i>
    Please <a href="/user/login" style="color:#E5343A;font-weight:700;">sign in</a> to list your business.
  </div>
  @endif

  <form id="bfsForm" enctype="multipart/form-data">
    @csrf

    {{-- Step 1: Business Info --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">1</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Business Information</h3>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <label class="bfs-label">Business Name *</label>
          <input type="text" name="title" class="bfs-inp" placeholder="e.g. Chennai South Indian Restaurant" required/>
        </div>
        <div class="col-md-6">
          <label class="bfs-label">Business Type *</label>
          <select name="business_type" class="bfs-inp" required>
            <option value="">Select type</option>
            @foreach(['Restaurant','Retail Store','Franchise','Hotel / Hospitality','Manufacturing','IT / Tech','Healthcare','Education','Salon / Beauty','Gym / Fitness','Bakery / Cafe','Pharmacy','Travel Agency','Other'] as $bt)
            <option value="{{ $bt }}">{{ $bt }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="bfs-label">Asking Price (₹) *</label>
          <input type="number" name="price" class="bfs-inp" placeholder="e.g. 5000000" required/>
        </div>
        <div class="col-12">
          <label class="bfs-label">Business Description *</label>
          <textarea name="description" class="bfs-inp" rows="4" placeholder="Describe your business — what it does, how long it has been running, why you are selling..." required style="resize:vertical;"></textarea>
        </div>
      </div>
    </div>

    {{-- Step 2: Location --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">2</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Location</h3>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="bfs-label">City *</label>
          <input type="text" name="city" class="bfs-inp" placeholder="e.g. Chennai" required/>
        </div>
        <div class="col-md-6">
          <label class="bfs-label">State</label>
          <input type="text" name="state" class="bfs-inp" placeholder="e.g. Tamil Nadu"/>
        </div>
        <div class="col-12">
          <label class="bfs-label">Full Address</label>
          <input type="text" name="address" class="bfs-inp" placeholder="Street, Area, Landmark"/>
        </div>
      </div>
    </div>

    {{-- Step 3: Financials --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">3</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Financial Details</h3>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="bfs-label">Monthly Turnover (₹)</label>
          <input type="number" name="turnover" class="bfs-inp" placeholder="Average monthly revenue"/>
        </div>
        <div class="col-md-6">
          <label class="bfs-label">Net Profit Margin (%)</label>
          <input type="number" name="profit_margin" class="bfs-inp" placeholder="e.g. 20" min="0" max="100"/>
        </div>
        <div class="col-12">
          <label class="bfs-label">Reason for Selling</label>
          <select name="reason_selling" class="bfs-inp">
            <option value="">Select reason</option>
            <option>Relocation</option><option>Retirement</option><option>New Business</option>
            <option>Partnership Dispute</option><option>Health Reasons</option><option>Other</option>
          </select>
        </div>
      </div>
    </div>

    {{-- Step 4: Business Details --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">4</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Business Details</h3>
      </div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="bfs-label">Year Established</label>
          <input type="number" name="established_year" class="bfs-inp" placeholder="e.g. 2018" min="1900" max="{{ date('Y') }}"/>
        </div>
        <div class="col-md-4">
          <label class="bfs-label">No. of Employees</label>
          <input type="number" name="employees" class="bfs-inp" placeholder="e.g. 5" min="0"/>
        </div>
        <div class="col-md-4">
          <label class="bfs-label">Premises</label>
          <select name="lease_type" class="bfs-inp">
            <option value="">Select</option>
            <option>Owned</option><option>Leased</option><option>Rented</option>
          </select>
        </div>
        <div class="col-12">
          <label class="bfs-label">What's Included in Sale</label>
          <input type="text" name="includes" class="bfs-inp" placeholder="e.g. Equipment, furniture, staff, brand name, customer database"/>
        </div>
      </div>
    </div>

    {{-- Step 5: Photos --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">5</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Photos</h3>
      </div>
      <input type="file" name="title_image" accept="image/*" class="form-control" style="border-radius:10px;"/>
      <small class="text-muted">Upload a clear photo of your business (storefront, interior, etc.)</small>
    </div>

    <button type="submit" id="bfsSubmit"
      style="width:100%;background:#E5343A;color:#fff;border:none;border-radius:12px;padding:14px;font-size:16px;font-weight:700;cursor:pointer;">
      <i class="fa-solid fa-paper-plane me-2"></i> Submit Business Listing
    </button>
    <p style="text-align:center;font-size:12px;color:#94A3B8;margin-top:10px;">Our team will review and publish within 24 hours</p>
  </form>
</div>

<style>
.bfs-label{font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;}
.bfs-inp{width:100%;border:1px solid #E2E8F0;border-radius:10px;padding:10px 14px;font-size:13px;outline:none;box-sizing:border-box;color:#0F172A;}
.bfs-inp:focus{border-color:#E5343A;}
</style>

@endsection
@section('js')
<script>
document.getElementById('bfsForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  var btn = document.getElementById('bfsSubmit');
  btn.textContent = 'Submitting...'; btn.disabled = true;
  try {
    var fd = new FormData(this);
    var res = await fetch('/business/store', {
      method:'POST',
      headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
      body: fd
    });
    var d = await res.json();
    if(d.success) {
      alert('Business listed successfully! It will be reviewed and published within 24 hours.');
      window.location.href = '/businesses';
    } else {
      alert(d.message || 'Submission failed. Please try again.');
    }
  } catch(ex) { alert('Something went wrong. Please try again.'); }
  btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> Submit Business Listing';
  btn.disabled = false;
});
</script>
@endsection
