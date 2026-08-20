@extends('frontend.layouts.app')

@php
    $amenities = json_decode($project->amenities ?? '[]', true) ?: [];
    $specifications = json_decode($project->specifications ?? '{}', true) ?: [];
    $nearbyPlaces = json_decode($project->nearby_places ?? '{}', true) ?: [];

    $cover = $project->image
        ? asset('images/' . trim(config('global.PROJECT_IMG_PATH', 'project/'), '/') . '/' . $project->image)
        : null;
@endphp

@section('title', $project->title)

@push('styles')
<style>
.pd{background:#f7f9fc;padding:24px 0 55px}
.pdwrap{max-width:1180px;margin:auto;padding:0 18px}
.gallery{display:grid;grid-template-columns:2fr 1fr;gap:9px;height:410px}
.gmain,.gthumb{border-radius:17px;overflow:hidden;background:#eef2f7}
.gmain img,.gthumb img{width:100%;height:100%;object-fit:cover}
.gside{display:grid;grid-template-rows:1fr 1fr;gap:9px}
.layout{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:20px;margin-top:18px}
.card{background:#fff;border:1px solid #e6eaf1;border-radius:16px;padding:19px;margin-bottom:13px}
.title{font-size:29px;font-weight:800;color:#172033}
.loc{font-size:12px;color:#64748b;margin-top:6px}
.chips{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}
.chip{font-size:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:999px;padding:6px 8px}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:15px}
.kpi{background:#f8fafc;border-radius:11px;padding:10px}
.kpi small{font-size:8px;color:#94a3b8;text-transform:uppercase}
.kpi strong{display:block;font-size:13px;margin-top:3px}
.sticky{position:sticky;top:90px}
.form input,.form select,.form textarea{width:100%;box-sizing:border-box;border:1px solid #dce3eb;border-radius:9px;padding:9px;margin-bottom:8px;font-size:11px}
.form textarea{min-height:85px;resize:vertical}
.form button{width:100%;border:0;background:#e5343a;color:#fff;border-radius:9px;padding:11px;font-weight:800}
.units{width:100%;border-collapse:collapse;font-size:11px}
.units th,.units td{padding:9px;border-bottom:1px solid #eef1f4;text-align:left}
.amen{display:flex;gap:7px;flex-wrap:wrap}
.amen span{font-size:10px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:999px;padding:7px 9px}
.props{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}
.prop{border:1px solid #e5e7eb;border-radius:11px;padding:11px;text-decoration:none;color:#172033}
.prop strong{font-size:12px}
.prop div{font-size:9px;color:#64748b;margin-top:4px}
.project-empty{display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:12px}
.flash-success{background:#ecfdf5;color:#047857;padding:8px;border-radius:8px;font-size:10px;margin-bottom:8px}
.flash-error{background:#fef2f2;color:#b91c1c;padding:8px;border-radius:8px;font-size:10px;margin-bottom:8px}
@media(max-width:900px){
    .layout{grid-template-columns:1fr}
    .sticky{position:static}
    .gallery{height:330px}
}
@media(max-width:650px){
    .gallery{grid-template-columns:1fr;height:auto}
    .gmain{height:260px}
    .gside{display:none}
    .kpis{grid-template-columns:repeat(2,1fr)}
    .props{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
<div class="pd">
    <div class="pdwrap">

        <div class="gallery">
            <div class="gmain">
                @if($cover)
                    <img src="{{ $cover }}" alt="{{ $project->title }}">
                @else
                    <div class="project-empty">
                        <i class="fa-regular fa-image"></i>&nbsp; No project cover image
                    </div>
                @endif
            </div>

            <div class="gside">
                @for($i = 0; $i < 2; $i++)
                    <div class="gthumb">
                        @if(isset($images[$i]))
                            <img
                                src="{{ asset('images/builder_projects/' . $project->id . '/' . $images[$i]->image) }}"
                                alt="{{ $project->title }} gallery image"
                            >
                        @else
                            <div class="project-empty">
                                <i class="fa-regular fa-image"></i>
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <div class="layout">
            <main>

                <div class="card">
                    <div class="title">{{ $project->title }}</div>

                    <div class="loc">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $project->location }}, {{ $project->city }}, {{ $project->state }}
                    </div>

                    <div class="chips">
                        <span class="chip">{{ $project->type }}</span>

                        @if(!empty($project->project_segment))
                            <span class="chip">{{ ucfirst($project->project_segment) }}</span>
                        @endif

                        @if(!empty($project->rera_number))
                            <span class="chip">RERA {{ $project->rera_number }} ✓</span>
                        @endif
                    </div>

                    <div class="kpis">
                        <div class="kpi">
                            <small>Developer</small>
                            <strong>{{ $project->company_name ?: $project->owner_name }}</strong>
                        </div>

                        <div class="kpi">
                            <small>Total Units</small>
                            <strong>{{ $project->total_units ?: '—' }}</strong>
                        </div>

                        <div class="kpi">
                            <small>Available</small>
                            <strong>{{ $project->available_units ?: '—' }}</strong>
                        </div>

                        <div class="kpi">
                            <small>Views</small>
                            <strong>{{ $project->total_click }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h4>Project Overview</h4>
                    <p style="font-size:12px;line-height:1.7;color:#536075">
                        {{ $project->description }}
                    </p>
                </div>

                @if($units->count())
                    <div class="card">
                        <h4>Configurations & Pricing</h4>

                        <div style="overflow:auto">
                            <table class="units">
                                <thead>
                                    <tr>
                                        <th>Configuration</th>
                                        <th>Carpet</th>
                                        <th>Built-up</th>
                                        <th>Price</th>
                                        <th>Available</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($units as $unit)
                                        <tr>
                                            <td>
                                                <strong>{{ $unit->configuration }}</strong>
                                            </td>

                                            <td>
                                                {{ $unit->carpet_area ?: '—' }}
                                            </td>

                                            <td>
                                                {{ $unit->built_up_area ?: '—' }}
                                            </td>

                                            <td>
                                                @if($unit->starting_price)
                                                    ₹{{ number_format($unit->starting_price) }}

                                                    @if($unit->maximum_price)
                                                        - ₹{{ number_format($unit->maximum_price) }}
                                                    @endif
                                                @else
                                                    On Request
                                                @endif
                                            </td>

                                            <td>
                                                {{ $unit->available_units ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(count($amenities))
                    <div class="card">
                        <h4>Amenities</h4>

                        <div class="amen">
                            @foreach($amenities as $amenity)
                                <span>
                                    <i class="fa-solid fa-circle-check" style="color:#159653"></i>
                                    {{ $amenity }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($floorPlans->count())
                    <div class="card">
                        <h4>Floor Plans</h4>

                        <div class="amen">
                            @foreach($floorPlans as $floorPlan)
                                <a
                                    target="_blank"
                                    class="chip"
                                    style="text-decoration:none;color:#172033"
                                    href="{{ asset('images/builder_projects/' . $project->id . '/' . $floorPlan->file_name) }}"
                                >
                                    <i class="fa-regular fa-file"></i>
                                    {{ $floorPlan->title }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($properties->count())
                    <div class="card">
                        <h4>Available Properties in this Project</h4>

                        <div class="props">
                            @foreach($properties as $property)
                                <a
                                    class="prop"
                                    href="{{ url('/property/' . $property->slug_id) }}"
                                >
                                    <strong>{{ $property->title }}</strong>

                                    <div>
                                        @if(!empty($property->tower))
                                            Tower {{ $property->tower }} ·
                                        @endif

                                        {{ $property->sub_type ?: '' }}

                                        @if(!empty($property->price))
                                            · ₹{{ number_format($property->price) }}
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="card">
                    <h4>About the Developer</h4>

                    <strong>{{ $project->company_name ?: $project->owner_name }}</strong>

                    @if(!empty($project->rera_promoter_number))
                        <div style="font-size:10px;color:#159653;margin-top:4px">
                            Verified Promoter RERA: {{ $project->rera_promoter_number }}
                        </div>
                    @endif

                    @if(!empty($project->about_developer))
                        <p style="font-size:11px;line-height:1.6;color:#64748b">
                            {{ $project->about_developer }}
                        </p>
                    @endif
                </div>

            </main>

            <aside class="sticky">
                <div class="card">
                    <h4>Interested in this project?</h4>

                    @if(session('success'))
                        <div class="flash-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="flash-error">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form
                        class="form"
                        method="POST"
                        action="{{ url('/project-enquiry') }}"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="project_id"
                            value="{{ $project->id }}"
                        >

                        <input
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Your name *"
                            required
                        >

                        <input
                            name="mobile"
                            value="{{ old('mobile') }}"
                            placeholder="Mobile *"
                            required
                        >

                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="Email"
                        >

                        <select name="configuration">
                            <option value="">Interested configuration</option>

                            @foreach($units as $unit)
                                <option
                                    value="{{ $unit->configuration }}"
                                    {{ old('configuration') === $unit->configuration ? 'selected' : '' }}
                                >
                                    {{ $unit->configuration }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            name="budget"
                            value="{{ old('budget') }}"
                            placeholder="Budget"
                        >

                        <textarea
                            name="message"
                            placeholder="Message"
                        >{{ old('message') }}</textarea>

                        <button type="submit">
                            Send Project Enquiry
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
