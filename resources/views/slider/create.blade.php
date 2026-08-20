@extends('layouts.main')
@section('title') {{ isset($slider) ? "Edit Banner" : "Add Banner" }} @endsection
@section('page-title')
<div class="page-title">
  <div class="row"><div class="col-12 col-md-6">
    <h4><i class="bi bi-image me-2" style="color:#e30620;"></i>{{ isset($slider) ? "Edit Banner" : "Add New Banner" }}</h4>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('slider.index') }}">Banners</a></li>
      <li class="breadcrumb-item active">{{ isset($slider) ? "Edit" : "Add" }}</li>
    </ol></nav>
  </div></div>
</div>
@endsection
@section('content')
<section class="section">
<div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);max-width:700px;">
  <div class="card-header bg-white" style="border-radius:16px 16px 0 0;padding:16px 22px;border-bottom:1px solid #F1F5F9;">
    <h6 style="font-weight:800;margin:0;">Banner Details</h6>
  </div>
  <div class="card-body p-4">
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ isset($slider) ? route('slider.update',$slider->id) : route('slider.store') }}" enctype="multipart/form-data">
      @csrf @if(isset($slider)) @method('PUT') @endif

      <div class="mb-3">
        <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block;">
          Banner Image {{ isset($slider) ? "(leave blank to keep current)" : "*" }}
        </label>
        @if(isset($slider) && ($slider->web_image || $slider->image))
          @php $prev = $slider->web_image ?: $slider->image; @endphp
          <img src="{{ url('images/slider/'.$prev) }}" style="width:100%;max-height:200px;object-fit:cover;border-radius:10px;margin-bottom:10px;" onerror="this.style.display='none'"/>
        @endif
        <input type="file" name="web_image" class="form-control" accept="image/*" style="border-radius:10px;" {{ isset($slider) ? "" : "required" }}/>
        <small class="text-muted">Recommended: 1920×600px landscape · JPG/PNG/WebP · Max 3MB</small>
      </div>

      <div class="mb-3">
        <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Banner Title / Label</label>
        <input type="text" name="type" class="form-control" style="border-radius:10px;"
          value="{{ old('type', $slider->type ?? '') }}" placeholder="e.g. Hero Banner, Summer Offer"/>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Sequence (display order)</label>
          <input type="number" name="sequence" class="form-control" style="border-radius:10px;"
            value="{{ old('sequence', $slider->sequence ?? 1) }}" min="1"/>
        </div>
        <div class="col-md-6 mb-3">
          <label style="font-size:12px;font-weight:600;margin-bottom:6px;display:block;">Click Link (optional)</label>
          <input type="text" name="link" class="form-control" style="border-radius:10px;"
            value="{{ old('link', $slider->link ?? '') }}" placeholder="/properties"/>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px;">
        <button type="submit" class="btn btn-danger" style="border-radius:10px;font-weight:700;">
          <i class="bi bi-cloud-check me-1"></i>{{ isset($slider) ? "Update Banner" : "Upload Banner" }}
        </button>
        <a href="{{ route('slider.index') }}" class="btn btn-light" style="border-radius:10px;">Cancel</a>
      </div>
    </form>
  </div>
</div>
</section>
@endsection