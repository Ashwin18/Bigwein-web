@extends('layouts.main')
@section('title') Search Widget Settings @endsection
@section('page-title')
<div class="page-title">
  <div class="row">
    <div class="col-12 col-md-6 order-md-1 order-last">
      <h4><i class="bi bi-sliders me-2" style="color:#e30620;"></i>Search Widget Settings</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Search Settings</li>
      </ol></nav>
    </div>
    <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-md-end align-items-center gap-2 mt-2 mt-md-0">
      <button class="btn btn-sm btn-outline-secondary" onclick="resetDefaults()" style="border-radius:8px;">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
      </button>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">

  {{-- ═══ MASTER TOGGLE ═══ --}}
  <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);border-left:4px solid #e30620!important;">
    <div class="card-body p-4">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h5 style="font-weight:800;color:#111;margin-bottom:4px;"><i class="bi bi-toggle-on me-2" style="color:#e30620;"></i>Dynamic Search Mode</h5>
          <p style="font-size:13px;color:#6B7280;margin:0;">
            <span class="text-success fw-bold" id="mode-on" style="{{ $cfg['enabled'] ? '' : 'display:none;' }}">
              <i class="bi bi-check-circle-fill"></i> <strong>ENABLED</strong> — Search widget reads from your settings below
            </span>
            <span style="{{ $cfg['enabled'] ? 'display:none;' : '' }}" id="mode-off">
              <i class="bi bi-slash-circle"></i> <strong>DISABLED</strong> — Search widget uses hardcoded defaults (safe mode)
            </span>
          </p>
        </div>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" id="masterToggle" role="switch"
            {{ $cfg['enabled'] ? 'checked' : '' }}
            onchange="toggleDynamic(this.checked)"
            style="cursor:pointer;width:52px;height:26px;">
        </div>
      </div>
    </div>
  </div>

  <div id="settingsBody">
    @if(!$cfg['enabled'])
    <div class="alert mb-4" style="background:#FEF9C3;border:1px solid #FDE68A;border-radius:12px;padding:14px 18px;font-size:13px;color:#92400E;">
      <i class="bi bi-info-circle-fill me-2"></i>
      <strong>Dynamic mode is OFF.</strong> Changes below are saved but won't affect the homepage until you enable Dynamic Search Mode above.
    </div>
    @endif

    {{-- ═══ TABS ═══ --}}
    <div class="card border-0 mb-4" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
      <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:16px 22px;">
        <h6 style="font-weight:800;margin:0;"><i class="bi bi-layout-tabs me-2" style="color:#e30620;"></i>Search Tabs</h6>
        <div class="d-flex align-items-center gap-3">
          <small class="text-muted">Drag <i class="bi bi-grip-vertical"></i> to reorder · toggle to show/hide</small>
          <span id="tabsSavedBadge" style="display:none;font-size:11px;background:#DCFCE7;color:#166534;padding:3px 9px;border-radius:8px;font-weight:600;">
            <i class="bi bi-check-circle-fill"></i> Saved
          </span>
          <button onclick="toggleAddTabForm()" id="addTabBtn" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:12px;font-weight:600;">
            <i class="bi bi-plus-circle me-1"></i> Add Tab
          </button>
        </div>
      </div>
      <div class="card-body p-4">
        <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#92400E;">
          <i class="bi bi-info-circle me-1"></i>
          Drag rows to change the order tabs appear on the homepage search bar. Changes save automatically on drop.
        </div>
        {{-- Inline Add Tab Form --}}
        <div id="addTabForm" style="display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:16px;margin-bottom:12px;">
          <div style="font-size:13px;font-weight:700;color:#166534;margin-bottom:12px;"><i class="bi bi-plus-circle me-2"></i>Add New Search Tab</div>
          <div class="row g-2">
            <div class="col-md-4">
              <label style="font-size:11px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Tab Label *</label>
              <input type="text" id="newTabLabel" class="form-control form-control-sm" placeholder="e.g. Luxury"
                style="border-radius:8px;" oninput="autoFillSlug(this.value)"/>
            </div>
            <div class="col-md-3">
              <label style="font-size:11px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Slug (URL key) *</label>
              <input type="text" id="newTabSlug" class="form-control form-control-sm" placeholder="e.g. luxury"
                style="border-radius:8px;"/>
            </div>
            <div class="col-md-3">
              <label style="font-size:11px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Icon class</label>
              <div style="display:flex;gap:6px;align-items:center;">
                <input type="text" id="newTabIcon" class="form-control form-control-sm" placeholder="fa-star"
                  style="border-radius:8px;" value="fa-star" oninput="previewIcon(this.value)"/>
                <i id="iconPreview" class="fa-solid fa-star" style="color:#e30620;font-size:16px;flex-shrink:0;"></i>
              </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button onclick="confirmAddTab()" class="btn btn-sm w-100" style="background:#E5343A;color:#fff;border-radius:8px;font-weight:700;">
                <i class="bi bi-check-circle me-1"></i> Add
              </button>
            </div>
          </div>
          <div style="margin-top:8px;">
            <button onclick="toggleAddTabForm()" style="background:none;border:none;font-size:12px;color:#64748B;cursor:pointer;">
              <i class="bi bi-x me-1"></i> Cancel
            </button>
          </div>
        </div>

        <div id="tabs-list" style="display:flex;flex-direction:column;gap:8px;">
          @foreach($cfg['tabs'] as $i => $tab)
          <div class="tab-sort-row" data-slug="{{ $tab['slug'] }}" data-label="{{ $tab['label'] }}" data-icon="{{ $tab['icon'] }}" data-active="{{ $tab['active'] ? '1' : '0' }}"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#F9FAFB;border-radius:12px;border:1px solid #F1F5F9;cursor:default;transition:box-shadow .15s;">

            {{-- Drag handle --}}
            <span class="drag-handle" style="cursor:grab;color:#CBD5E1;font-size:18px;flex-shrink:0;" title="Drag to reorder">
              <i class="bi bi-grip-vertical"></i>
            </span>

            {{-- Sequence badge --}}
            <span class="seq-badge" style="width:24px;height:24px;border-radius:50%;background:#E5343A;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              {{ $i + 1 }}
            </span>

            {{-- Icon --}}
            <i class="fa-solid {{ $tab['icon'] }}" style="color:#e30620;font-size:16px;width:20px;flex-shrink:0;"></i>

            {{-- Label + slug --}}
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:700;color:#0F172A;">{{ $tab['label'] }}</div>
              <div style="font-size:11px;color:#94A3B8;margin-top:1px;">/{{ $tab['slug'] }}</div>
            </div>

            {{-- Status pill --}}
            <span class="tab-status-pill" style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:{{ $tab['active'] ? '#DCFCE7' : '#F3F4F6' }};color:{{ $tab['active'] ? '#166534' : '#9CA3AF' }};">
              {{ $tab['active'] ? 'Visible' : 'Hidden' }}
            </span>

            {{-- Toggle --}}
            <div class="form-check form-switch mb-0">
              <input class="form-check-input tab-toggle" type="checkbox" {{ $tab['active'] ? 'checked' : '' }}
                onchange="onTabToggle(this)" style="cursor:pointer;">
            </div>

            {{-- Delete --}}
            <button onclick="deleteTab(this)" title="Delete tab"
              style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:4px 6px;border-radius:6px;transition:all .15s;flex-shrink:0;"
              onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'"
              onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
              <i class="bi bi-trash3" style="font-size:14px;"></i>
            </button>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- ═══ SUB-TYPES ═══ --}}
    <div class="row g-4 mb-4">
      {{-- BUY sub-types --}}
      <div class="col-md-6">
        <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:16px 22px;">
            <h6 style="font-weight:800;margin:0;"><i class="bi bi-house me-2" style="color:#2563EB;"></i>Buy Sub-Types</h6>
            <button class="btn btn-sm btn-outline-primary" onclick="addSubtype('buy')" style="border-radius:8px;font-size:11px;">+ Add</button>
          </div>
          <div class="card-body p-3" id="buy-subtypes-list">
            @foreach($cfg['buy_subtypes'] as $i => $st)
            <div class="d-flex align-items-center gap-2 mb-2 p-2" style="background:#F9FAFB;border-radius:8px;" id="buy-st-{{ $i }}">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" {{ $st['active'] ? 'checked' : '' }}
                  onchange="toggleSubtype('buy',{{ $i }},this.checked)" style="cursor:pointer;">
              </div>
              <input type="text" value="{{ $st['label'] }}" class="form-control form-control-sm"
                style="border-radius:7px;font-size:12px;" onchange="updateSubtype('buy',{{ $i }},'label',this.value)"/>
              <input type="text" value="{{ $st['slug'] }}" class="form-control form-control-sm"
                style="border-radius:7px;font-size:11px;color:#94A3B8;max-width:90px;" placeholder="slug"
                onchange="updateSubtype('buy',{{ $i }},'slug',this.value)"/>
              <button class="btn btn-sm btn-outline-danger" onclick="removeSubtype('buy',{{ $i }})" style="border-radius:7px;padding:3px 8px;"><i class="bi bi-trash"></i></button>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      {{-- RENT sub-types --}}
      <div class="col-md-6">
        <div class="card border-0 h-100" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:16px 22px;">
            <h6 style="font-weight:800;margin:0;"><i class="bi bi-key me-2" style="color:#16A34A;"></i>Rent Sub-Types</h6>
            <button class="btn btn-sm btn-outline-success" onclick="addSubtype('rent')" style="border-radius:8px;font-size:11px;">+ Add</button>
          </div>
          <div class="card-body p-3" id="rent-subtypes-list">
            @foreach($cfg['rent_subtypes'] as $i => $st)
            <div class="d-flex align-items-center gap-2 mb-2 p-2" style="background:#F9FAFB;border-radius:8px;" id="rent-st-{{ $i }}">
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" {{ $st['active'] ? 'checked' : '' }}
                  onchange="toggleSubtype('rent',{{ $i }},this.checked)" style="cursor:pointer;">
              </div>
              <input type="text" value="{{ $st['label'] }}" class="form-control form-control-sm"
                style="border-radius:7px;font-size:12px;" onchange="updateSubtype('rent',{{ $i }},'label',this.value)"/>
              <input type="text" value="{{ $st['slug'] }}" class="form-control form-control-sm"
                style="border-radius:7px;font-size:11px;color:#94A3B8;max-width:90px;" placeholder="slug"
                onchange="updateSubtype('rent',{{ $i }},'slug',this.value)"/>
              <button class="btn btn-sm btn-outline-danger" onclick="removeSubtype('rent',{{ $i }})" style="border-radius:7px;padding:3px 8px;"><i class="bi bi-trash"></i></button>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- ═══ FILTER OPTIONS ═══ --}}
    <div class="row g-4 mb-4">
      {{-- BHK Options --}}
      <div class="col-md-4">
        <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:14px 18px;">
            <h6 style="font-weight:800;margin:0;font-size:14px;"><i class="bi bi-building me-2" style="color:#e30620;"></i>BHK Options</h6>
            <button class="btn btn-sm btn-outline-danger" onclick="addChipOption('bhk_options')" style="border-radius:7px;font-size:11px;">+ Add</button>
          </div>
          <div class="card-body p-3">
            <div id="bhk_options-list" class="d-flex flex-wrap gap-2">
              @foreach($cfg['bhk_options'] as $i => $opt)
              <div class="d-flex align-items-center gap-1 p-1 ps-2" style="background:#FFF1F3;border:1px solid #FECDD3;border-radius:20px;">
                <span id="bhk_options-{{ $i }}" style="font-size:12px;font-weight:600;color:#C4272D;min-width:50px;" contenteditable="true" onblur="updateChip('bhk_options',{{ $i }},this.textContent)">{{ $opt }}</span>
                <button onclick="removeChip('bhk_options',{{ $i }})" style="background:none;border:none;color:#C4272D;cursor:pointer;padding:0 4px;font-size:12px;">✕</button>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      {{-- Property Status --}}
      <div class="col-md-4">
        <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:14px 18px;">
            <h6 style="font-weight:800;margin:0;font-size:14px;"><i class="bi bi-check-circle me-2" style="color:#e30620;"></i>Property Status</h6>
            <button class="btn btn-sm btn-outline-danger" onclick="addChipOption('prop_statuses')" style="border-radius:7px;font-size:11px;">+ Add</button>
          </div>
          <div class="card-body p-3">
            <div id="prop_statuses-list" class="d-flex flex-column gap-2">
              @foreach($cfg['prop_statuses'] as $i => $opt)
              <div class="d-flex align-items-center gap-2 p-2" style="background:#F9FAFB;border-radius:8px;">
                <span style="font-size:13px;flex:1;" id="prop_statuses-{{ $i }}" contenteditable="true" onblur="updateChip('prop_statuses',{{ $i }},this.textContent)">{{ $opt }}</span>
                <button onclick="removeChip('prop_statuses',{{ $i }})" style="background:none;border:none;color:#E30620;cursor:pointer;font-size:12px;">✕</button>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      {{-- Commercial Types --}}
      <div class="col-md-4">
        <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:14px 18px;">
            <h6 style="font-weight:800;margin:0;font-size:14px;"><i class="bi bi-shop me-2" style="color:#e30620;"></i>Commercial Types</h6>
            <button class="btn btn-sm btn-outline-danger" onclick="addChipOption('commercial_types')" style="border-radius:7px;font-size:11px;">+ Add</button>
          </div>
          <div class="card-body p-3">
            <div id="commercial_types-list" class="d-flex flex-column gap-2">
              @foreach($cfg['commercial_types'] as $i => $opt)
              <div class="d-flex align-items-center gap-2 p-2" style="background:#F9FAFB;border-radius:8px;">
                <span style="font-size:13px;flex:1;" id="commercial_types-{{ $i }}" contenteditable="true" onblur="updateChip('commercial_types',{{ $i }},this.textContent)">{{ $opt }}</span>
                <button onclick="removeChip('commercial_types',{{ $i }})" style="background:none;border:none;color:#E30620;cursor:pointer;font-size:12px;">✕</button>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ═══ BUDGET RANGES ═══ --}}
    <div class="row g-4">
      @foreach(['budget_buy'=>['Buy Budget Ranges','#2563EB'],'budget_rent'=>['Rent Budget Ranges','#16A34A']] as $bkey=>$binfo)
      <div class="col-md-6">
        <div class="card border-0" style="border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);">
          <div class="card-header bg-white d-flex align-items-center justify-content-between" style="border-radius:16px 16px 0 0;padding:14px 18px;">
            <h6 style="font-weight:800;margin:0;font-size:14px;color:{{ $binfo[1] }};"><i class="bi bi-currency-rupee me-2"></i>{{ $binfo[0] }}</h6>
            <button class="btn btn-sm btn-outline-secondary" onclick="addBudget('{{ $bkey }}')" style="border-radius:7px;font-size:11px;">+ Add Range</button>
          </div>
          <div class="card-body p-3" id="{{ $bkey }}-list">
            @foreach($cfg[$bkey] as $i => $b)
            <div class="d-flex align-items-center gap-2 mb-2" id="{{ $bkey }}-{{ $i }}">
              <input type="text" value="{{ $b['label'] }}" placeholder="Label" class="form-control form-control-sm" style="border-radius:7px;font-size:12px;" onchange="updateBudget('{{ $bkey }}',{{ $i }},'label',this.value)"/>
              <input type="number" value="{{ $b['min'] }}" placeholder="Min" class="form-control form-control-sm" style="border-radius:7px;font-size:12px;max-width:90px;" onchange="updateBudget('{{ $bkey }}',{{ $i }},'min',this.value)"/>
              <input type="number" value="{{ $b['max'] }}" placeholder="Max" class="form-control form-control-sm" style="border-radius:7px;font-size:12px;max-width:90px;" onchange="updateBudget('{{ $bkey }}',{{ $i }},'max',this.value)"/>
              <button onclick="removeBudget('{{ $bkey }}',{{ $i }})" class="btn btn-sm btn-outline-danger" style="border-radius:7px;padding:3px 8px;"><i class="bi bi-trash"></i></button>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach
    </div>

  </div>{{-- end settingsBody --}}

</section>
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

@section('script')
<script>
const CSRF = '{{ csrf_token() }}';
const saveUrl  = '{{ url("search-settings/save") }}';
const toggleUrl= '{{ url("search-settings/toggle") }}';
const resetUrl = '{{ url("search-settings/reset") }}';

// In-memory config state
let cfg = @json($cfg);

async function post(url, data) {
  const r = await fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
    body: JSON.stringify(data)
  });
  return r.json();
}

async function saveCfg(key) {
  const res = await post(saveUrl, {key, value: cfg[key]});
  if (res.success) toastr.success(res.message);
  else toastr.error(res.message || 'Save failed');
}

// ── Master toggle ──
async function toggleDynamic(enabled) {
  try {
    const res = await post(toggleUrl, {enabled});
    if (res && res.success) {
      toastr.success(res.message || 'Search mode updated!');
    } else {
      toastr.success(enabled ? 'Dynamic search enabled!' : 'Dynamic search disabled!');
    }
  } catch(e) {
    // Still proceed even if response parse fails
  }
  // Reload page to apply changes (most reliable)
  setTimeout(() => location.reload(), 800);
}

// ── Tabs ──
function toggleTab(i, active) {
  cfg.tabs[i].active = active;
  saveCfg('tabs');
}

// ── Sub-types ──
function toggleSubtype(tab, i, active) {
  cfg[tab+'_subtypes'][i].active = active;
  saveCfg(tab+'_subtypes');
}
function updateSubtype(tab, i, field, val) {
  cfg[tab+'_subtypes'][i][field] = val.trim();
  saveCfg(tab+'_subtypes');
}
function removeSubtype(tab, i) {
  cfg[tab+'_subtypes'].splice(i, 1);
  saveCfg(tab+'_subtypes');
  setTimeout(() => location.reload(), 600);
}
function addSubtype(tab) {
  const label = prompt('Enter sub-type label (e.g. "Studio Apartment"):');
  if (!label) return;
  const slug = label.toLowerCase().replace(/[^a-z0-9]/g,'');
  cfg[tab+'_subtypes'].push({label, slug, active: true});
  saveCfg(tab+'_subtypes');
  setTimeout(() => location.reload(), 600);
}

// ── Chip options (BHK, Status, Commercial) ──
function updateChip(key, i, val) {
  cfg[key][i] = val.trim();
  saveCfg(key);
}
function removeChip(key, i) {
  cfg[key].splice(i, 1);
  saveCfg(key);
  setTimeout(() => location.reload(), 600);
}
function addChipOption(key) {
  const placeholders = {
    bhk_options:'e.g. 6 BHK',
    prop_statuses:'e.g. Resale',
    commercial_types:'e.g. Retail Space'
  };
  const val = prompt('Add new option:', placeholders[key] || '');
  if (!val) return;
  cfg[key].push(val.trim());
  saveCfg(key);
  setTimeout(() => location.reload(), 600);
}

// ── Budget ──
function updateBudget(key, i, field, val) {
  cfg[key][i][field] = val;
  saveCfg(key);
}
function removeBudget(key, i) {
  cfg[key].splice(i, 1);
  saveCfg(key);
  setTimeout(() => location.reload(), 600);
}
function addBudget(key) {
  cfg[key].push({label:'New Range', min:'', max:''});
  saveCfg(key);
  setTimeout(() => location.reload(), 600);
}

// ── Reset ──
async function resetDefaults() {
  if (!confirm('Reset all search settings to defaults?')) return;
  const res = await post(resetUrl, {});
  if (res.success) { toastr.success(res.message); setTimeout(() => location.reload(), 800); }
}

/* ─────────── TABS SORTABLE ─────────────────────────────── */
(function initTabsSortable() {
  const list = document.getElementById('tabs-list');
  if (!list || typeof Sortable === 'undefined') return;

  Sortable.create(list, {
    handle      : '.drag-handle',
    animation   : 150,
    ghostClass  : 'sortable-ghost',
    chosenClass : 'sortable-chosen',
    onStart: function(evt) {
      evt.item.style.boxShadow = '0 8px 24px rgba(0,0,0,.12)';
    },
    onEnd: function(evt) {
      evt.item.style.boxShadow = '';
      updateSeqBadges();
      saveTabsOrder();
    }
  });

  // Add drag-hover styles dynamically
  const style = document.createElement('style');
  style.textContent = `
    .sortable-ghost  { opacity:.4; border:2px dashed #E5343A !important; }
    .sortable-chosen { box-shadow:0 8px 24px rgba(229,52,58,.2) !important; border-color:#E5343A !important; }
    .drag-handle:active { cursor:grabbing; }
  `;
  document.head.appendChild(style);
})();

function updateSeqBadges() {
  document.querySelectorAll('#tabs-list .tab-sort-row').forEach((row, idx) => {
    const badge = row.querySelector('.seq-badge');
    if (badge) badge.textContent = idx + 1;
  });
}

function onTabToggle(checkbox) {
  const row  = checkbox.closest('.tab-sort-row');
  const pill = row.querySelector('.tab-status-pill');
  const active = checkbox.checked;
  row.dataset.active = active ? '1' : '0';
  if (pill) {
    pill.textContent  = active ? 'Visible' : 'Hidden';
    pill.style.background = active ? '#DCFCE7' : '#F3F4F6';
    pill.style.color      = active ? '#166534' : '#9CA3AF';
  }
  saveTabsOrder();
}

function buildTabsFromDom() {
  const tabs = [];
  document.querySelectorAll('#tabs-list .tab-sort-row').forEach(row => {
    tabs.push({
      slug  : row.dataset.slug,
      label : row.dataset.label,
      icon  : row.dataset.icon,
      active: row.dataset.active === '1',
    });
  });
  return tabs;
}

function saveTabsOrder() {
  cfg.tabs = buildTabsFromDom(); // Update in-memory cfg first
  saveCfg('tabs');               // Then save (saveCfg reads cfg.tabs)

  // Show saved badge briefly
  const badge = document.getElementById('tabsSavedBadge');
  if (badge) {
    badge.style.display = 'inline-block';
    clearTimeout(badge._t);
    badge._t = setTimeout(() => badge.style.display = 'none', 2000);
  }
}

function deleteTab(btn) {
  const row   = btn.closest('.tab-sort-row');
  const label = row.dataset.label;
  if (!confirm('Delete the "' + label + '" tab? It will no longer appear in the search widget.')) return;
  row.style.transition = 'opacity .3s';
  row.style.opacity    = '0';
  setTimeout(() => {
    row.remove();
    updateSeqBadges();
    saveTabsOrder();
  }, 300);
}

function toggleAddTabForm() {
  const form = document.getElementById('addTabForm');
  const btn  = document.getElementById('addTabBtn');
  const isOpen = form.style.display !== 'none';
  form.style.display = isOpen ? 'none' : 'block';
  btn.innerHTML = isOpen
    ? '<i class="bi bi-plus-circle me-1"></i> Add Tab'
    : '<i class="bi bi-x-circle me-1"></i> Cancel';
  if (!isOpen) document.getElementById('newTabLabel').focus();
}

function autoFillSlug(val) {
  const slug = document.getElementById('newTabSlug');
  slug.value = val.toLowerCase().replace(/[^a-z0-9]/g, '');
}

function previewIcon(val) {
  const el = document.getElementById('iconPreview');
  el.className = 'fa-solid ' + (val.trim() || 'fa-circle');
}

function confirmAddTab() {
  const label = document.getElementById('newTabLabel').value.trim();
  const slug  = document.getElementById('newTabSlug').value.trim();
  const icon  = document.getElementById('newTabIcon').value.trim() || 'fa-circle';

  if (!label) { document.getElementById('newTabLabel').focus(); return; }
  if (!slug)  { document.getElementById('newTabSlug').focus();  return; }

  addNewTab(label, slug, icon);
  // Reset form
  document.getElementById('newTabLabel').value = '';
  document.getElementById('newTabSlug').value  = '';
  document.getElementById('newTabIcon').value  = 'fa-star';
  document.getElementById('iconPreview').className = 'fa-solid fa-star';
  toggleAddTabForm();
}

function addNewTab(label, slug, icon) {

  icon = icon || 'fa-circle';
  const newRow = document.createElement('div');
  newRow.className = 'tab-sort-row';
  newRow.dataset.slug   = slug;
  newRow.dataset.label  = label;
  newRow.dataset.icon   = icon || 'fa-circle';
  newRow.dataset.active = '1';
  newRow.style.cssText  = 'display:flex;align-items:center;gap:12px;padding:12px 16px;background:#F9FAFB;border-radius:12px;border:1px solid #F1F5F9;cursor:default;transition:box-shadow .15s;';

  const seq = document.querySelectorAll('#tabs-list .tab-sort-row').length + 1;
  newRow.innerHTML = `
    <span class="drag-handle" style="cursor:grab;color:#CBD5E1;font-size:18px;flex-shrink:0;" title="Drag to reorder">
      <i class="bi bi-grip-vertical"></i>
    </span>
    <span class="seq-badge" style="width:24px;height:24px;border-radius:50%;background:#E5343A;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${seq}</span>
    <i class="fa-solid ${icon || 'fa-circle'}" style="color:#e30620;font-size:16px;width:20px;flex-shrink:0;"></i>
    <div style="flex:1;">
      <div style="font-size:14px;font-weight:700;color:#0F172A;">${label}</div>
      <div style="font-size:11px;color:#94A3B8;margin-top:1px;">/${slug}</div>
    </div>
    <span class="tab-status-pill" style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:#DCFCE7;color:#166534;">Visible</span>
    <div class="form-check form-switch mb-0">
      <input class="form-check-input tab-toggle" type="checkbox" checked onchange="onTabToggle(this)" style="cursor:pointer;">
    </div>
    <button onclick="deleteTab(this)" title="Delete tab"
      style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:4px 6px;border-radius:6px;transition:all .15s;flex-shrink:0;"
      onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'"
      onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
      <i class="bi bi-trash3" style="font-size:14px;"></i>
    </button>
  `;

  document.getElementById('tabs-list').appendChild(newRow);
  updateSeqBadges();
  saveTabsOrder();
}
</script>
@endsection
