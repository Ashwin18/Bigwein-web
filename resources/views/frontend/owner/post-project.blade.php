@extends('frontend.layouts.app')
@section('title','Submit Your Project — BigWein')
@section('content')

<div style="background:linear-gradient(135deg,#0a0a14,#1a1025);padding:40px 0;">
  <div class="container">
    <div style="font-size:13px;color:rgba(255,255,255,.5);margin-bottom:8px;">
      <a href="/" style="color:inherit;text-decoration:none;">Home</a> › Owner Dashboard › Post Project
    </div>
    <h1 style="color:#fff;font-size:28px;font-weight:800;margin:0 0 6px;">Submit a Project</h1>
    <p style="color:rgba(255,255,255,.65);margin:0;">Builder / Developer — submit your residential or commercial project for listing</p>
  </div>
</div>

<div class="container" style="padding:40px 0 60px;max-width:800px;">

  {{-- Nav links --}}
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
    <a href="/owner/dashboard"     style="font-size:13px;color:#64748B;text-decoration:none;padding:6px 14px;border:1px solid #E2E8F0;border-radius:8px;">Dashboard</a>
    <a href="/owner/my-properties" style="font-size:13px;color:#64748B;text-decoration:none;padding:6px 14px;border:1px solid #E2E8F0;border-radius:8px;">My Properties</a>
    <a href="/owner/post-property" style="font-size:13px;color:#64748B;text-decoration:none;padding:6px 14px;border:1px solid #E2E8F0;border-radius:8px;">Post Property</a>
    <span style="font-size:13px;color:#E5343A;font-weight:700;padding:6px 14px;border:1px solid #FECDD3;border-radius:8px;background:#FFF1F3;">Post Project</span>
  </div>

  <form id="postProjectForm" enctype="multipart/form-data">
    @csrf

    {{-- Section 1: Project Info --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">1</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Project Information</h3>
      </div>
      <div class="row g-3">
        <div class="col-12">
          <label class="pf-label">Project Name *</label>
          <input type="text" name="title" class="pf-inp" placeholder="e.g. Green Valley Residency Phase 2" required/>
        </div>
        <div class="col-md-6">
          <label class="pf-label">Category *</label>
          <select name="category_id" class="pf-inp" required>
            <option value="">Select category</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->category }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="pf-label">Project Status *</label>
          <select name="project_status" class="pf-inp" required>
            <option value="New Launch">New Launch</option>
            <option value="Under Construction">Under Construction</option>
            <option value="Ready to Move">Ready to Move</option>
          </select>
        </div>
        <div class="col-12">
          <label class="pf-label">Project Description *</label>
          <textarea name="description" class="pf-inp" rows="5" required style="resize:vertical;"
            placeholder="Describe the project — highlights, amenities, configuration (1BHK, 2BHK, 3BHK), launch price, possession date, number of units..."></textarea>
        </div>
      </div>
    </div>

    {{-- Section 2: Location --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:16px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">2</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Location</h3>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="pf-label">City *</label>
          <input type="text" name="city" class="pf-inp" placeholder="e.g. Chennai" required/>
        </div>
        <div class="col-md-6">
          <label class="pf-label">State</label>
          <input type="text" name="state" class="pf-inp" placeholder="e.g. Tamil Nadu"/>
        </div>
        <div class="col-12">
          <label class="pf-label">Full Address / Locality</label>
          <input type="text" name="address" class="pf-inp" placeholder="Street, Area, Landmark, City"/>
        </div>
        <div class="col-md-6">
          <label class="pf-label">Latitude (optional)</label>
          <input type="number" name="latitude" class="pf-inp" placeholder="e.g. 13.0827" step="any"/>
        </div>
        <div class="col-md-6">
          <label class="pf-label">Longitude (optional)</label>
          <input type="number" name="longitude" class="pf-inp" placeholder="e.g. 80.2707" step="any"/>
        </div>
      </div>
    </div>

    {{-- Section 3: Media --}}
    <div style="background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:24px;margin-bottom:24px;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <div style="width:32px;height:32px;background:#E5343A;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">3</div>
        <h3 style="font-size:16px;font-weight:700;margin:0;">Project Image</h3>
      </div>
      <input type="file" name="image" class="form-control" accept="image/*" style="border-radius:10px;"/>
      <small class="text-muted">Upload a main banner image for the project (landscape, min 1200×600px recommended)</small>
    </div>

    {{-- Submission note --}}
    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#166534;">
      <i class="fa-solid fa-circle-info me-2"></i>
      Your project will be reviewed by the BigWein team and published within <strong>24 hours</strong>. You'll be able to track its status from your dashboard.
    </div>

    <button type="submit" id="submitBtn"
      style="width:100%;background:#E5343A;color:#fff;border:none;border-radius:12px;padding:14px;font-size:16px;font-weight:700;cursor:pointer;">
      <i class="fa-solid fa-paper-plane me-2"></i> Submit Project for Review
    </button>
  </form>
</div>

<style>
.pf-label{font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px;}
.pf-inp{width:100%;border:1px solid #E2E8F0;border-radius:10px;padding:10px 14px;font-size:13px;outline:none;box-sizing:border-box;color:#0F172A;}
.pf-inp:focus{border-color:#E5343A;}
</style>

@endsection
@section('js')
<script>
document.getElementById('postProjectForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  var btn = document.getElementById('submitBtn');
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Submitting...';
  btn.disabled = true;
  try {
    var fd = new FormData(this);
    var res = await fetch('/owner/post-project', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      body: fd
    });
    var d = await res.json();
    if (d.success) {
      alert(d.message || 'Project submitted! It will be reviewed within 24 hours.');
      window.location.href = '/owner/dashboard';
    } else {
      alert(d.message || 'Submission failed. Please check all required fields.');
    }
  } catch(ex) {
    alert('Something went wrong. Please try again.');
  }
  btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i> Submit Project for Review';
  btn.disabled = false;
});
</script>
@endsection
