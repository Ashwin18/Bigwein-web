@extends('frontend.owner.layouts.app')
@php
$isEdit=!empty($project);
$amenities=$details && $details->amenities ? (json_decode($details->amenities,true)?:[]) : [];
$spec=$details && $details->specifications ? (json_decode($details->specifications,true)?:[]) : [];
$near=$details && $details->nearby_places ? (json_decode($details->nearby_places,true)?:[]) : [];
@endphp
@section('title',$isEdit?'Edit Project':'Post Project')
@section('page-title',$isEdit?'Edit & Resubmit Project':'Add New Project')
@section('page-bread','Builder / Developer · Admin approval required')

@push('styles')
<style>
.pp{max-width:1100px;margin:auto}.hero{background:linear-gradient(135deg,#172033,#34223b);color:#fff;padding:22px;border-radius:18px;margin-bottom:13px}.hero h2{margin:0;font-size:22px}.hero p{font-size:11px;color:#cbd5e1;margin:6px 0 0}.notice{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;border-radius:11px;padding:10px;margin-bottom:12px;font-size:11px}
.pc{background:#fff;border:1px solid #e6eaf1;border-radius:15px;padding:18px;margin-bottom:12px}.pt{font-size:14px;font-weight:800;color:#172033;margin-bottom:13px}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:11px}.full{grid-column:1/-1}.f label{display:block;font-size:9px;color:#64748b;text-transform:uppercase;font-weight:800;margin-bottom:5px}.f input,.f select,.f textarea{width:100%;box-sizing:border-box;border:1px solid #dce3eb;border-radius:9px;padding:9px 10px;font-size:11px}.f textarea{min-height:80px}.checks{display:flex;gap:7px;flex-wrap:wrap}.check{border:1px solid #e2e8f0;border-radius:999px;padding:7px 9px;font-size:10px}
.unit{display:grid;grid-template-columns:1.2fr repeat(5,1fr) 32px;gap:6px;margin-bottom:7px}.unit input{border:1px solid #dce3eb;border-radius:8px;padding:8px;font-size:10px;width:100%;box-sizing:border-box}.rm{border:0;border-radius:8px;background:#fef2f2;color:#b91c1c}.add{border:1px solid #dce3eb;background:#fff;border-radius:8px;padding:8px 10px;font-size:10px;font-weight:800}.submit{width:100%;border:0;background:#e5343a;color:#fff;border-radius:11px;padding:13px;font-weight:800}.existing{display:flex;gap:8px;flex-wrap:wrap;margin-top:9px}.existing img{width:100px;height:75px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb}
@media(max-width:800px){.grid{grid-template-columns:1fr}.full{grid-column:auto}.unit{grid-template-columns:1fr 1fr}.rm{width:100%}}
</style>
@endpush

@section('content')
<div class="pp">
<div class="hero"><h2><i class="fa-solid fa-city"></i> {{ $isEdit?'Update Project':'Create Builder Project' }}</h2><p>{{ $profile->company_name ?? $owner->company_name }} · Verified Builder / Developer</p></div>
@if($isEdit && $details?->admin_remarks)<div class="notice"><strong>Admin changes requested:</strong> {{ $details->admin_remarks }}</div>@endif

<form method="POST" action="{{ $isEdit?url('/owner/project/'.$project->id.'/update'):url('/owner/post-project') }}" enctype="multipart/form-data">@csrf
<div class="pc"><div class="pt">1 · Basic Project Information</div><div class="grid">
<div class="f full"><label>Project Name *</label><input name="title" value="{{ old('title',$project->title??'') }}" required></div>
<div class="f"><label>Segment *</label><select name="project_segment" required>@foreach(['residential'=>'Residential','commercial'=>'Commercial','mixed'=>'Mixed Use','plotted'=>'Plotted Development'] as $k=>$v)<option value="{{ $k }}" {{ old('project_segment',$details->project_segment??'')===$k?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
<div class="f"><label>Subtype *</label><input name="project_subtype" value="{{ old('project_subtype',$details->project_subtype??'') }}" placeholder="Apartment / Villa / Plot / Office" required></div>
<div class="f"><label>Admin Category *</label><select name="category_id" required><option value="">Select</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ (string)old('category_id',$project->category_id??'')===(string)$c->id?'selected':'' }}>{{ $c->category }}</option>@endforeach</select></div>
<div class="f"><label>Project Status *</label><select name="project_status">@foreach(['Upcoming','New Launch','Under Construction','Ready to Move'] as $v)<option {{ old('project_status',$project->type??'Upcoming')===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
<div class="f"><label>Launch Date</label><input type="date" name="launch_date" value="{{ old('launch_date',$details->launch_date??'') }}"></div><div class="f"><label>Possession Date</label><input type="date" name="possession_date" value="{{ old('possession_date',$details->possession_date??'') }}"></div>
<div class="f full"><label>Description *</label><textarea name="description" required>{{ old('description',$project->description??'') }}</textarea></div>
</div></div>

<div class="pc"><div class="pt">2 · Location & RERA</div><div class="grid">
<div class="f full"><label>Locality / Project Location *</label><input name="location" value="{{ old('location',$project->location??'') }}" required></div>
<div class="f"><label>City *</label><input name="city" value="{{ old('city',$project->city??$owner->city??'') }}" required></div><div class="f"><label>State *</label><input name="state" value="{{ old('state',$project->state??$owner->state??'') }}" required></div>
<div class="f"><label>Country</label><input name="country" value="{{ old('country',$project->country??'India') }}"></div><div class="f"><label>RERA Number</label><input name="rera_number" value="{{ old('rera_number',$details->rera_number??'') }}"></div>
<div class="f"><label>RERA URL</label><input name="rera_url" value="{{ old('rera_url',$details->rera_url??'') }}"></div><div class="f"><label>RERA Certificate</label><input type="file" name="rera_certificate" accept=".jpg,.jpeg,.png,.pdf"></div>
<div class="f"><label>Latitude</label><input name="latitude" value="{{ old('latitude',$project->latitude??'') }}"></div><div class="f"><label>Longitude</label><input name="longitude" value="{{ old('longitude',$project->longitude??'') }}"></div>
</div></div>

<div class="pc"><div class="pt">3 · Project Scale</div><div class="grid">
@foreach([['total_land_area','Total Land Area'],['total_towers','Towers'],['total_blocks','Blocks'],['total_floors','Floors'],['total_units','Total Units'],['available_units','Available Units'],['open_space_percent','Open Space %']] as [$k,$label])<div class="f"><label>{{ $label }}</label><input type="number" step="0.01" name="{{ $k }}" value="{{ old($k,$details->{$k}??'') }}"></div>@endforeach
<div class="f"><label>Area Unit</label><select name="land_area_unit">@foreach(['sqft'=>'Sq.ft','acre'=>'Acre','sqm'=>'Sq.m','cent'=>'Cent'] as $k=>$v)<option value="{{ $k }}" {{ old('land_area_unit',$details->land_area_unit??'sqft')===$k?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
</div></div>

<div class="pc"><div class="pt">4 · Unit Configurations</div><div id="units">
@if(count($units))
@foreach($units as $u)
<div class="unit"><input name="unit_configuration[]" value="{{ $u->configuration }}" placeholder="2 BHK"><input name="unit_carpet_area[]" type="number" step="0.01" value="{{ $u->carpet_area }}" placeholder="Carpet"><input name="unit_built_up_area[]" type="number" step="0.01" value="{{ $u->built_up_area }}" placeholder="Built-up"><input name="unit_starting_price[]" type="number" step="0.01" value="{{ $u->starting_price }}" placeholder="Start ₹"><input name="unit_maximum_price[]" type="number" step="0.01" value="{{ $u->maximum_price }}" placeholder="Max ₹"><input name="unit_available[]" type="number" value="{{ $u->available_units }}" placeholder="Units"><button type="button" class="rm" onclick="removeUnit(this)">×</button></div>
@endforeach
@else
<div class="unit"><input name="unit_configuration[]" placeholder="2 BHK"><input name="unit_carpet_area[]" type="number" step="0.01" placeholder="Carpet"><input name="unit_built_up_area[]" type="number" step="0.01" placeholder="Built-up"><input name="unit_starting_price[]" type="number" step="0.01" placeholder="Start ₹"><input name="unit_maximum_price[]" type="number" step="0.01" placeholder="Max ₹"><input name="unit_available[]" type="number" placeholder="Units"><button type="button" class="rm" onclick="removeUnit(this)">×</button></div>
@endif
</div><button class="add" type="button" onclick="addUnit()">+ Add Configuration</button></div>

<div class="pc"><div class="pt">5 · Amenities</div><div class="checks">@foreach(['Swimming Pool','Gym','Clubhouse','Children Play Area','24/7 Security','CCTV','Power Backup','Lift','Car Parking','Garden','Indoor Games','Jogging Track','EV Charging','Rainwater Harvesting','Solar Power','Senior Citizen Area'] as $a)<label class="check"><input type="checkbox" name="amenities[]" value="{{ $a }}" {{ in_array($a,old('amenities',$amenities))?'checked':'' }}> {{ $a }}</label>@endforeach</div></div>

<div class="pc"><div class="pt">6 · Specifications & Nearby</div><div class="grid">
@foreach(['flooring'=>'Flooring','doors'=>'Doors','windows'=>'Windows','electrical'=>'Electrical','plumbing'=>'Plumbing','kitchen'=>'Kitchen','bathroom'=>'Bathroom','structure'=>'Structure'] as $k=>$v)<div class="f"><label>{{ $v }}</label><input name="specifications[{{ $k }}]" value="{{ old('specifications.'.$k,$spec[$k]??'') }}"></div>@endforeach
@foreach(['school'=>'School','hospital'=>'Hospital','metro'=>'Metro','railway'=>'Railway','airport'=>'Airport','it_park'=>'IT Park','mall'=>'Mall'] as $k=>$v)<div class="f"><label>Nearby {{ $v }}</label><input name="nearby_places[{{ $k }}]" value="{{ old('nearby_places.'.$k,$near[$k]??'') }}" placeholder="Name / distance"></div>@endforeach
</div></div>

<div class="pc"><div class="pt">7 · Gallery, Floor Plans & Media</div><div class="grid">
<div class="f"><label>Cover Image {{ $isEdit?'':'*' }}</label><input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp" {{ $isEdit?'':'required' }}></div>
<div class="f"><label>Gallery Images</label><input type="file" name="gallery[]" multiple accept=".jpg,.jpeg,.png,.webp"></div>
<div class="f"><label>Floor Plan Title</label><input name="floor_plan_title[]" placeholder="2 BHK Floor Plan"></div><div class="f"><label>Floor Plan File</label><input type="file" name="floor_plan_file[]" accept=".jpg,.jpeg,.png,.pdf"></div>
<div class="f full"><label>Video Link</label><input name="video_link" value="{{ old('video_link',$project->video_link??'') }}"></div>
@if($isEdit && count($images))<div class="full existing">@foreach($images as $img)<img src="{{ asset('images/builder_projects/'.$project->id.'/'.$img->image) }}">@endforeach</div>@endif
</div></div>

<div class="pc"><div class="pt">8 · SEO</div><div class="grid"><div class="f"><label>SEO Title</label><input name="meta_title" value="{{ old('meta_title',$project->meta_title??'') }}"></div><div class="f"><label>SEO Keywords</label><input name="meta_keywords" value="{{ old('meta_keywords',$project->meta_keywords??'') }}"></div><div class="f full"><label>SEO Description</label><textarea name="meta_description">{{ old('meta_description',$project->meta_description??'') }}</textarea></div></div></div>
<button class="submit"><i class="fa-solid fa-paper-plane"></i> {{ $isEdit?'Resubmit Project for Approval':'Submit Project for Admin Approval' }}</button>
</form></div>

<script>
function addUnit(){const r=document.querySelector('.unit').cloneNode(true);r.querySelectorAll('input').forEach(i=>i.value='');document.getElementById('units').appendChild(r)}
function removeUnit(b){const rows=document.querySelectorAll('.unit');if(rows.length===1){rows[0].querySelectorAll('input').forEach(i=>i.value='');return}b.closest('.unit').remove()}
</script>
@endsection