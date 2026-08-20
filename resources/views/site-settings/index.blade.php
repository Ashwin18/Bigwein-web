@extends('layouts.main')
@section('title') Site Settings @endsection
@section('page-title')
<div class="page-title">
  <div class="row align-items-center">
    <div class="col-12 col-md-6">
      <h4><i class="bi bi-sliders me-2" style="color:#e30620;"></i>Site Settings</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Site Settings</li>
      </ol></nav>
    </div>
    <div class="col-12 col-md-6 text-md-end">
      <span id="savedBadge" style="display:none;background:#DCFCE7;color:#166534;font-size:12px;font-weight:600;padding:5px 14px;border-radius:8px;">
        <i class="bi bi-check-circle-fill me-1"></i>Saved!
      </span>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">
<style>
.ss-card{background:#fff;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06);margin-bottom:20px;}
.ss-head{padding:14px 20px;border-bottom:1px solid #F1F5F9;font-size:14px;font-weight:800;color:#0F172A;display:flex;align-items:center;gap:8px;}
.ss-body{padding:20px;}
.ss-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #F8FAFC;}
.ss-row:last-child{border-bottom:none;}
.ss-label{font-size:13px;font-weight:600;color:#0F172A;}
.ss-sub{font-size:11px;color:#94A3B8;margin-top:2px;}
.f-inp{border:1px solid #E2E8F0;border-radius:8px;padding:7px 12px;font-size:13px;outline:none;width:100%;}
.f-inp:focus{border-color:#E5343A;}
.btn-save{background:#E5343A;color:#fff;border:none;border-radius:9px;padding:8px 20px;font-size:13px;font-weight:700;cursor:pointer;}
</style>

{{-- Announcement Banner --}}
<div class="ss-card">
  <div class="ss-head"><i class="bi bi-megaphone" style="color:#E5343A;"></i> Announcement Banner</div>
  <div class="ss-body">
    @php $ann = $cfg['announcement'] ?? []; @endphp
    <div class="ss-row">
      <div><div class="ss-label">Show Banner</div><div class="ss-sub">Display a site-wide announcement at the top</div></div>
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" id="annShow" {{ !empty($ann['show']) ? 'checked' : '' }}/>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Announcement Text</label>
      <input type="text" id="annText" class="f-inp" value="{{ $ann['text'] ?? '' }}" placeholder="e.g. New listings available in Chennai! Click to explore."/>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Background Color</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" id="annColor" value="{{ $ann['color'] ?? '#E5343A' }}" style="width:40px;height:36px;border-radius:8px;border:1px solid #E2E8F0;cursor:pointer;padding:2px;"/>
          <input type="text" id="annColorHex" class="f-inp" value="{{ $ann['color'] ?? '#E5343A' }}" style="width:100px;"/>
        </div>
      </div>
      <div class="col-md-4">
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Text Color</label>
        <div style="display:flex;gap:8px;align-items:center;">
          <input type="color" id="annTextColor" value="{{ $ann['text_color'] ?? '#ffffff' }}" style="width:40px;height:36px;border-radius:8px;border:1px solid #E2E8F0;cursor:pointer;padding:2px;"/>
          <input type="text" id="annTextColorHex" class="f-inp" value="{{ $ann['text_color'] ?? '#ffffff' }}" style="width:100px;"/>
        </div>
      </div>
      <div class="col-md-4">
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">Link (optional)</label>
        <input type="text" id="annLink" class="f-inp" value="{{ $ann['link'] ?? '' }}" placeholder="/properties"/>
      </div>
    </div>
    <button class="btn-save" onclick="saveAnnouncement()"><i class="bi bi-cloud-check me-1"></i>Save Announcement</button>
  </div>
</div>

{{-- Homepage Sections --}}
<div class="ss-card">
  <div class="ss-head"><i class="bi bi-layout-text-window" style="color:#2563EB;"></i> Homepage Sections</div>
  <div class="ss-body">
    <p style="font-size:12px;color:#64748B;margin-bottom:14px;">Toggle sections to show or hide them on the homepage.</p>
    <div id="sectionsList">
      @foreach($cfg['sections'] ?? [] as $i => $sec)
      <div class="ss-row">
        <div>
          <div class="ss-label">{{ $sec['label'] }}</div>
          <input type="text" class="f-inp mt-1" style="width:260px;" value="{{ $sec['title'] }}"
            onchange="updateSection({{ $i }}, 'title', this.value)" placeholder="Section title"/>
        </div>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" {{ $sec['show'] ? 'checked' : '' }}
            onchange="updateSection({{ $i }}, 'show', this.checked)"/>
        </div>
      </div>
      @endforeach
    </div>
    <button class="btn-save mt-3" onclick="saveSections()"><i class="bi bi-cloud-check me-1"></i>Save Sections</button>
  </div>
</div>

{{-- Property Card Fields --}}
<div class="ss-card">
  <div class="ss-head"><i class="bi bi-card-checklist" style="color:#16A34A;"></i> Property Card Display</div>
  <div class="ss-body">
    <p style="font-size:12px;color:#64748B;margin-bottom:14px;">Choose which fields appear on property cards across the site.</p>
    @foreach($cfg['card_fields'] ?? [] as $i => $field)
    <div class="ss-row">
      <div class="ss-label">{{ $field['label'] }}</div>
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" {{ $field['show'] ? 'checked' : '' }}
          onchange="updateCardField({{ $i }}, this.checked)"/>
      </div>
    </div>
    @endforeach
    <button class="btn-save mt-3" onclick="saveCardFields()"><i class="bi bi-cloud-check me-1"></i>Save</button>
  </div>
</div>

{{-- Listing Filters --}}
<div class="ss-card">
  <div class="ss-head"><i class="bi bi-funnel" style="color:#D97706;"></i> Listing Page Filters</div>
  <div class="ss-body">
    <p style="font-size:12px;color:#64748B;margin-bottom:14px;">Toggle which filters appear on the property listing page.</p>
    @foreach($cfg['listing_filters'] ?? [] as $i => $filter)
    <div class="ss-row">
      <div class="ss-label">{{ $filter['label'] }}</div>
      <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" {{ $filter['show'] ? 'checked' : '' }}
          onchange="updateFilter({{ $i }}, this.checked)"/>
      </div>
    </div>
    @endforeach
    <button class="btn-save mt-3" onclick="saveFilters()"><i class="bi bi-cloud-check me-1"></i>Save</button>
  </div>
</div>

{{-- SEO Settings --}}
<div class="ss-card">
  <div class="ss-head"><i class="bi bi-search" style="color:#7C3AED;"></i> SEO Patterns</div>
  <div class="ss-body">
    <p style="font-size:12px;color:#64748B;margin-bottom:14px;">Use <code>{title}</code>, <code>{city}</code>, <code>{price}</code>, <code>{bhk}</code>, <code>{category}</code> as placeholders.</p>
    @php $seo = $cfg['seo'] ?? []; @endphp
    @foreach([
      ['home_title',    'Homepage Title'],
      ['home_desc',     'Homepage Meta Description'],
      ['property_title','Property Page Title'],
      ['property_desc', 'Property Meta Description'],
      ['listing_title', 'Listing Page Title'],
    ] as [$key, $label])
    <div style="margin-bottom:12px;">
      <label style="font-size:12px;font-weight:600;display:block;margin-bottom:5px;">{{ $label }}</label>
      <input type="text" id="seo_{{ $key }}" class="f-inp" value="{{ $seo[$key] ?? '' }}" placeholder="{{ $label }}"/>
    </div>
    @endforeach
    <button class="btn-save" onclick="saveSeo()"><i class="bi bi-cloud-check me-1"></i>Save SEO</button>
  </div>
</div>

</section>
@endsection

@section('script')
<script>
const CSRF   = '{{ csrf_token() }}';
const saveUrl = '{{ url("site-settings/save") }}';
let sectionsData   = @json($cfg['sections'] ?? []);
let cardFieldsData = @json($cfg['card_fields'] ?? []);
let filtersData    = @json($cfg['listing_filters'] ?? []);

async function apiSave(key, value) {
  const r = await fetch(saveUrl, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
    body: JSON.stringify({key, value})
  });
  const d = await r.json();
  if (d.success) { showSaved(); }
  else if(window.toastr) toastr.error(d.message||'Failed');
}

function showSaved() {
  const b = document.getElementById('savedBadge');
  b.style.display='inline-block'; clearTimeout(b._t);
  b._t = setTimeout(()=>b.style.display='none', 2500);
}

// Announcement
document.getElementById('annColor').addEventListener('input', e => document.getElementById('annColorHex').value = e.target.value);
document.getElementById('annTextColor').addEventListener('input', e => document.getElementById('annTextColorHex').value = e.target.value);

function saveAnnouncement() {
  apiSave('announcement', {
    show:       document.getElementById('annShow').checked,
    text:       document.getElementById('annText').value,
    color:      document.getElementById('annColorHex').value || document.getElementById('annColor').value,
    text_color: document.getElementById('annTextColorHex').value || document.getElementById('annTextColor').value,
    link:       document.getElementById('annLink').value,
    dismissible:true
  });
}

// Sections
function updateSection(i, key, val) { sectionsData[i][key] = val; }
function saveSections() { apiSave('sections', sectionsData); }

// Card fields
function updateCardField(i, val) { cardFieldsData[i].show = val; }
function saveCardFields() { apiSave('card_fields', cardFieldsData); }

// Filters
function updateFilter(i, val) { filtersData[i].show = val; }
function saveFilters() { apiSave('listing_filters', filtersData); }

// SEO
function saveSeo() {
  const keys = ['home_title','home_desc','property_title','property_desc','listing_title'];
  const seo = {};
  keys.forEach(k => { const el = document.getElementById('seo_'+k); if(el) seo[k]=el.value; });
  apiSave('seo', seo);
}
</script>
@endsection
