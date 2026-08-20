@extends('layouts.main')
@section('title') Search Settings @endsection
@section('page-title')
<div class="page-title">
  <div class="row align-items-center">
    <div class="col-12 col-md-6">
      <h4><i class="bi bi-search me-2" style="color:#e30620;"></i>Search Settings</h4>
      <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Search Settings</li>
      </ol></nav>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
      <span id="globalSavedBadge" style="display:none;font-size:12px;background:#DCFCE7;color:#166534;padding:5px 12px;border-radius:8px;font-weight:600;">
        <i class="bi bi-check-circle-fill"></i> All changes saved
      </span>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="section">
<style>
.ss-card{border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.07);margin-bottom:24px;background:#fff;}
.ss-card .card-header{background:#fff;border-radius:16px 16px 0 0 !important;padding:14px 20px;border-bottom:1px solid #F1F5F9;}
.tab-row{display:flex;align-items:center;gap:10px;padding:10px 14px;background:#F9FAFB;border-radius:10px;border:1px solid #F1F5F9;margin-bottom:6px;transition:box-shadow .15s;}
.tab-row.sortable-ghost{opacity:.4;border:2px dashed #E5343A!important;}
.drag-hdl{cursor:grab;color:#CBD5E1;font-size:18px;flex-shrink:0;}
.drag-hdl:active{cursor:grabbing;}
.seq-badge{width:22px;height:22px;border-radius:50%;background:#E5343A;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.tab-subtypes-panel{display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:0 0 10px 10px;padding:14px;margin-top:-6px;margin-bottom:6px;}
.subtype-row{display:flex;align-items:center;gap:8px;padding:7px 10px;background:#fff;border-radius:8px;border:1px solid #E2E8F0;margin-bottom:5px;}
.subtype-row.sortable-ghost{opacity:.4;border:2px dashed #16A34A!important;}
.add-subtype-form{display:none;margin-top:10px;padding:10px;background:#fff;border-radius:8px;border:1px solid #E2E8F0;}
.pill-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;border:1px solid #E2E8F0;font-size:12px;font-weight:600;background:#F8FAFC;cursor:pointer;margin:3px;}
.pill-chip.active{background:#E5343A;color:#fff;border-color:#E5343A;}
.f-inp-sm{border:1px solid #E2E8F0;border-radius:8px;padding:6px 10px;font-size:12px;outline:none;width:100%;transition:border-color .15s;}
.f-inp-sm:focus{border-color:#16A34A;}
</style>

{{-- ─── SEARCH TABS ────────────────────────────────────────────────────── --}}
<div class="ss-card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div style="font-weight:800;font-size:15px;display:flex;align-items:center;gap:7px;">
      <i class="bi bi-layout-tabs" style="color:#e30620;"></i> Search Tabs
    </div>
    <div class="d-flex align-items-center gap-2">
      <small class="text-muted d-none d-md-inline">Drag <i class="bi bi-grip-vertical"></i> to reorder · toggle to show/hide</small>
      <button onclick="toggleAddTabForm()" id="addTabBtn" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:12px;font-weight:600;">
        <i class="bi bi-plus-circle me-1"></i> Add Tab
      </button>
    </div>
  </div>
  <div class="card-body p-4">
    <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#92400E;">
      <i class="bi bi-info-circle me-1"></i> Drag rows to reorder. Click <strong>▸ Subtypes</strong> to manage each tab's sub-categories.
    </div>

    {{-- Add Tab Form --}}
    <div id="addTabForm" style="display:none;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:14px;margin-bottom:12px;">
      <div style="font-size:13px;font-weight:700;color:#166534;margin-bottom:10px;"><i class="bi bi-plus-circle me-2"></i>Add New Tab</div>
      <div class="row g-2">
        <div class="col-md-3"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:4px;">Tab Label *</label>
          <input id="newTabLabel" type="text" class="f-inp-sm" placeholder="e.g. Lease" oninput="autoSlug(this)"/></div>
        <div class="col-md-2"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:4px;">Slug *</label>
          <input id="newTabSlug" type="text" class="f-inp-sm" placeholder="lease"/></div>
        <div class="col-md-3"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:4px;">Icon class</label>
          <div style="display:flex;gap:6px;align-items:center;">
            <input id="newTabIcon" type="text" class="f-inp-sm" value="fa-star" oninput="document.getElementById('iconPrev').className='fa-solid '+this.value"/>
            <i id="iconPrev" class="fa-solid fa-star" style="color:#e30620;font-size:16px;flex-shrink:0;"></i>
          </div></div>
        <div class="col-md-2 d-flex align-items-end">
          <button onclick="confirmAddTab()" class="btn btn-sm w-100" style="background:#16A34A;color:#fff;border-radius:8px;font-weight:700;">
            <i class="bi bi-check2 me-1"></i>Add
          </button></div>
        <div class="col-md-2 d-flex align-items-end">
          <button onclick="toggleAddTabForm()" class="btn btn-sm btn-light w-100" style="border-radius:8px;">Cancel</button></div>
      </div>
    </div>

    {{-- Tab Rows --}}
    <div id="tabsList">
      @foreach($cfg['tabs'] as $i => $tab)
      @php $subs = $cfg['tab_subtypes'][$tab['slug']] ?? []; @endphp
      <div class="tab-row tab-sort-row"
        data-slug="{{ $tab['slug'] }}"
        data-label="{{ $tab['label'] }}"
        data-icon="{{ $tab['icon'] }}"
        data-active="{{ $tab['active'] ? '1' : '0' }}">

        <span class="drag-hdl"><i class="bi bi-grip-vertical"></i></span>
        <span class="seq-badge">{{ $i+1 }}</span>
        <i class="fa-solid {{ $tab['icon'] }}" style="color:#e30620;font-size:15px;width:18px;flex-shrink:0;"></i>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:700;color:#0F172A;">{{ $tab['label'] }}</div>
          <div style="font-size:11px;color:#94A3B8;">/{{ $tab['slug'] }}</div>
        </div>
        <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:{{ $tab['active']?'#DCFCE7':'#F3F4F6' }};color:{{ $tab['active']?'#166534':'#9CA3AF' }};">
          {{ $tab['active']?'Visible':'Hidden' }}
        </span>
        <button onclick="toggleSubtypesPanel(this, '{{ $tab['slug'] }}')"
          style="background:#EFF6FF;border:none;border-radius:8px;padding:5px 11px;font-size:12px;font-weight:600;color:#1D4ED8;cursor:pointer;white-space:nowrap;">
          ▸ Subtypes <span style="background:#DBEAFE;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px;">{{ count($subs) }}</span>
        </button>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input tab-toggle" type="checkbox" {{ $tab['active']?'checked':'' }}
            onchange="onTabToggle(this)" style="cursor:pointer;"/>
        </div>
        <button onclick="deleteTab(this)" title="Delete"
          style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:4px 6px;border-radius:6px;flex-shrink:0;"
          onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'"
          onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
          <i class="bi bi-trash3" style="font-size:13px;"></i>
        </button>
      </div>

      {{-- Subtypes Panel for this tab --}}
      <div class="tab-subtypes-panel" id="subtypes-{{ $tab['slug'] }}">
        <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:10px;">
          <i class="bi bi-list-ul me-1"></i> Subtypes for "{{ $tab['label'] }}"
          <span style="color:#6B7280;font-weight:500;"> — drag to reorder, toggle to show/hide</span>
        </div>
        <div id="sublist-{{ $tab['slug'] }}" class="subtype-sort-list" data-tab="{{ $tab['slug'] }}">
          @foreach($subs as $si => $sub)
          <div class="subtype-row"
            data-slug="{{ $sub['slug'] }}"
            data-label="{{ $sub['label'] }}"
            data-active="{{ $sub['active'] ? '1' : '0' }}">
            <span style="cursor:grab;color:#CBD5E1;font-size:14px;flex-shrink:0;"><i class="bi bi-grip-vertical"></i></span>
            <span style="font-size:12px;font-weight:600;color:#0F172A;flex:1;">{{ $sub['label'] }}</span>
            <span style="font-size:10px;color:#94A3B8;">/{{ $sub['slug'] }}</span>
            <div class="form-check form-switch mb-0 ms-1">
              <input class="form-check-input subtype-toggle" type="checkbox"
                {{ $sub['active']?'checked':'' }}
                onchange="onSubtypeToggle(this, '{{ $tab['slug'] }}')"
                style="cursor:pointer;"/>
            </div>
            <button onclick="deleteSubtype(this, '{{ $tab['slug'] }}')"
              style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:3px 5px;border-radius:5px;flex-shrink:0;"
              onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'"
              onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
              <i class="bi bi-x-lg" style="font-size:11px;"></i>
            </button>
          </div>
          @endforeach
        </div>
        {{-- Add Subtype --}}
        <button onclick="toggleAddSubForm('{{ $tab['slug'] }}')"
          style="background:none;border:1px dashed #BBF7D0;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;color:#16A34A;cursor:pointer;width:100%;margin-top:6px;">
          <i class="bi bi-plus-circle me-1"></i> Add Subtype
        </button>
        <div class="add-subtype-form" id="addsub-{{ $tab['slug'] }}">
          <div style="display:flex;gap:8px;">
            <input type="text" id="newsubl-{{ $tab['slug'] }}" class="f-inp-sm" placeholder="Label (e.g. Studio)"
              oninput="document.getElementById('newsugs-{{ $tab['slug'] }}').value=this.value.toLowerCase().replace(/[^a-z0-9]/g,'')"/>
            <input type="text" id="newsugs-{{ $tab['slug'] }}" class="f-inp-sm" placeholder="slug" style="width:120px;"/>
            <button onclick="confirmAddSubtype('{{ $tab['slug'] }}')"
              style="background:#16A34A;color:#fff;border:none;border-radius:8px;padding:6px 16px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">Add</button>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</div>

{{-- ─── BHK OPTIONS ───────────────────────────────────────────────────── --}}
<div class="ss-card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div style="font-weight:800;font-size:15px;"><i class="bi bi-house-door me-2" style="color:#2563EB;"></i>BHK Options</div>
    <small class="text-muted">Shown in search widget across all tabs</small>
  </div>
  <div class="card-body p-4">
    <div id="bhkChips">
      @foreach($cfg['bhk_options'] as $b)
      <span class="pill-chip active" data-val="{{ $b }}" onclick="removeBhk(this)">
        {{ $b }} <i class="bi bi-x-sm"></i>
      </span>
      @endforeach
    </div>
    <div style="display:flex;gap:8px;margin-top:12px;">
      <input id="newBhk" type="text" class="f-inp-sm" placeholder="e.g. 6 BHK" style="width:160px;"/>
      <button onclick="addBhk()" style="background:#2563EB;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;">Add</button>
      <button onclick="saveBhk()" style="background:#E5343A;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-cloud-check me-1"></i>Save</button>
    </div>
  </div>
</div>

{{-- ─── PROPERTY STATUS ────────────────────────────────────────────────── --}}
<div class="ss-card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div style="font-weight:800;font-size:15px;"><i class="bi bi-tag me-2" style="color:#16A34A;"></i>Property Status</div>
    <small class="text-muted">Status filter options</small>
  </div>
  <div class="card-body p-4">
    <div id="statusChips">
      @foreach($cfg['prop_statuses'] as $st)
      <span class="pill-chip active" data-val="{{ $st }}" onclick="removeStatus(this)">
        {{ $st }} <i class="bi bi-x-sm"></i>
      </span>
      @endforeach
    </div>
    <div style="display:flex;gap:8px;margin-top:12px;">
      <input id="newStatus" type="text" class="f-inp-sm" placeholder="e.g. Resale" style="width:200px;"/>
      <button onclick="addStatus()" style="background:#16A34A;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;">Add</button>
      <button onclick="saveStatus()" style="background:#E5343A;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:13px;font-weight:600;cursor:pointer;"><i class="bi bi-cloud-check me-1"></i>Save</button>
    </div>
  </div>
</div>

</section>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const saveUrl = '{{ url("search-settings/save") }}';

// ── Helpers ───────────────────────────────────────────────────────────────
async function apiSave(key, value) {
    const r = await fetch(saveUrl, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
        body: JSON.stringify({key, value})
    });
    const d = await r.json();
    if (d.success) showSaved();
    else if(window.toastr) toastr.error(d.message||'Save failed');
}

function showSaved() {
    const b = document.getElementById('globalSavedBadge');
    b.style.display = 'inline-block';
    clearTimeout(b._t);
    b._t = setTimeout(() => b.style.display='none', 2500);
}

// ── Tab Sortable ──────────────────────────────────────────────────────────
Sortable.create(document.getElementById('tabsList'), {
    handle: '.drag-hdl',
    animation: 150,
    filter: '.tab-subtypes-panel',
    onEnd() { updateSeqBadges(); saveTabs(); }
});

function updateSeqBadges() {
    document.querySelectorAll('#tabsList .tab-sort-row').forEach((r,i) => {
        const b = r.querySelector('.seq-badge');
        if (b) b.textContent = i+1;
    });
}

function buildTabsFromDom() {
    return [...document.querySelectorAll('#tabsList .tab-sort-row')].map(r => ({
        slug  : r.dataset.slug,
        label : r.dataset.label,
        icon  : r.dataset.icon,
        active: r.dataset.active === '1',
    }));
}

function saveTabs() { apiSave('tabs', buildTabsFromDom()); }

function onTabToggle(cb) {
    const row = cb.closest('.tab-sort-row');
    const active = cb.checked;
    row.dataset.active = active ? '1' : '0';
    const pill = row.querySelector('span[style*="border-radius:20px"]');
    if (pill) {
        pill.textContent = active ? 'Visible' : 'Hidden';
        pill.style.background = active ? '#DCFCE7' : '#F3F4F6';
        pill.style.color = active ? '#166534' : '#9CA3AF';
    }
    saveTabs();
}

function deleteTab(btn) {
    const row = btn.closest('.tab-sort-row');
    if (!confirm('Delete "'+row.dataset.label+'" tab?')) return;
    const slug = row.dataset.slug;
    const panel = document.getElementById('subtypes-'+slug);
    row.style.opacity='0'; row.style.transition='opacity .3s';
    setTimeout(() => { row.remove(); if(panel) panel.remove(); updateSeqBadges(); saveTabs(); }, 300);
}

// ── Add Tab ───────────────────────────────────────────────────────────────
function toggleAddTabForm() {
    const f = document.getElementById('addTabForm');
    const b = document.getElementById('addTabBtn');
    const open = f.style.display !== 'none';
    f.style.display = open ? 'none' : 'block';
    b.innerHTML = open ? '<i class="bi bi-plus-circle me-1"></i> Add Tab' : '<i class="bi bi-x-circle me-1"></i> Cancel';
    if (!open) document.getElementById('newTabLabel').focus();
}

function autoSlug(inp) {
    document.getElementById('newTabSlug').value = inp.value.toLowerCase().replace(/[^a-z0-9]/g,'');
}

function confirmAddTab() {
    const label = document.getElementById('newTabLabel').value.trim();
    const slug  = document.getElementById('newTabSlug').value.trim();
    const icon  = document.getElementById('newTabIcon').value.trim() || 'fa-circle';
    if (!label || !slug) return;

    const seq = document.querySelectorAll('#tabsList .tab-sort-row').length + 1;
    const html = `
    <div class="tab-row tab-sort-row" data-slug="${slug}" data-label="${label}" data-icon="${icon}" data-active="1">
      <span class="drag-hdl"><i class="bi bi-grip-vertical"></i></span>
      <span class="seq-badge">${seq}</span>
      <i class="fa-solid ${icon}" style="color:#e30620;font-size:15px;width:18px;flex-shrink:0;"></i>
      <div style="flex:1;"><div style="font-size:13px;font-weight:700;color:#0F172A;">${label}</div><div style="font-size:11px;color:#94A3B8;">/${slug}</div></div>
      <span style="font-size:11px;font-weight:600;padding:3px 9px;border-radius:20px;background:#DCFCE7;color:#166534;">Visible</span>
      <button onclick="toggleSubtypesPanel(this,'${slug}')" style="background:#EFF6FF;border:none;border-radius:8px;padding:5px 11px;font-size:12px;font-weight:600;color:#1D4ED8;cursor:pointer;white-space:nowrap;">
        ▸ Subtypes <span style="background:#DBEAFE;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px;">0</span>
      </button>
      <div class="form-check form-switch mb-0"><input class="form-check-input tab-toggle" type="checkbox" checked onchange="onTabToggle(this)" style="cursor:pointer;"/></div>
      <button onclick="deleteTab(this)" style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:4px 6px;border-radius:6px;flex-shrink:0;"
        onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'" onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
        <i class="bi bi-trash3" style="font-size:13px;"></i>
      </button>
    </div>
    <div class="tab-subtypes-panel" id="subtypes-${slug}">
      <div style="font-size:12px;font-weight:700;color:#166534;margin-bottom:10px;"><i class="bi bi-list-ul me-1"></i> Subtypes for "${label}"</div>
      <div id="sublist-${slug}" class="subtype-sort-list" data-tab="${slug}"></div>
      <button onclick="toggleAddSubForm('${slug}')" style="background:none;border:1px dashed #BBF7D0;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;color:#16A34A;cursor:pointer;width:100%;margin-top:6px;">
        <i class="bi bi-plus-circle me-1"></i> Add Subtype
      </button>
      <div class="add-subtype-form" id="addsub-${slug}">
        <div style="display:flex;gap:8px;">
          <input type="text" id="newsubl-${slug}" class="f-inp-sm" placeholder="Label"
            oninput="document.getElementById('newsugs-${slug}').value=this.value.toLowerCase().replace(/[^a-z0-9]/g,'')"/>
          <input type="text" id="newsugs-${slug}" class="f-inp-sm" placeholder="slug" style="width:120px;"/>
          <button onclick="confirmAddSubtype('${slug}')" style="background:#16A34A;color:#fff;border:none;border-radius:8px;padding:6px 16px;font-size:12px;font-weight:700;cursor:pointer;">Add</button>
        </div>
      </div>
    </div>`;

    document.getElementById('tabsList').insertAdjacentHTML('beforeend', html);
    initSubtypeSortable(slug);
    toggleAddTabForm();
    saveTabs();
    saveTabSubtypes(slug);
}

// ── Subtypes Panel ────────────────────────────────────────────────────────
function toggleSubtypesPanel(btn, slug) {
    const panel = document.getElementById('subtypes-'+slug);
    if (!panel) return;
    const open = panel.style.display === 'block';
    panel.style.display = open ? 'none' : 'block';
    btn.innerHTML = (open ? '▸' : '▾') + ` Subtypes <span style="background:#DBEAFE;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px;">${panel.querySelectorAll('.subtype-row').length}</span>`;
}

function buildSubtypesForTab(slug) {
    return [...document.querySelectorAll(`#sublist-${slug} .subtype-row`)].map(r => ({
        slug  : r.dataset.slug,
        label : r.dataset.label,
        active: r.dataset.active === '1',
    }));
}

function buildAllTabSubtypes() {
    const result = {};
    document.querySelectorAll('.tab-sort-row').forEach(tr => {
        result[tr.dataset.slug] = buildSubtypesForTab(tr.dataset.slug);
    });
    return result;
}

function saveTabSubtypes(slug) {
    apiSave('tab_subtypes', buildAllTabSubtypes());
    // Update badge count
    const btn = document.querySelector(`#subtypes-${slug}`)?.previousElementSibling?.querySelector('button[onclick*="toggleSubtypesPanel"]');
    const count = buildSubtypesForTab(slug).length;
    if (btn) btn.innerHTML = `▾ Subtypes <span style="background:#DBEAFE;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px;">${count}</span>`;
}

function onSubtypeToggle(cb, tabSlug) {
    const row = cb.closest('.subtype-row');
    row.dataset.active = cb.checked ? '1' : '0';
    saveTabSubtypes(tabSlug);
}

function deleteSubtype(btn, tabSlug) {
    const row = btn.closest('.subtype-row');
    row.remove();
    saveTabSubtypes(tabSlug);
}

function toggleAddSubForm(slug) {
    const f = document.getElementById('addsub-'+slug);
    f.style.display = f.style.display === 'block' ? 'none' : 'block';
    if (f.style.display === 'block') document.getElementById('newsubl-'+slug)?.focus();
}

function confirmAddSubtype(tabSlug) {
    const label = document.getElementById('newsubl-'+tabSlug)?.value.trim();
    const slug  = document.getElementById('newsugs-'+tabSlug)?.value.trim();
    if (!label || !slug) return;

    const row = document.createElement('div');
    row.className = 'subtype-row';
    row.dataset.slug   = slug;
    row.dataset.label  = label;
    row.dataset.active = '1';
    row.innerHTML = `
      <span style="cursor:grab;color:#CBD5E1;font-size:14px;flex-shrink:0;"><i class="bi bi-grip-vertical"></i></span>
      <span style="font-size:12px;font-weight:600;color:#0F172A;flex:1;">${label}</span>
      <span style="font-size:10px;color:#94A3B8;">/${slug}</span>
      <div class="form-check form-switch mb-0 ms-1">
        <input class="form-check-input subtype-toggle" type="checkbox" checked onchange="onSubtypeToggle(this,'${tabSlug}')" style="cursor:pointer;"/>
      </div>
      <button onclick="deleteSubtype(this,'${tabSlug}')" style="background:none;border:none;cursor:pointer;color:#CBD5E1;padding:3px 5px;border-radius:5px;flex-shrink:0;"
        onmouseover="this.style.background='#FEE2E2';this.style.color='#991B1B'" onmouseout="this.style.background='none';this.style.color='#CBD5E1'">
        <i class="bi bi-x-lg" style="font-size:11px;"></i>
      </button>`;
    document.getElementById('sublist-'+tabSlug).appendChild(row);

    document.getElementById('newsubl-'+tabSlug).value = '';
    document.getElementById('newsugs-'+tabSlug).value = '';
    saveTabSubtypes(tabSlug);
}

// Init subtype sortables
function initSubtypeSortable(slug) {
    const el = document.getElementById('sublist-'+slug);
    if (!el || !window.Sortable) return;
    Sortable.create(el, {
        animation: 150,
        handle: 'span[style*="grab"]',
        onEnd: () => saveTabSubtypes(slug)
    });
}

// Init all on page load
document.querySelectorAll('.subtype-sort-list').forEach(el => {
    initSubtypeSortable(el.dataset.tab);
});

// ── BHK ───────────────────────────────────────────────────────────────────
function getBhkValues() {
    return [...document.querySelectorAll('#bhkChips .pill-chip')].map(c => c.dataset.val);
}
function saveBhk() { apiSave('bhk_options', getBhkValues()); }
function removeBhk(chip) {
    if (!confirm('Remove "'+chip.dataset.val+'"?')) return;
    chip.remove();
}
function addBhk() {
    const val = document.getElementById('newBhk').value.trim();
    if (!val) return;
    document.getElementById('bhkChips').insertAdjacentHTML('beforeend',
        `<span class="pill-chip active" data-val="${val}" onclick="removeBhk(this)">${val} <i class="bi bi-x-sm"></i></span>`);
    document.getElementById('newBhk').value='';
}

// ── Property Status ───────────────────────────────────────────────────────
function getStatusValues() {
    return [...document.querySelectorAll('#statusChips .pill-chip')].map(c => c.dataset.val);
}
function saveStatus() { apiSave('prop_statuses', getStatusValues()); }
function removeStatus(chip) {
    if (!confirm('Remove "'+chip.dataset.val+'"?')) return;
    chip.remove();
}
function addStatus() {
    const val = document.getElementById('newStatus').value.trim();
    if (!val) return;
    document.getElementById('statusChips').insertAdjacentHTML('beforeend',
        `<span class="pill-chip active" data-val="${val}" onclick="removeStatus(this)">${val} <i class="bi bi-x-sm"></i></span>`);
    document.getElementById('newStatus').value='';
}
</script>
@endsection
