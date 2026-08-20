@extends('layouts.main')
@section('title') Homepage Banners @endsection
@section('page-title')
<div class="page-title">
  <div class="row align-items-center">
    <div class="col-12 col-md-6">
      <h4><i class="bi bi-images me-2" style="color:#e30620;"></i>Homepage Banners</h4>
      <nav><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Banners</li>
      </ol></nav>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
      <a href="{{ route('slider.create') }}" class="btn btn-danger" style="border-radius:10px;font-weight:600;">
        <i class="bi bi-plus-circle me-1"></i> Add New Banner
      </a>
    </div>
  </div>
</div>
@endsection
@section('content')
<section class="section">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#1E40AF;">
  <i class="bi bi-info-circle me-1"></i>
  Banners appear on the homepage hero section. Use landscape images (1920×600px recommended). Sequence 1 = first banner.
</div>
@if($sliders->isEmpty())
<div style="text-align:center;padding:60px;background:#fff;border-radius:16px;border:1px solid #F1F5F9;">
  <i class="bi bi-images" style="font-size:48px;color:#E2E8F0;display:block;margin-bottom:16px;"></i>
  <h5 style="color:#374151;margin-bottom:8px;">No banners uploaded yet</h5>
  <p style="color:#94A3B8;margin-bottom:20px;">Upload your first homepage banner image.</p>
  <a href="{{ route('slider.create') }}" class="btn btn-danger" style="border-radius:10px;">
    <i class="bi bi-plus-circle me-1"></i> Add First Banner
  </a>
</div>
@else
<div class="row g-3">
  @foreach($sliders as $slide)
  @php $imgSrc = $slide->web_image ? url('images/slider/'.$slide->web_image) : ($slide->image ? url('images/slider/'.$slide->image) : null); @endphp
  <div class="col-md-4">
    <div style="background:#fff;border:1px solid #F1F5F9;border-radius:14px;overflow:hidden;" onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow=''">
      @if($imgSrc)
        <img src="{{ $imgSrc }}" style="width:100%;height:180px;object-fit:cover;" onerror="this.style.background='#F1F5F9'"/>
      @else
        <div style="width:100%;height:180px;background:#F1F5F9;display:flex;align-items:center;justify-content:center;">
          <i class="bi bi-image" style="font-size:36px;color:#CBD5E1;"></i>
        </div>
      @endif
      <div style="padding:14px 16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
          <div style="font-size:14px;font-weight:700;color:#0F172A;">{{ $slide->type ?: 'Banner #'.$slide->id }}</div>
          <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#DCFCE7;color:#166534;">Active</span>
        </div>
        <div style="font-size:11px;color:#94A3B8;margin-bottom:4px;"><i class="bi bi-sort-numeric-up me-1"></i>Sequence: {{ $slide->sequence }}</div>
        @if($slide->link)<div style="font-size:11px;color:#64748B;margin-bottom:8px;"><i class="bi bi-link-45deg me-1"></i>{{ Str::limit($slide->link,40) }}</div>@endif
        <div style="display:flex;gap:6px;margin-top:10px;">
          <a href="{{ route('slider.edit',$slide->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;flex:1;font-size:12px;">
            <i class="bi bi-pencil me-1"></i>Edit
          </a>
          <form method="POST" action="{{ route('slider.destroy',$slide->id) }}" style="flex:1;" onsubmit="return confirm('Delete this banner?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="border-radius:8px;font-size:12px;">
              <i class="bi bi-trash3 me-1"></i>Delete
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif
</section>
@endsection