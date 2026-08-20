@php
/* ── Null-safe defaults: all variables the blade uses ── */
$isEdit          = $isEdit          ?? false;
$formUrl         = $formUrl         ?? url("owner/post-property");
$prop            = $prop            ?? null;
$cust            = $cust            ?? session("bw_customer") ?? [];
$categories      = $categories      ?? collect();
$parameters      = $parameters      ?? collect();
$facilities      = $facilities      ?? collect();
$cities          = $cities          ?? collect();
$gallery         = $gallery         ?? collect();
$savedParams     = $savedParams     ?? collect();
$savedFacilities = $savedFacilities ?? collect();
$savedP          = $savedP          ?? collect();
$savedF          = $savedF          ?? collect();
$amenityList     = $amenityList     ?? ["Swimming Pool","Gym / Fitness","Car Parking","Lift / Elevator","Power Backup","24/7 Security","Garden / Park","High-Speed WiFi","Clubhouse","CCTV Surveillance","Intercom","Water Supply 24/7","Visitor Parking","Kids Play Area","Temple / Prayer Hall"];
$bedroomParamId  = $bedroomParamId  ?? 2;
$propId          = $propId          ?? null;
$ownerProperty   = $ownerProperty   ?? null;
$packages        = $packages        ?? collect();
$selectedPackage = $selectedPackage ?? null;
$swBuySubtypes   = isset($swBuySubtypes)   ? $swBuySubtypes   : ["Residential","Commercial","Land / Plot","Apartment","Villa"];
$swRentSubtypes  = isset($swRentSubtypes)  ? $swRentSubtypes  : ["Full House","PG / Hostel","Flatmates","Apartment"];
$swBhk           = isset($swBhk)           ? $swBhk           : ["1 BHK","2 BHK","3 BHK","4 BHK","5+ BHK"];
$swStatuses      = isset($swStatuses)      ? $swStatuses      : ["Ready to Move","Under Construction","New Launch"];
$swCommTypes     = isset($swCommTypes)     ? $swCommTypes     : ["Office","Co-working Space","Shop / Showroom","Warehouse","Factory / Industrial"];
$initialPropertyGroup = old("property_group", $propertyGroup ?? request("property_group", ""));
$initialListingMode   = old("listing_mode", $listingMode ?? request("listing_mode", ""));
$initialPropertyType  = old("propery_type", $isEdit ? ($prop->propery_type ?? 0) : request("propery_type", 0));
@endphp

@extends('frontend.owner.layouts.app')
@section('title', isset($prop) ? 'Edit Property' : 'Post New Property')
@section('page-title', isset($prop) ? 'Edit Property' : 'Post New Property')
@section('page-bread', isset($prop) ? 'Update your listing details' : 'Fill in details to list your property')

@section('content')
@php
try {
    $swCfg = \App\Http\Controllers\SearchSettingsController::load();
} catch(\Throwable $e) {
    $swCfg = [];
}
$loadedBuySubtypes  = collect($swCfg['tab_subtypes']['buy']  ?? [])->where('active', true)->pluck('label')->filter()->values()->toArray();
$loadedRentSubtypes = collect($swCfg['tab_subtypes']['rent'] ?? [])->where('active', true)->pluck('label')->filter()->values()->toArray();
if (!empty($loadedBuySubtypes)) { $swBuySubtypes = $loadedBuySubtypes; }
if (!empty($loadedRentSubtypes)) { $swRentSubtypes = $loadedRentSubtypes; }
$swBhk          = $swCfg['bhk_options']      ?? ['1 BHK','2 BHK','3 BHK','4 BHK','5+ BHK'];
$swStatuses     = $swCfg['prop_statuses']    ?? ['Ready to Move','Under Construction','New Launch'];
$swCommTypes    = $swCfg['commercial_types'] ?? ['Office','Co-working Space','Shop / Showroom','Warehouse','Factory / Industrial'];
@endphp

<div class="post-layout">
  {{-- Step sidebar --}}
  <div class="post-steps" id="postStepsSidebar">
    <div style="font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;">Posting Steps</div>
    @foreach([['Basic Info','fa-circle-info'],['Location','fa-location-dot'],['Property Details','fa-ruler-combined'],['Pricing','fa-indian-rupee-sign'],['Amenities','fa-star'],['Gallery &amp; Media','fa-images'],['Review &amp; Submit','fa-paper-plane']] as $i => $step)
    @php $n = $i+1; @endphp
    <div class="ps-item {{ $n===1?'active':'' }}" id="psi{{ $n }}" onclick="gotoFormStep({{ $n }})">
      <div class="ps-dot"><span>{{ $n }}</span></div>
      <div class="ps-label">{!! $step[0] !!}</div>
    </div>
    @if($n < 7)<div class="ps-connector" id="psc{{ $n }}"></div>@endif
    @endforeach
  </div>

  {{-- Form --}}
  <form method="POST" action="{{ $formUrl }}" enctype="multipart/form-data" id="propForm">
    @csrf
    <input type="hidden" name="property_group" id="propertyGroupInput" value="{{ $initialPropertyGroup }}">
    <input type="hidden" name="listing_mode" id="listingModeInput" value="{{ $initialListingMode }}">
    @php
      $isBuilderAccount = strtolower((string)($cust['owner_type'] ?? '')) === 'builder';
      $savedProjectId = old('project_id', $isEdit ? ($prop->project_id ?? '') : '');
      $savedProjectUnitId = old('project_unit_id', $isEdit ? ($prop->project_unit_id ?? '') : '');
    @endphp

    @if($isBuilderAccount && isset($builderProjects) && $builderProjects->count())
    <div class="form-card" style="margin-bottom:16px;border:1px solid #dbe4f0;background:#fbfdff">
      <div class="form-card-title"><i class="fa-solid fa-city"></i> Property Source</div>
      <div class="fg">
        <div class="f-group">
          <label class="f-label">Listing Source</label>
          <select class="f-input f-select" id="builderPropertySource" onchange="toggleBuilderProjectSource()">
            <option value="independent" {{ !$savedProjectId?'selected':'' }}>Independent Property</option>
            <option value="project" {{ $savedProjectId?'selected':'' }}>From My Project</option>
          </select>
        </div>
        <div class="f-group builder-project-link" style="{{ $savedProjectId?'':'display:none' }}">
          <label class="f-label">Select Project</label>
          <select class="f-input f-select" name="project_id" id="builderProjectId" onchange="applyBuilderProject()">
            <option value="">Select Approved Project</option>
            @foreach($builderProjects as $bp)
              <option value="{{ $bp->id }}" {{ (string)$savedProjectId===(string)$bp->id?'selected':'' }}>{{ $bp->title }}</option>
            @endforeach
          </select>
        </div>
        <div class="f-group builder-project-link" style="{{ $savedProjectId?'':'display:none' }}">
          <label class="f-label">Configuration</label>
          <select class="f-input f-select" name="project_unit_id" id="builderProjectUnitId" data-saved="{{ $savedProjectUnitId }}" onchange="applyBuilderUnit()">
            <option value="">Select Configuration</option>
          </select>
        </div>
        <div class="f-group builder-project-link" style="{{ $savedProjectId?'':'display:none' }}">
          <label class="f-label">Tower / Block</label>
          <input class="f-input" name="tower" value="{{ old('tower',$isEdit?($prop->tower??''):'') }}" placeholder="e.g. Tower B">
        </div>
        <div class="f-group builder-project-link" style="{{ $savedProjectId?'':'display:none' }}">
          <label class="f-label">Unit Number <span style="font-weight:500">(internal)</span></label>
          <input class="f-input" name="unit_number" value="{{ old('unit_number',$isEdit?($prop->unit_number??''):'') }}" placeholder="e.g. B-704">
        </div>
      </div>
      <div style="font-size:10px;color:#64748b;margin-top:8px">Selecting a project auto-fills its category, locality, city, state and coordinates. Unit configuration can auto-fill carpet/built-up area and starting price.</div>
    </div>
    @endif

    {{-- ═══ STEP 1: BASIC INFO ═══ --}}
    <div class="form-section active" id="fs1">
      <div class="fs-head"><h3>Basic Information</h3><p>Property type, category and a clear description</p></div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-tag"></i> Listing Purpose</div>
        <div class="f-group" style="margin-bottom:0;">
          <label class="f-label">Property For <span class="f-req">*</span></label>
          <div class="radio-group">
            @foreach([['0','Sell','fa-tag'],['1','Rent','fa-key']] as $opt)
            <label class="radio-chip {{ ($isEdit && $prop->propery_type == $opt[0]) || (!$isEdit && (string)$initialPropertyType === (string)$opt[0]) ? 'checked' : '' }}">
              <input type="radio" name="propery_type" value="{{ $opt[0] }}" {{ ($isEdit && $prop->propery_type == $opt[0]) || (!$isEdit && (string)$initialPropertyType === (string)$opt[0]) ? 'checked' : '' }}/>
              <i class="fa-solid {{ $opt[2] }}"></i> For {{ $opt[1] }}
            </label>
            @endforeach
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-layer-group"></i> Category &amp; Type</div>
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Property Category <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-layer-group"></i>
            <select class="f-input f-select" name="category_id" id="ownerCategoryId" required onchange="onCategoryChange(this.value)">
              <option value="">Select Category</option>
              @foreach($categories as $cat)
              <option value="{{ $cat->id }}"
                data-slug="{{ strtolower($cat->category) }}"
                {{ ($isEdit && $prop->category_id == $cat->id) || old('category_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->category }}
              </option>
              @endforeach
            </select></div>
          </div>
        </div>
      </div>

      {{-- ── CATEGORY-SPECIFIC SUBTYPE (driven by selected Admin Category) ── --}}
      <div class="form-card" id="card-category-subtype" style="display:none;">
        <div class="form-card-title">
          <i class="fa-solid fa-diagram-project"></i>
          <span id="categorySubtypeTitle">Property Sub-Type</span>
        </div>
        <label class="f-label" id="categorySubtypeHelp">
          Select the type that best describes this property <span class="f-req">*</span>
        </label>
        <div class="radio-group" id="categorySubtypeOptions" style="flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
        <input type="hidden" name="commercial_type" id="commercialTypeHidden"
               value="{{ old('commercial_type', $isEdit ? ($prop->commercial_type??'') : '') }}">
      </div>

      {{-- ── BHK TYPE (shown for residential, not commercial/plots) ── --}}
      <div class="form-card" id="card-bhk" style="display:none;">
        <div class="form-card-title"><i class="fa-solid fa-bed"></i> BHK Type</div>
        <label class="f-label">Number of Bedrooms (BHK)</label>
        <div class="radio-group" style="flex-wrap:wrap;gap:8px;margin-top:8px;" id="bhk-chips">
          @php $savedBhk = $isEdit ? ($savedP->firstWhere('parameter_id', $bedroomParamId)->value ?? '') : old('par_'.$bedroomParamId,''); @endphp
          @foreach($swBhk as $bhk)
          @php $bhkNum = (int) filter_var($bhk, FILTER_SANITIZE_NUMBER_INT); @endphp
          <label class="radio-chip {{ $savedBhk == $bhkNum ? 'checked':'' }}">
            <input type="radio" name="par_{{ $bedroomParamId }}" value="{{ $bhkNum }}"
              {{ $savedBhk == $bhkNum ? 'checked':'' }}/>
            {{ $bhk }}
          </label>
          @endforeach
        </div>
      </div>

      {{-- ── PROPERTY STATUS (dynamic from search settings) ── --}}
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-circle-check"></i> Property Status</div>
        <label class="f-label">Current construction / availability status</label>
        <div class="radio-group" style="flex-wrap:wrap;gap:8px;margin-top:8px;">
          @foreach($swStatuses as $i => $st)
          <label class="radio-chip">
            <input type="radio" name="prop_status" value="{{ $st }}"
              {{ old('prop_status', $isEdit ? ($prop->prop_status??'') : '') == $st ? 'checked':'' }}/>
            {{ $st }}
          </label>
          @endforeach
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-pen-to-square"></i> Title &amp; Description</div>
        <div class="fg fg1">
          <div class="f-group span2">
            <label class="f-label">Property Title <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-heading"></i>
            <input class="f-input" type="text" name="title" maxlength="150"
              value="{{ old('title', $isEdit ? $prop->title : '') }}"
              placeholder="e.g. Premium 3BHK Apartment in Anna Nagar with Car Parking" required
              oninput="document.getElementById('titleCount').textContent=this.value.length+'/150'"/>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span class="f-hint">Write a descriptive title — buyers search by keywords</span>
              <span class="char-count" id="titleCount">{{ strlen($isEdit?$prop->title:'') }}/150</span>
            </div>
          </div>
          <div class="f-group span2">
            <label class="f-label">Description <span class="f-req">*</span></label>
            <textarea class="f-textarea" name="description" rows="5" maxlength="3000" required
              placeholder="Describe your property — highlights, construction quality, nearby landmarks, special features…"
              oninput="document.getElementById('descCount').textContent=this.value.length+'/3000'">{{ old('description', $isEdit ? $prop->description : '') }}</textarea>
            <div style="display:flex;justify-content:space-between;">
              <span class="f-hint">Minimum 50 characters recommended</span>
              <span class="char-count" id="descCount">{{ strlen($isEdit?$prop->description:'') }}/3000</span>
            </div>
          </div>
        </div>
      </div>
      <div class="form-nav">
        <a href="{{ url('/owner/my-properties') }}" class="btn btn-ghost"><i class="fa-solid fa-chevron-left"></i> Cancel</a>
        <button type="button" class="btn btn-red" onclick="nextStep(1)">Next: Location <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 2: LOCATION ═══ --}}
    <div class="form-section" id="fs2">
      <div class="fs-head"><h3>Location Details</h3><p>Accurate location attracts more genuine buyers</p></div>
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-map-pin"></i> Address</div>
        <div class="fg">
          <div class="f-group span2">
            <label class="f-label">Full Address <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-location-dot"></i>
            <input class="f-input" type="text" name="address" required
              value="{{ old('address', $isEdit ? $prop->address : '') }}"
              placeholder="Door no., Street name, Area, Locality"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">City <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-city"></i>
            <input class="f-input" type="text" name="city" required value="{{ old('city', $isEdit ? $prop->city : '') }}" placeholder="e.g. Chennai"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">State <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-map"></i>
            <select class="f-input f-select" name="state" required>
              <option value="">Select State</option>
              @foreach(['Tamil Nadu','Karnataka','Telangana','Maharashtra','Kerala','Andhra Pradesh','Delhi','Gujarat','Rajasthan','Madhya Pradesh','Uttar Pradesh','West Bengal','Punjab','Haryana','Odisha','Jharkhand','Bihar','Chhattisgarh','Assam','Goa'] as $st)
              <option value="{{ $st }}" {{ old('state', $isEdit ? $prop->state : '') === $st ? 'selected':'' }}>{{ $st }}</option>
              @endforeach
            </select></div>
          </div>
          <div class="f-group">
            <label class="f-label">Pincode</label>
            <div class="f-wrap"><i class="fa-solid fa-location-crosshairs"></i>
            <input class="f-input" type="text" name="pincode" maxlength="10"
              value="{{ old('pincode', $isEdit ? $prop->pincode??'' : '') }}" placeholder="e.g. 600001"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Country</label>
            <div class="f-wrap"><i class="fa-solid fa-globe"></i>
            <input class="f-input" type="text" name="country" value="{{ old('country', $isEdit ? $prop->country : 'India') }}"/>
            </div>
          </div>
        </div>
      </div>
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-map-location-dot"></i> Map Coordinates (Optional)</div>
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Latitude</label>
            <div class="f-wrap"><i class="fa-solid fa-arrows-up-down"></i>
            <input class="f-input" type="text" name="latitude" value="{{ old('latitude', $isEdit ? $prop->latitude : '') }}" placeholder="e.g. 13.0827"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Longitude</label>
            <div class="f-wrap"><i class="fa-solid fa-arrows-left-right"></i>
            <input class="f-input" type="text" name="longitude" value="{{ old('longitude', $isEdit ? $prop->longitude : '') }}" placeholder="e.g. 80.2707"/>
            </div>
          </div>
        </div>
        <div style="height:160px;background:#EEF2FF;border-radius:var(--r-lg);display:flex;align-items:center;justify-content:center;margin-top:14px;border:1px solid var(--border);">
          <div style="text-align:center;color:var(--gray2);"><i class="fa-solid fa-map-location-dot" style="font-size:32px;margin-bottom:8px;display:block;"></i><span style="font-size:13px;">Enter coordinates above to pin location</span></div>
        </div>
      </div>
      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(2)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="nextStep(2)">Next: Property Details <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 3: PROPERTY DETAILS ═══ --}}
    <div class="form-section" id="fs3">
      <div class="fs-head"><h3>Property Details</h3><p>Specific features — what does your property have?</p></div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-ruler-combined"></i> Size &amp; Configuration</div>
        <div class="fg fg3">
          @foreach($parameters as $param)
          <div class="f-group js-param-field" data-param-name="{{ strtolower($param->name) }}">
            <label class="f-label">{{ $param->name }}</label>
            <div class="f-wrap"><i class="fa-solid fa-hashtag"></i>
            @if($param->type_of_parameter === 'textbox')
              <input class="f-input" type="text" name="par_{{ $param->id }}"
                value="{{ old('par_'.$param->id, $savedP[$param->id] ?? '') }}"
                placeholder="{{ $param->name }}"/>
            @else
              <input class="f-input" type="number" min="0" name="par_{{ $param->id }}"
                value="{{ old('par_'.$param->id, $savedP[$param->id] ?? '') }}"
                placeholder="e.g. 2"/>
            @endif
            </div>
          </div>
          @endforeach
          <div class="f-group">
            <label class="f-label">Total Area (sqft)</label>
            <div class="f-wrap"><i class="fa-solid fa-vector-square"></i>
            <input class="f-input" type="number" name="total_area" value="{{ old('total_area', $isEdit ? $prop->total_area??'' : '') }}" placeholder="e.g. 1850"/>
            </div>
          </div>
          <div class="f-group js-building-field">
            <label class="f-label">Carpet Area (sqft)</label>
            <div class="f-wrap"><i class="fa-solid fa-square"></i>
            <input class="f-input" type="number" name="carpet_area" value="{{ old('carpet_area', $isEdit ? $prop->carpet_area??'' : '') }}" placeholder="e.g. 1400"/>
            </div>
          </div>
          <div class="f-group js-building-field">
            <label class="f-label">Floor Number</label>
            <div class="f-wrap"><i class="fa-solid fa-building-columns"></i>
            <input class="f-input" type="number" name="floor_number" value="{{ old('floor_number', $isEdit ? $prop->floor_number??'' : '') }}" placeholder="e.g. 3"/>
            </div>
          </div>
          <div class="f-group js-building-field">
            <label class="f-label">Total Floors</label>
            <div class="f-wrap"><i class="fa-solid fa-layer-group"></i>
            <input class="f-input" type="number" name="total_floors" value="{{ old('total_floors', $isEdit ? $prop->total_floors??'' : '') }}" placeholder="e.g. 12"/>
            </div>
          </div>
        </div>
      </div>


      <div class="form-card" id="card-land-details" style="display:none;">
        <div class="form-card-title"><i class="fa-solid fa-map"></i> Land / Plot Details</div>
        <div class="fg">
          <div class="f-group"><label class="f-label">Land Type</label><div class="f-wrap"><i class="fa-solid fa-seedling"></i><select class="f-input f-select" name="land_type"><option value="">Select</option>@foreach(["Residential Plot","Agricultural Land","Industrial Plot","Commercial Plot","Farm Land"] as $opt)<option value="{{ $opt }}" {{ old("land_type", $isEdit ? ($prop->land_type??"") : "") === $opt ? "selected":"" }}>{{ $opt }}</option>@endforeach</select></div></div>
          <div class="f-group"><label class="f-label">Plot Area Unit</label><div class="f-wrap"><i class="fa-solid fa-ruler"></i><select class="f-input f-select" name="area_unit">@foreach(["sqft","sq. yard","cent","acre","ground"] as $opt)<option value="{{ $opt }}" {{ old("area_unit", $isEdit ? ($prop->area_unit??"sqft") : "sqft") === $opt ? "selected":"" }}>{{ strtoupper($opt) }}</option>@endforeach</select></div></div>
          <div class="f-group"><label class="f-label">Road Width (ft)</label><div class="f-wrap"><i class="fa-solid fa-road"></i><input class="f-input" type="number" name="road_width" value="{{ old("road_width", $isEdit ? ($prop->road_width??"") : "") }}" placeholder="e.g. 30"></div></div>
          <div class="f-group"><label class="f-label">Boundary Wall</label><div class="f-wrap"><i class="fa-solid fa-border-all"></i><select class="f-input f-select" name="boundary_wall"><option value="">Select</option><option value="Yes" {{ old("boundary_wall", $isEdit ? ($prop->boundary_wall??"") : "") === "Yes" ? "selected":"" }}>Yes</option><option value="No" {{ old("boundary_wall", $isEdit ? ($prop->boundary_wall??"") : "") === "No" ? "selected":"" }}>No</option></select></div></div>
        </div>
      </div>

      <div class="form-card" id="card-property-specifications">
        <div class="form-card-title"><i class="fa-solid fa-list-check"></i> Property Specifications</div>
        <div class="fg">
          @foreach([
            ['age_of_building','Age of Building','fa-calendar',['Under Construction','New (0-1 year)','1-5 years','5-10 years','10+ years']],
            ['facing','Facing Direction','fa-compass',['East','West','North','South','North-East','North-West','South-East','South-West']],
            ['furnishing','Furnishing Status','fa-couch',['Fully Furnished','Semi Furnished','Unfurnished']],
            ['water_supply','Water Supply','fa-droplet',['Corporation','Borewell','Both','24/7 Available']],
            ['prop_status','Property Status','fa-circle-check',['Ready to Move','Under Construction','New Launch']],
          ] as $spec)
          <div class="f-group">
            <label class="f-label">{{ $spec[1] }}</label>
            <div class="f-wrap"><i class="fa-solid {{ $spec[2] }}"></i>
            <select class="f-input f-select" name="{{ $spec[0] }}">
              <option value="">Select</option>
              @foreach($spec[3] as $opt)
              <option value="{{ $opt }}" {{ old($spec[0], $isEdit ? ($prop->{$spec[0]}??'') : '') === $opt ? 'selected':'' }}>{{ $opt }}</option>
              @endforeach
            </select></div>
          </div>
          @endforeach
        </div>
      </div>

      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(3)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="nextStep(3)">Next: Pricing <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 4: PRICING ═══ --}}
    <div class="form-section" id="fs4">
      <div class="fs-head"><h3>Pricing Details</h3><p>Set a competitive price to attract genuine enquiries</p></div>
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-indian-rupee-sign"></i> Price Information</div>
        <div class="fg">
          <div class="f-group">
            <label class="f-label">Expected Price (₹) <span class="f-req">*</span></label>
            <div class="f-wrap"><i class="fa-solid fa-indian-rupee-sign"></i>
            <input class="f-input" type="number" name="price" required min="1"
              value="{{ old('price', $isEdit ? $prop->price : '') }}"
              placeholder="e.g. 8600000"
              oninput="showPriceLabel(this.value)"/>
            </div>
            <span class="f-hint" id="priceLabel" style="font-weight:600;color:var(--red);">
              {{ $isEdit && $prop->price ? '₹ '.number_format($prop->price) : '' }}
            </span>
          </div>
          <div class="f-group">
            <label class="f-label">Price Negotiable?</label>
            <div class="f-wrap"><i class="fa-solid fa-handshake"></i>
            <select class="f-input f-select" name="price_negotiable">
              <option value="0" {{ old('price_negotiable', $isEdit ? $prop->price_negotiable??0 : 0) == 0 ? 'selected':'' }}>Fixed Price</option>
              <option value="1" {{ old('price_negotiable', $isEdit ? $prop->price_negotiable??0 : 0) == 1 ? 'selected':'' }}>Yes, Negotiable</option>
            </select></div>
          </div>
          <div class="f-group">
            <label class="f-label">Maintenance Charges (₹/month)</label>
            <div class="f-wrap"><i class="fa-solid fa-file-invoice-dollar"></i>
            <input class="f-input" type="number" name="maintenance"
              value="{{ old('maintenance', $isEdit ? $prop->maintenance??'' : '') }}" placeholder="Optional"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Security Deposit (₹)</label>
            <div class="f-wrap"><i class="fa-solid fa-shield"></i>
            <input class="f-input" type="number" name="security_deposit"
              value="{{ old('security_deposit', $isEdit ? $prop->security_deposit??'' : '') }}" placeholder="For rent properties"/>
            </div>
          </div>
          <div class="f-group">
            <label class="f-label">Rent Duration</label>
            <div class="f-wrap"><i class="fa-solid fa-clock"></i>
            <select class="f-input f-select" name="rentduration">
              @foreach(['','Monthly','Quarterly','Half-Yearly','Yearly'] as $rd)
              <option value="{{ $rd }}" {{ old('rentduration', $isEdit ? $prop->rentduration??'' : '') === $rd ? 'selected':'' }}>{{ $rd ?: 'N/A' }}</option>
              @endforeach
            </select></div>
          </div>
        </div>
      </div>
      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(4)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="nextStep(4)">Next: Amenities <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 5: AMENITIES ═══ --}}
    <div class="form-section" id="fs5">
      <div class="fs-head"><h3>Amenities &amp; Facilities</h3><p>Select available amenities and nearby facility distances</p></div>
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-star"></i> Property Amenities</div>
        <div class="amenity-grid">
          @foreach($amenityList as $amenity)
          <label class="amenity-chip js-category-amenity">
            <input type="checkbox" name="amenities[]" value="{{ $amenity }}" onchange="this.closest('.amenity-chip').classList.toggle('checked',this.checked)"/>
            <i class="fa-solid fa-check-circle" style="color:var(--red);font-size:12px;"></i>
            {{ $amenity }}
          </label>
          @endforeach
        </div>
      </div>
      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-location-dot"></i> Nearby Facilities (km)</div>
        <div class="fg fg3">
          @foreach($facilities as $fac)
          <div class="f-group">
            <label class="f-label">{{ $fac->name }}</label>
            <div class="f-wrap"><i class="fa-solid fa-location-dot" style="color:var(--red);"></i>
            <input class="f-input" type="number" step="0.1" min="0" name="facility_{{ $fac->id }}"
              value="{{ old('facility_'.$fac->id, $savedF[$fac->id] ?? '') }}"
              placeholder="Distance in km"/>
            </div>
          </div>
          @endforeach
          @if($facilities->isEmpty())
          {{-- fallback fields if outdoor_facilities table is empty --}}
          @foreach([['Hospital','fa-hospital-user'],['School','fa-school'],['Metro','fa-train-subway'],['Mall','fa-bag-shopping'],['Airport','fa-plane'],['Railway Station','fa-train']] as $fac)
          <div class="f-group">
            <label class="f-label">{{ $fac[0] }}</label>
            <div class="f-wrap"><i class="fa-solid {{ $fac[1] }}"></i>
            <input class="f-input" type="number" step="0.1" name="near_{{ Str::slug($fac[0]) }}" placeholder="km away"/>
            </div>
          </div>
          @endforeach
          @endif
        </div>
      </div>
      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(5)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="nextStep(5)">Next: Gallery <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 6: GALLERY ═══ --}}
    <div class="form-section" id="fs6">
      <div class="fs-head"><h3>Photos &amp; Media</h3><p>Properties with more photos get 5× more enquiries</p></div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-camera"></i> Main Cover Photo</div>
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
          <div style="width:100px;height:80px;border-radius:var(--r);overflow:hidden;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;" id="mainImgPreview">
            @if($isEdit && $prop->title_image)
              <img src="{{ asset('images/'.config('global.PROPERTY_TITLE_IMG_PATH','property_title_img/').$prop->title_image) }}" style="width:100%;height:100%;object-fit:cover;" id="mainImgThumb"/>
            @else
              <i class="fa-solid fa-image" style="font-size:28px;color:var(--gray3);" id="mainImgThumb"></i>
            @endif
          </div>
          <div style="flex:1;">
            <label class="f-label" style="margin-bottom:6px;display:block;">Cover Image {{ $isEdit ? '(leave blank to keep current)' : '' }}</label>
            <input type="file" name="title_image" accept="image/*" class="f-input no-icon" style="padding:8px;"
              onchange="previewMain(event)"/>
            <span class="f-hint">JPG/PNG, Max 5MB. This is shown in property cards.</span>
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-images"></i> Gallery Photos <span style="font-size:11px;font-weight:400;color:var(--gray2);">(Max 20 photos, each under 5MB)</span></div>
        @if($isEdit && $gallery->count())
        <div style="margin-bottom:18px;">
          <div style="font-size:12px;font-weight:700;color:var(--gray);margin-bottom:10px;">Existing Photos ({{ $gallery->count() }})</div>
          <div class="gallery-preview">
            @foreach($gallery as $img)
            <div class="gp-item {{ $loop->first ? 'main-img' : '' }}" id="gimg-{{ $img->id }}">
              @php
                $galleryBase = config('global.PROPERTY_GALLERY_IMG_PATH','/property_gallery_img/');
                $nestedGalleryPath = $galleryBase.$prop->id.'/'.$img->image;
                $galleryImagePath = file_exists(public_path('images').$nestedGalleryPath)
                  ? $nestedGalleryPath
                  : $galleryBase.$img->image;
              @endphp
              <img src="{{ asset('images/'.ltrim($galleryImagePath,'/')) }}" alt=""/>
              <button type="button" class="gp-remove" onclick="deleteGalleryImg({{ $img->id }}, {{ $prop->id }})"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endforeach
          </div>
        </div>
        @endif
        <div class="gallery-area" id="galleryDrop">
          <input type="file" name="gallery[]" accept="image/*" multiple onchange="handleGallery(event)"/>
          <div class="ga-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
          <div class="ga-title">Drag &amp; drop photos here</div>
          <div class="ga-sub">or <span style="color:var(--red);font-weight:700;">browse to upload</span></div>
          <div class="ga-limit">JPG, PNG, WEBP · Max 20 photos · Each under 5MB</div>
        </div>
        <div class="gallery-preview" id="galleryPreview" style="margin-top:14px;"></div>
      </div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-brands fa-youtube" style="color:#FF0000;"></i> Video Tour (Optional)</div>
        <div class="f-group">
          <label class="f-label">YouTube / Vimeo Link</label>
          <div class="f-wrap"><i class="fa-brands fa-youtube"></i>
          <input class="f-input" type="url" name="video_link"
            value="{{ old('video_link', $isEdit ? $prop->video_link??'' : '') }}"
            placeholder="https://youtube.com/watch?v=..."/>
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-card-title"><i class="fa-solid fa-cube" style="color:#7C3AED;"></i> 3D Image / Virtual Tour (Optional)</div>
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
          <div style="width:100px;height:80px;border-radius:var(--r);overflow:hidden;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;" id="img3dPreview">
            @if($isEdit && !empty($prop->three_d_image))
              <img src="{{ url('images/'.config('global.3D_IMG_PATH','3d_image/').$prop->three_d_image) }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'"/>
            @else
              <i class="fa-solid fa-cube" style="font-size:28px;color:var(--gray3);"></i>
            @endif
          </div>
          <div style="flex:1;">
            <label class="f-label" style="margin-bottom:6px;display:block;">3D Image {{ $isEdit ? '(leave blank to keep current)' : '' }}</label>
            <input type="file" name="3d_image" accept="image/*" class="f-input no-icon" style="padding:8px;"
              onchange="preview3D(event)"/>
            <span class="f-hint">JPG/PNG, Max 5MB. Used for 3D/virtual tour preview.</span>
          </div>
        </div>
      </div>

      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(6)"><i class="fa-solid fa-chevron-left"></i> Back</button>
        <button type="button" class="btn btn-red" onclick="nextStep(6)">Review &amp; Submit <i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    {{-- ═══ STEP 7: REVIEW ═══ --}}
    <div class="form-section" id="fs7">
      <div class="fs-head"><h3>Review &amp; Submit</h3><p>Double-check everything before submitting</p></div>

      <div class="form-card" style="border:2px solid var(--green);background:#F0FDF4;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
          <div style="width:52px;height:52px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-check" style="color:#fff;font-size:22px;"></i>
          </div>
          <div>
            <div style="font-size:16px;font-weight:800;color:var(--green);">All Details Filled!</div>
            <div style="font-size:13px;color:var(--gray);">Your listing is ready. Review the summary below before submitting.</div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" id="reviewSummary">
          <div style="background:#fff;border-radius:var(--r);padding:12px;border:1px solid var(--border);">
            <div style="font-size:11px;color:var(--gray2);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Title</div>
            <div style="font-size:13px;font-weight:700;color:var(--navy);" id="rev_title">—</div>
          </div>
          <div style="background:#fff;border-radius:var(--r);padding:12px;border:1px solid var(--border);">
            <div style="font-size:11px;color:var(--gray2);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Location</div>
            <div style="font-size:13px;font-weight:700;color:var(--navy);" id="rev_location">—</div>
          </div>
          <div style="background:#fff;border-radius:var(--r);padding:12px;border:1px solid var(--border);">
            <div style="font-size:11px;color:var(--gray2);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Price</div>
            <div style="font-size:13px;font-weight:700;color:var(--red);" id="rev_price">—</div>
          </div>
          <div style="background:#fff;border-radius:var(--r);padding:12px;border:1px solid var(--border);">
            <div style="font-size:11px;color:var(--gray2);font-weight:600;text-transform:uppercase;margin-bottom:3px;">Listing Type</div>
            <div style="font-size:13px;font-weight:700;color:var(--navy);" id="rev_type">—</div>
          </div>
        </div>
      </div>

      <div class="form-card" style="background:var(--amber-light);border:1px solid var(--amber);">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <i class="fa-solid fa-circle-info" style="color:var(--amber);font-size:18px;margin-top:2px;flex-shrink:0;"></i>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--navy);">What happens next?</div>
            <ul style="font-size:13px;color:var(--gray);margin-top:8px;padding-left:16px;line-height:1.7;">
              <li>Our team reviews your listing within <strong>24 hours</strong></li>
              <li>Once approved, it goes live on BigWein.com</li>
              <li>Buyers can view and enquire directly to you</li>
              <li>You'll be notified via email when approved</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="form-nav">
        <button type="button" class="btn btn-outline" onclick="prevStep(7)"><i class="fa-solid fa-chevron-left"></i> Edit Details</button>
        <button type="submit" class="btn btn-red" style="background:var(--green);box-shadow:0 2px 10px rgba(22,163,74,.3);" id="submitBtn">
          <i class="fa-solid fa-paper-plane"></i> {{ $isEdit ? 'Update Property' : 'Submit for Review' }}
        </button>
      </div>
    </div>

  </form>
</div>

@if($isBuilderAccount && isset($builderProjects))
<script>
const BW_BUILDER_PROJECTS = @json($builderProjects->keyBy('id'));
const BW_BUILDER_UNITS = @json($builderProjectUnits->groupBy('project_id'));

function toggleBuilderProjectSource(){
  const linked=document.getElementById('builderPropertySource')?.value==='project';
  document.querySelectorAll('.builder-project-link').forEach(el=>el.style.display=linked?'':'none');
  const p=document.getElementById('builderProjectId');
  if(!linked && p){p.value='';document.getElementById('builderProjectUnitId').innerHTML='<option value="">Select Configuration</option>';}
}
function applyBuilderProject(){
  const id=document.getElementById('builderProjectId')?.value;
  const p=BW_BUILDER_PROJECTS[id];
  const unit=document.getElementById('builderProjectUnitId');
  if(unit){
    const saved=unit.dataset.saved||'';
    unit.innerHTML='<option value="">Select Configuration</option>';
    (BW_BUILDER_UNITS[id]||[]).forEach(u=>{
      const o=document.createElement('option');o.value=u.id;o.textContent=u.configuration+(u.built_up_area?' · '+u.built_up_area+' sqft':'');
      if(String(saved)===String(u.id))o.selected=true;unit.appendChild(o);
    });
  }
  if(!p)return;
  const cat=document.getElementById('ownerCategoryId'); if(cat){cat.value=p.category_id||''; if(typeof onCategoryChange==='function')onCategoryChange(cat.value);}
  const set=(name,val)=>{const el=document.querySelector(`[name="${name}"]`);if(el && val!==null && val!==undefined && String(val)!=='')el.value=val;};
  set('address',p.location);set('city',p.city);set('state',p.state);set('country',p.country);set('latitude',p.latitude);set('longitude',p.longitude);
  applyBuilderUnit();
}
function applyBuilderUnit(){
  const pid=document.getElementById('builderProjectId')?.value;
  const uid=document.getElementById('builderProjectUnitId')?.value;
  const u=(BW_BUILDER_UNITS[pid]||[]).find(x=>String(x.id)===String(uid)); if(!u)return;
  const set=(name,val)=>{const el=document.querySelector(`[name="${name}"]`);if(el && val!==null && val!==undefined && String(val)!=='')el.value=val;};
  set('carpet_area',u.carpet_area);set('total_area',u.built_up_area);set('price',u.starting_price);
}
document.addEventListener('DOMContentLoaded',()=>{toggleBuilderProjectSource(); if(document.getElementById('builderProjectId')?.value)applyBuilderProject();});
</script>
@endif

@endsection

@push('scripts')
<script>
// ─── CATEGORY-DRIVEN PROPERTY FORM V2.3 ───────────────────────────────
const CATEGORY_PROFILES = {
  villa: {
    aliases:['villa'], title:'Villa Type', help:'Select the type of villa you are listing',
    subtypes:['Independent Villa','Gated Community Villa','Duplex Villa','Triplex Villa','Luxury Villa','Farm Villa'],
    showBhk:true, showLand:false, showBuilding:true, parameterMode:'residential',
    amenities:['Swimming Pool','Gym / Fitness','Car Parking','Lift / Elevator','Power Backup','24/7 Security','Garden / Park','High-Speed WiFi','Clubhouse','CCTV Surveillance','Intercom','Water Supply 24/7','Visitor Parking','Kids Play Area','Temple / Prayer Hall']
  },
  apartment: {
    aliases:['apartment','flat'], title:'Apartment Type', help:'Select the apartment configuration',
    subtypes:['Studio Apartment','1 BHK Apartment','2 BHK Apartment','3 BHK Apartment','4+ BHK Apartment','Penthouse','Duplex Apartment','Serviced Apartment'],
    showBhk:true, showLand:false, showBuilding:true, parameterMode:'residential', amenities:null
  },
  townhouse: {
    aliases:['townhouse','town house','row house'], title:'Townhouse Type', help:'Select the type of townhouse',
    subtypes:['Independent Townhouse','Gated Townhouse','Duplex Townhouse','Row House','Luxury Townhouse'],
    showBhk:true, showLand:false, showBuilding:true, parameterMode:'residential', amenities:null
  },
  plot: {
    aliases:['plot','land','agricultural'], title:'Land / Plot Type', help:'Select the type of land or plot',
    subtypes:['Residential Plot','Agricultural Land','Commercial Plot','Industrial Plot','Farm Land'],
    showBhk:false, showLand:true, showBuilding:false, parameterMode:'plot',
    amenities:['24/7 Security','Water Supply 24/7']
  },
  commercial: {
    aliases:['commercial','office','shop','warehouse','industrial'], title:'Commercial Space Type', help:'Select the type of commercial property',
    subtypes:['Office Space','Shop / Showroom','Warehouse','Co-working Space','Factory / Industrial','Restaurant','Hotel'],
    showBhk:false, showLand:false, showBuilding:true, parameterMode:'commercial',
    amenities:['Car Parking','Lift / Elevator','Power Backup','24/7 Security','High-Speed WiFi','CCTV Surveillance','Intercom','Water Supply 24/7','Visitor Parking']
  },
  pg: {
    aliases:['pg house','pg','hostel','co-living','coliving'], title:'PG / Co-Living Type', help:'Select the accommodation type',
    subtypes:['Boys PG','Girls PG','Co-Living','Student Hostel','Working Professional PG'],
    showBhk:false, showLand:false, showBuilding:true, parameterMode:'pg',
    amenities:['Car Parking','Power Backup','24/7 Security','High-Speed WiFi','CCTV Surveillance','Water Supply 24/7']
  },
  residential: {
    aliases:['residential','house','bungalow'], title:'Residential Type', help:'Select the residential property type',
    subtypes:['Independent House','Apartment','Villa','Duplex House','Bungalow'],
    showBhk:true, showLand:false, showBuilding:true, parameterMode:'residential', amenities:null
  }
};

function normCat(v){ return (v||'').toString().toLowerCase().replace(/[_-]+/g,' ').replace(/\s+/g,' ').trim(); }

function currentCategoryName(){
  const s=document.getElementById('ownerCategoryId');
  return s?.selectedOptions?.[0] ? normCat(s.selectedOptions[0].textContent) : '';
}

function resolveCategoryProfile(){
  const name=currentCategoryName();
  for(const [key,p] of Object.entries(CATEGORY_PROFILES)){
    if((p.aliases||[]).some(a=>name.includes(a))) return {key,...p};
  }
  return {key:'generic',title:'Property Sub-Type',help:'Subtype is not configured for this category',subtypes:[],showBhk:false,showLand:false,showBuilding:true,parameterMode:'generic',amenities:null};
}

const STORED_SUBTYPE = @json(old('sub_type', $isEdit ? ($prop->sub_type ?? '') : ''));
const STORED_COMMERCIAL = @json(old('commercial_type', $isEdit ? ($prop->commercial_type ?? '') : ''));

function renderCategorySubtypes(profile){
  const card=document.getElementById('card-category-subtype');
  const box=document.getElementById('categorySubtypeOptions');
  const title=document.getElementById('categorySubtypeTitle');
  const help=document.getElementById('categorySubtypeHelp');
  const comm=document.getElementById('commercialTypeHidden');
  if(!card||!box) return;

  title.textContent=profile.title||'Property Sub-Type';
  help.innerHTML=(profile.help||'Select property subtype')+' <span class="f-req">*</span>';
  box.innerHTML='';

  if(!profile.subtypes?.length){
    card.style.display='none';
    if(comm) comm.value='';
    return;
  }

  let preferred=document.querySelector('input[name="sub_type"]:checked')?.value || (profile.key==='commercial' ? (STORED_COMMERCIAL||STORED_SUBTYPE) : STORED_SUBTYPE);

  profile.subtypes.forEach((st,i)=>{
    const label=document.createElement('label');
    label.className='radio-chip';
    const input=document.createElement('input');
    input.type='radio'; input.name='sub_type'; input.value=st;
    if(preferred===st || (!preferred && i===0)){ input.checked=true; label.classList.add('checked'); }
    input.addEventListener('change',()=>{
      box.querySelectorAll('.radio-chip').forEach(x=>x.classList.remove('checked'));
      label.classList.add('checked');
      if(comm) comm.value=profile.key==='commercial' ? st : '';
    });
    label.appendChild(input); label.appendChild(document.createTextNode(' '+st)); box.appendChild(label);
  });

  const selected=box.querySelector('input[name="sub_type"]:checked');
  if(comm) comm.value=profile.key==='commercial' && selected ? selected.value : '';
  card.style.display='block';
}

function setFieldVisible(name,visible){
  const input=document.querySelector(`[name="${name}"]`);
  const group=input?.closest('.f-group');
  if(!group) return;
  group.style.display=visible?'':'none';
  if(!visible) group.querySelectorAll('input,select,textarea').forEach(el=>{
    if(el.type==='checkbox'||el.type==='radio') el.checked=false; else el.value='';
  });
}

function filterParameters(profile){
  document.querySelectorAll('.js-param-field').forEach(el=>{
    const n=normCat(el.dataset.paramName||'');
    let visible=true;
    if(profile.parameterMode==='plot'){
      visible=!['bedroom','bathroom','balcon','kitchen','wifi','car parking','cctv'].some(t=>n.includes(t));
    }else if(profile.parameterMode==='commercial'){
      visible=!['bedroom','balcon','bhk'].some(t=>n.includes(t));
    }else if(profile.parameterMode==='pg'){
      visible=!['bhk'].some(t=>n.includes(t));
    }
    el.style.display=visible?'':'none';
    if(!visible) el.querySelectorAll('input,select').forEach(i=>i.value='');
  });
}

function filterAmenities(profile){
  document.querySelectorAll('.js-category-amenity').forEach(chip=>{
    const text=(chip.textContent||'').trim();
    const visible=!profile.amenities || profile.amenities.includes(text);
    chip.style.display=visible?'':'none';
    if(!visible){
      const cb=chip.querySelector('input[type=checkbox]');
      if(cb) cb.checked=false;
      chip.classList.remove('checked');
    }
  });
}

function applyCategorySpecificFields(){
  const p=resolveCategoryProfile();
  renderCategorySubtypes(p);

  const bhk=document.getElementById('card-bhk');
  if(bhk){
    bhk.style.display=p.showBhk?'block':'none';
    if(!p.showBhk) bhk.querySelectorAll('input').forEach(i=>i.checked=false);
  }

  const land=document.getElementById('card-land-details');
  if(land){
    land.style.display=p.showLand?'block':'none';
    if(!p.showLand) land.querySelectorAll('input,select').forEach(i=>i.value='');
  }

  ['carpet_area','floor_number','total_floors','age_of_building','furnishing','water_supply'].forEach(n=>setFieldVisible(n,p.showBuilding));
  filterParameters(p);
  filterAmenities(p);

  const spec=document.getElementById('card-property-specifications');
  if(spec){
    spec.style.display=[...spec.querySelectorAll('.f-group')].some(g=>g.style.display!=='none')?'block':'none';
  }
}

function onCategoryChange(){ applyCategorySpecificFields(); }

document.addEventListener('DOMContentLoaded',()=>{
  applyCategorySpecificFields();
  document.getElementById('ownerCategoryId')?.addEventListener('change',applyCategorySpecificFields);

  document.getElementById('propForm')?.addEventListener('submit',()=>{
    document.querySelectorAll('.f-group,.form-card,.amenity-chip').forEach(c=>{
      if(c.style.display==='none'){
        c.querySelectorAll('input,select,textarea').forEach(el=>{
          if(el.type==='hidden') return;
          if(el.type==='checkbox'||el.type==='radio') el.checked=false; else el.value='';
        });
      }
    });
    const p=resolveCategoryProfile();
    const comm=document.getElementById('commercialTypeHidden');
    const sub=document.querySelector('input[name="sub_type"]:checked')?.value||'';
    if(comm) comm.value=p.key==='commercial'?sub:'';
  });
});

// ─── Step navigation ───
let currentStep = 1;
const totalSteps = 7;

function nextStep(n) { gotoFormStep(n + 1); }
function prevStep(n) { gotoFormStep(n - 1); }

function gotoFormStep(n) {
    if (n < 1 || n > totalSteps) return;
    currentStep = n;
    document.querySelectorAll('.form-section').forEach((s, i) => {
        s.classList.toggle('active', i === n - 1);
    });
    for (let i = 1; i <= totalSteps; i++) {
        const item = document.getElementById('psi' + i);
        if (!item) continue;
        item.classList.toggle('active', i === n);
        item.classList.toggle('done', i < n);
        const dot = item.querySelector('.ps-dot');
        dot.innerHTML = i < n ? '<i class="fa-solid fa-check" style="font-size:11px;"></i>' : '<span>' + i + '</span>';
        if (i < totalSteps) {
            const conn = document.getElementById('psc' + i);
            if (conn) conn.classList.toggle('done', i < n);
        }
    }
    if (n === 7) buildReview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── Review summary ───
function buildReview() {
    const title   = document.querySelector('[name=title]')?.value || '—';
    const city    = document.querySelector('[name=city]')?.value || '';
    const state   = document.querySelector('[name=state]')?.value || '';
    const price   = document.querySelector('[name=price]')?.value;
    const ptype   = document.querySelector('[name=propery_type]:checked')?.value === '0' ? 'For Sale' : 'For Rent';

    document.getElementById('rev_title').textContent    = title;
    document.getElementById('rev_location').textContent = [city, state].filter(Boolean).join(', ') || '—';
    document.getElementById('rev_price').textContent    = price ? '₹ ' + Number(price).toLocaleString('en-IN') : '—';
    document.getElementById('rev_type').textContent     = ptype;
}

// ─── Price label ───
function showPriceLabel(v) {
    const lbl = document.getElementById('priceLabel');
    if (!v) { lbl.textContent = ''; return; }
    const n = parseFloat(v);
    if (n >= 10000000) lbl.textContent = '₹ ' + (n / 10000000).toFixed(2) + ' Cr';
    else if (n >= 100000) lbl.textContent = '₹ ' + (n / 100000).toFixed(2) + ' Lakh';
    else lbl.textContent = '₹ ' + n.toLocaleString('en-IN');
}

// ─── Main image preview ───
function preview3D(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const box = document.getElementById('img3dPreview');
        box.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:var(--r);" />`;
    };
    reader.readAsDataURL(file);
}

function previewMain(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const box = document.getElementById('mainImgPreview');
        box.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:var(--r);" id="mainImgThumb"/>`;
    };
    reader.readAsDataURL(file);
}

// ─── Gallery preview ───
function handleGallery(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('galleryPreview');
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = ev => {
            const div = document.createElement('div');
            div.className = 'gp-item';
            div.innerHTML = `<img src="${ev.target.result}" alt=""/><button type="button" class="gp-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// ─── Delete existing gallery image (edit mode) ───
async function deleteGalleryImg(imageId, propId) {
    if (!confirm('Remove this photo?')) return;
    const res = await owFetch(`/owner/gallery/${imageId}`, { method: 'DELETE' });
    if (res.success) {
        document.getElementById('gimg-' + imageId)?.remove();
        showToast('Photo removed.', 'success');
    }
}

// ─── Radio chips ───
document.querySelectorAll('.radio-group').forEach(group => {
    group.querySelectorAll('.radio-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            group.querySelectorAll('.radio-chip').forEach(c => c.classList.remove('checked'));
            chip.classList.add('checked');
        });
    });
});

// ─── Form submit loader ───
document.getElementById('propForm')?.addEventListener('submit', function () {
    document.querySelectorAll('.form-card[style*="display: none"], .f-group[style*="display: none"]').forEach(box => { box.querySelectorAll('input,select,textarea').forEach(el => { if(el.type==='radio'||el.type==='checkbox') el.checked=false; else el.value=''; }); });
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';
    btn.disabled = true;
});

// Initialize price label if editing
const priceInput = document.querySelector('[name=price]');
if (priceInput?.value) showPriceLabel(priceInput.value);
</script>
@endpush
