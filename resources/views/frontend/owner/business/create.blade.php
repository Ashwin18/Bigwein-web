@extends('frontend.owner.layouts.app')
@php
  $isEdit = !empty($business);
  $details = $isEdit && $business->category_details ? (json_decode($business->category_details,true) ?: []) : [];
  $assetsSelected = $isEdit && $business->assets_included ? (json_decode($business->assets_included,true) ?: []) : [];
  $docMap = collect($documents ?? [])->keyBy('document_type');
@endphp

@section('title', $isEdit ? 'Edit Business' : 'List Business for Sale')
@section('page-title', $isEdit ? 'Edit Business' : 'List Business for Sale')
@section('page-bread', $isEdit ? 'Update draft / requested changes and resubmit' : 'Business Marketplace · Save draft or submit for admin approval')

@push('styles')
<style>
.bw-biz{max-width:1040px;margin:auto}.biz-head{background:linear-gradient(135deg,#172033,#321f2d);border-radius:18px;padding:22px;color:#fff;margin-bottom:15px;display:flex;justify-content:space-between;gap:15px;align-items:center}.biz-head h2{margin:0;font-size:22px}.biz-head p{margin:6px 0 0;font-size:11px;color:#cbd5e1}.status-pill{padding:7px 10px;border-radius:999px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800;white-space:nowrap}
.bc{background:#fff;border:1px solid #e7ebf1;border-radius:15px;padding:19px;margin-bottom:13px}.bt{font-weight:800;color:#172033;font-size:14px;margin-bottom:15px;display:flex;align-items:center;gap:8px}.num{width:24px;height:24px;border-radius:8px;background:#fff1f2;color:#ef3f45;display:inline-flex;align-items:center;justify-content:center;font-size:11px}
.bg{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.bf{grid-column:1/-1}.f label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;color:#64748b;margin-bottom:6px}.f input,.f select,.f textarea{width:100%;border:1px solid #dce3eb;border-radius:9px;padding:10px 11px;font:inherit;font-size:12px;box-sizing:border-box;background:#fff}.f textarea{min-height:90px;resize:vertical}.hint{font-size:10px;color:#94a3b8;margin-top:5px}
.assets{display:flex;flex-wrap:wrap;gap:8px}.asset{border:1px solid #e2e8f0;border-radius:999px;padding:8px 11px;font-size:11px;background:#fff}.asset input{margin-right:4px}
.cat-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.cat-empty{font-size:11px;color:#94a3b8;padding:12px;background:#f8fafc;border-radius:9px}
.gallery-existing{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-top:10px}.gallery-item{border:1px solid #e5e7eb;border-radius:10px;padding:6px;background:#fff}.gallery-item img{width:100%;height:90px;object-fit:cover;border-radius:7px}.gallery-item label{font-size:9px;color:#b91c1c;margin-top:5px;display:block}
.preview{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.preview img{width:90px;height:70px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb}
.admin-note{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:11px;padding:12px;font-size:11px;margin-bottom:13px}.actions{display:grid;grid-template-columns:1fr 1.4fr;gap:10px}.draft,.submit{border:0;border-radius:11px;padding:13px;font-weight:800}.draft{background:#fff;border:1px solid #dce3eb;color:#475569}.submit{background:#ef3e3e;color:#fff}
@media(max-width:760px){.bg,.cat-fields{grid-template-columns:1fr}.bf{grid-column:auto}.gallery-existing{grid-template-columns:repeat(2,1fr)}.actions{grid-template-columns:1fr}.biz-head{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="bw-biz">
  <div class="biz-head">
    <div>
      <h2><i class="fa-solid fa-briefcase"></i> {{ $isEdit ? 'Update your business listing' : 'List a Business for Sale' }}</h2>
      <p>Business data stays completely separate from Property listings.</p>
    </div>
    @if($isEdit)<span class="status-pill">{{ ucwords(str_replace('_',' ',$business->request_status)) }}</span>@endif
  </div>

  @if($isEdit && in_array($business->request_status,['changes_requested','rejected']) && $business->admin_remarks)
    <div class="admin-note"><strong>Admin Remarks:</strong> {{ $business->admin_remarks }}</div>
  @endif

  <form method="POST" action="{{ $isEdit ? url('/owner/business/'.$business->id.'/update') : url('/owner/list-business') }}" enctype="multipart/form-data" id="businessForm">
    @csrf

    <div class="bc"><div class="bt"><span class="num">1</span> Business Information</div><div class="bg">
      <div class="f bf"><label>Business Listing Title *</label><input name="title" value="{{ old('title',$business->title ?? '') }}" placeholder="e.g. Profitable South Indian Restaurant for Sale"></div>
      <div class="f"><label>Business Category *</label><select name="business_category_id" id="businessCategory"><option value="">Select Category</option>@foreach($categories as $c)<option value="{{ $c->id }}" data-name="{{ strtolower($c->name) }}" {{ (string)old('business_category_id',$business->business_category_id ?? '')===(string)$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
      <div class="f"><label>Business Status *</label><select name="business_status"><option value="running" {{ old('business_status',$business->business_status ?? 'running')==='running'?'selected':'' }}>Running Business</option><option value="temporarily_closed" {{ old('business_status',$business->business_status ?? '')==='temporarily_closed'?'selected':'' }}>Temporarily Closed</option><option value="new_setup" {{ old('business_status',$business->business_status ?? '')==='new_setup'?'selected':'' }}>New Setup</option><option value="franchise_resale" {{ old('business_status',$business->business_status ?? '')==='franchise_resale'?'selected':'' }}>Franchise Resale</option></select></div>
      <div class="f"><label>Specific Business Type</label><input name="business_type" value="{{ old('business_type',$business->business_type ?? '') }}"></div>
      <div class="f"><label>Established Year</label><input type="number" name="established_year" min="1900" max="{{ date('Y') }}" value="{{ old('established_year',$business->established_year ?? '') }}"></div>
      <div class="f"><label>Employees</label><input type="number" name="employees" min="0" value="{{ old('employees',$business->employees ?? '') }}"></div>
      <div class="f"><label>Reason for Sale</label><input name="reason_for_sale" value="{{ old('reason_for_sale',$business->reason_for_sale ?? '') }}" placeholder="Relocation / retirement / new venture"></div>
      <div class="f bf"><label>Description *</label><textarea name="description" placeholder="Business strengths, customer base, opportunity...">{{ old('description',$business->description ?? '') }}</textarea></div>
    </div></div>

    <div class="bc" id="categorySpecificCard"><div class="bt"><span class="num">2</span> Category-Specific Details</div>
      <div id="categoryFields" class="cat-fields"></div>
      <div id="categoryEmpty" class="cat-empty">Choose a Business Category to load relevant business-specific fields.</div>
    </div>

    <div class="bc"><div class="bt"><span class="num">3</span> Location & Premises</div><div class="bg">
      <div class="f"><label>City *</label><input name="city" value="{{ old('city',$business->city ?? '') }}"></div>
      <div class="f"><label>State *</label><input name="state" value="{{ old('state',$business->state ?? '') }}"></div>
      <div class="f"><label>Locality</label><input name="locality" value="{{ old('locality',$business->locality ?? '') }}"></div>
      <div class="f"><label>Premises</label><select name="premises_type"><option value="">Select</option>@foreach(['owned'=>'Owned','rented'=>'Rented','leased'=>'Leased'] as $k=>$v)<option value="{{ $k }}" {{ old('premises_type',$business->premises_type ?? '')===$k?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
      <div class="f"><label>Built-up Area (sqft)</label><input type="number" step="0.01" name="built_up_area" value="{{ old('built_up_area',$business->built_up_area ?? '') }}"></div>
      <div class="f"><label>Monthly Rent ₹</label><input type="number" step="0.01" name="monthly_rent" value="{{ old('monthly_rent',$business->monthly_rent ?? '') }}"></div>
      <div class="f"><label>Lease Months Remaining</label><input type="number" name="lease_months_remaining" value="{{ old('lease_months_remaining',$business->lease_months_remaining ?? '') }}"></div>
      <div class="f bf"><label>Full Address <span style="font-weight:500">(Admin only)</span></label><textarea name="address">{{ old('address',$business->address ?? '') }}</textarea></div>
    </div></div>

    <div class="bc"><div class="bt"><span class="num">4</span> Price & Financials</div><div class="bg">
      <div class="f"><label>Asking Price ₹ *</label><input type="number" step="0.01" name="asking_price" value="{{ old('asking_price',$business->asking_price ?? '') }}"></div>
      <div class="f"><label>Price Negotiable</label><select name="negotiable"><option value="0">No</option><option value="1" {{ old('negotiable',$business->negotiable ?? 0)?'selected':'' }}>Yes</option></select></div>
      <div class="f"><label>Monthly Revenue ₹</label><input type="number" step="0.01" name="monthly_revenue" value="{{ old('monthly_revenue',$business->monthly_revenue ?? '') }}"></div>
      <div class="f"><label>Monthly Expense ₹</label><input type="number" step="0.01" name="monthly_expense" value="{{ old('monthly_expense',$business->monthly_expense ?? '') }}"></div>
      <div class="f"><label>Monthly Profit ₹</label><input type="number" step="0.01" name="monthly_profit" value="{{ old('monthly_profit',$business->monthly_profit ?? '') }}"></div>
      <div class="f"><label>Inventory Value ₹</label><input type="number" step="0.01" name="inventory_value" value="{{ old('inventory_value',$business->inventory_value ?? '') }}"></div>
      <div class="f bf"><label>Financial Visibility</label><select name="financial_visibility">@foreach(['verified_buyers'=>'Verified Buyers Only','public'=>'Show Publicly','hidden'=>'Hidden'] as $k=>$v)<option value="{{ $k }}" {{ old('financial_visibility',$business->financial_visibility ?? 'verified_buyers')===$k?'selected':'' }}>{{ $v }}</option>@endforeach</select><div class="hint">Sensitive financial information does not have to be shown publicly.</div></div>
    </div></div>

    <div class="bc"><div class="bt"><span class="num">5</span> What's Included in Sale</div>
      <div class="assets">@foreach(['Brand / Business Name','Furniture','Equipment','Machinery','Inventory','Customer Database','Website / Domain','Social Media','Licences','Staff','Vehicles','Franchise Rights','Lease Rights','Supplier Relationships'] as $a)<label class="asset"><input type="checkbox" name="assets[]" value="{{ $a }}" {{ in_array($a,old('assets',$assetsSelected))?'checked':'' }}> {{ $a }}</label>@endforeach</div>
    </div>

    <div class="bc"><div class="bt"><span class="num">6</span> Photos & Documents</div><div class="bg">
      <div class="f bf"><label style="text-transform:none;font-size:11px"><input type="checkbox" name="is_confidential" value="1" style="width:auto" {{ old('is_confidential',$business->is_confidential ?? 0)?'checked':'' }}> Confidential listing — hide business identity publicly</label></div>
      <div class="f"><label>Cover Image</label><input type="file" name="cover_image" accept="image/*" id="coverInput">@if($isEdit && $business->cover_image)<div class="hint">Current cover already saved. Upload only to replace it.</div>@endif<div id="coverPreview" class="preview"></div></div>
      <div class="f"><label>Add Gallery Images</label><input type="file" name="gallery[]" accept="image/*" multiple id="galleryInput"><div id="galleryPreview" class="preview"></div></div>

      @if($isEdit && count($images))
      <div class="bf"><label style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase">Existing Gallery</label><div class="gallery-existing">@foreach($images as $img)<div class="gallery-item"><img src="{{ asset('images/businesses/'.$business->id.'/'.$img->image) }}"><label><input type="checkbox" name="remove_images[]" value="{{ $img->id }}"> Remove</label></div>@endforeach</div></div>
      @endif

      @foreach(['gst'=>'GST Certificate','registration'=>'Business Registration','licence'=>'Licence / FSSAI','lease_agreement'=>'Lease Agreement'] as $key=>$label)
      <div class="f"><label>{{ $label }} <span style="font-weight:500">(Admin only)</span></label><input type="file" name="documents[{{ $key }}]" accept=".jpg,.jpeg,.png,.pdf">@if($isEdit && $docMap->has($key))<div class="hint">Document already uploaded. Upload a new file only to replace it.</div>@endif</div>
      @endforeach
    </div></div>

    <div class="actions">
      <button class="draft" type="submit" name="action" value="draft"><i class="fa-regular fa-floppy-disk"></i> Save Draft</button>
      <button class="submit" type="submit" name="action" value="submit"><i class="fa-solid fa-paper-plane"></i> {{ $isEdit ? 'Submit / Resubmit for Approval' : 'Submit Business for Approval' }}</button>
    </div>
  </form>
</div>

<script>
const EXISTING_DETAILS = @json(old('category_details',$details));

const BUSINESS_FIELDS = {
  restaurant: [
    ['cuisine_type','Cuisine Type','text','e.g. South Indian, Multi-cuisine'],
    ['seating_capacity','Seating Capacity','number','e.g. 80'],
    ['service_model','Service Model','select',['Dine-in','Delivery','Takeaway','Dine-in + Delivery']],
    ['fssai_status','FSSAI Licence','select',['Available','Not Available','In Process']],
    ['online_delivery','Swiggy / Zomato','select',['Both','Swiggy','Zomato','Not Listed']],
    ['avg_monthly_orders','Average Monthly Orders','number','']
  ],
  cafe: [
    ['seating_capacity','Seating Capacity','number',''],
    ['cuisine_type','Products / Cuisine','text','Coffee, bakery, snacks'],
    ['fssai_status','FSSAI Licence','select',['Available','Not Available','In Process']],
    ['online_delivery','Online Delivery','select',['Both','Swiggy','Zomato','Not Listed']]
  ],
  hotel: [
    ['number_of_rooms','Number of Rooms','number',''],
    ['occupancy_rate','Average Occupancy %','number',''],
    ['hotel_class','Hotel Category','select',['Budget','1 Star','2 Star','3 Star','4 Star','5 Star','Boutique']],
    ['restaurant_available','Restaurant Available','select',['Yes','No']],
    ['banquet_hall','Banquet Hall','select',['Yes','No']],
    ['ota_presence','OTA Presence','text','Booking.com, MakeMyTrip etc.']
  ],
  retail: [
    ['store_area','Store Area (sqft)','number',''],
    ['inventory_value_note','Inventory Included','select',['Yes','No','Negotiable']],
    ['brands_sold','Major Brands Sold','text',''],
    ['avg_monthly_customers','Average Monthly Customers','number',''],
    ['pos_available','POS / Billing System','select',['Yes','No']]
  ],
  supermarket: [
    ['store_area','Store Area (sqft)','number',''],
    ['daily_footfall','Average Daily Footfall','number',''],
    ['inventory_value_note','Inventory Included','select',['Yes','No','Negotiable']],
    ['delivery_service','Home Delivery','select',['Yes','No']],
    ['pos_available','POS / Billing System','select',['Yes','No']]
  ],
  salon: [
    ['workstations','Workstations / Chairs','number',''],
    ['treatment_rooms','Treatment Rooms','number',''],
    ['staff_count_specialist','Trained Specialists','number',''],
    ['brand_products','Major Product Brands','text',''],
    ['membership_base','Active Memberships','number','']
  ],
  gym: [
    ['active_members','Active Members','number',''],
    ['equipment_count','Equipment Count','number',''],
    ['trainers','Trainers','number',''],
    ['floor_area','Workout Area (sqft)','number',''],
    ['membership_fee','Average Monthly Membership ₹','number','']
  ],
  franchise: [
    ['franchise_brand','Franchise Brand','text',''],
    ['royalty_percent','Royalty %','number',''],
    ['agreement_valid_until','Agreement Valid Until','date',''],
    ['transfer_approval','Brand Transfer Approval Required','select',['Yes','No']],
    ['territory_rights','Territory / Area Rights','text','']
  ],
  manufacturing: [
    ['manufacturing_type','Manufacturing Type','text',''],
    ['installed_capacity','Installed Capacity','text',''],
    ['machinery_value','Approx Machinery Value ₹','number',''],
    ['factory_area','Factory Area (sqft)','number',''],
    ['licenses','Key Industrial Licences','text','']
  ],
  healthcare: [
    ['speciality','Speciality','text',''],
    ['consultation_rooms','Consultation Rooms','number',''],
    ['beds','Beds (if applicable)','number',''],
    ['licenses','Healthcare Licences','text',''],
    ['daily_patients','Average Daily Patients','number','']
  ],
  pharmacy: [
    ['store_area','Store Area (sqft)','number',''],
    ['drug_license','Drug Licence','select',['Available','In Process','Not Available']],
    ['inventory_value_note','Medicine Inventory Included','select',['Yes','No','Negotiable']],
    ['daily_customers','Average Daily Customers','number','']
  ],
  education: [
    ['education_type','Institution Type','text','School, training centre, tuition etc.'],
    ['student_count','Active Students','number',''],
    ['classrooms','Classrooms','number',''],
    ['affiliation','Board / Affiliation','text',''],
    ['staff_count_specialist','Teaching Staff','number','']
  ],
  it: [
    ['service_focus','Service / Product Focus','text',''],
    ['active_clients','Active Clients','number',''],
    ['recurring_revenue','Recurring Revenue %','number',''],
    ['team_size','Technical Team Size','number',''],
    ['ip_assets','Software / IP Assets Included','text','']
  ],
  agency: [
    ['service_focus','Primary Services','text',''],
    ['active_clients','Active Clients','number',''],
    ['retainer_clients','Monthly Retainer Clients','number',''],
    ['team_size','Team Size','number','']
  ],
  travel: [
    ['service_focus','Travel Segment','text','Domestic, international, corporate etc.'],
    ['active_clients','Active Corporate Clients','number',''],
    ['iata_status','IATA / Industry Accreditation','text',''],
    ['vehicles_owned','Vehicles Owned','number','']
  ],
  automobile: [
    ['service_type','Business Type','text','Workshop, dealership, detailing etc.'],
    ['service_bays','Service Bays','number',''],
    ['equipment_count','Major Equipment Count','number',''],
    ['brand_authorization','Brand Authorization','text','']
  ],
  ecommerce: [
    ['platform','Primary Platform','text','Own website, Amazon, Flipkart etc.'],
    ['monthly_orders','Average Monthly Orders','number',''],
    ['repeat_customer_percent','Repeat Customer %','number',''],
    ['social_followers','Total Social Followers','number',''],
    ['website_included','Website / Domain Included','select',['Yes','No']]
  ],
  cloud: [
    ['cuisine_type','Cuisine Type','text',''],
    ['kitchen_area','Kitchen Area (sqft)','number',''],
    ['online_delivery','Delivery Platforms','select',['Both','Swiggy','Zomato','Other']],
    ['avg_monthly_orders','Average Monthly Orders','number',''],
    ['fssai_status','FSSAI Licence','select',['Available','Not Available','In Process']]
  ]
};

function profileKey(name){
  name=(name||'').toLowerCase();
  if(name.includes('restaurant')) return 'restaurant';
  if(name.includes('cafe') || name.includes('bakery')) return 'cafe';
  if(name.includes('hotel') || name.includes('hospitality')) return 'hotel';
  if(name.includes('supermarket')) return 'supermarket';
  if(name.includes('retail')) return 'retail';
  if(name.includes('salon') || name.includes('spa')) return 'salon';
  if(name.includes('gym') || name.includes('fitness')) return 'gym';
  if(name.includes('franchise')) return 'franchise';
  if(name.includes('manufactur')) return 'manufacturing';
  if(name.includes('clinic') || name.includes('healthcare')) return 'healthcare';
  if(name.includes('pharmacy')) return 'pharmacy';
  if(name.includes('education')) return 'education';
  if(name.includes('it') || name.includes('software')) return 'it';
  if(name.includes('digital agency')) return 'agency';
  if(name.includes('travel')) return 'travel';
  if(name.includes('automobile') || name.includes('service centre')) return 'automobile';
  if(name.includes('e-commerce')) return 'ecommerce';
  if(name.includes('cloud kitchen')) return 'cloud';
  return null;
}

function renderCategoryFields(){
  const sel=document.getElementById('businessCategory');
  const box=document.getElementById('categoryFields');
  const empty=document.getElementById('categoryEmpty');
  const name=sel?.selectedOptions?.[0]?.dataset?.name || '';
  const key=profileKey(name);
  box.innerHTML='';
  if(!key || !BUSINESS_FIELDS[key]){
    empty.style.display='block';
    empty.textContent = sel?.value ? 'No special fields are configured for this category. The general business fields are sufficient.' : 'Choose a Business Category to load relevant business-specific fields.';
    return;
  }
  empty.style.display='none';

  BUSINESS_FIELDS[key].forEach(([field,label,type,extra])=>{
    const wrap=document.createElement('div'); wrap.className='f';
    const lab=document.createElement('label'); lab.textContent=label; wrap.appendChild(lab);
    let input;
    if(type==='select'){
      input=document.createElement('select');
      const blank=document.createElement('option'); blank.value=''; blank.textContent='Select'; input.appendChild(blank);
      (extra||[]).forEach(opt=>{const o=document.createElement('option');o.value=opt;o.textContent=opt;input.appendChild(o);});
    }else{
      input=document.createElement('input'); input.type=type||'text'; if(typeof extra==='string')input.placeholder=extra;
    }
    input.name=`category_details[${field}]`;
    if(EXISTING_DETAILS && EXISTING_DETAILS[field]!==undefined) input.value=EXISTING_DETAILS[field];
    wrap.appendChild(input); box.appendChild(wrap);
  });
}
document.getElementById('businessCategory')?.addEventListener('change',renderCategoryFields);
renderCategoryFields();

function previewFiles(input, target){
  const box=document.getElementById(target); if(!box) return; box.innerHTML='';
  [...(input.files||[])].forEach(file=>{if(!file.type.startsWith('image/'))return;const img=document.createElement('img');img.src=URL.createObjectURL(file);box.appendChild(img);});
}
document.getElementById('coverInput')?.addEventListener('change',function(){previewFiles(this,'coverPreview')});
document.getElementById('galleryInput')?.addEventListener('change',function(){previewFiles(this,'galleryPreview')});
</script>
@endsection