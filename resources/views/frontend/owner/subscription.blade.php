@extends('frontend.owner.layouts.app')
@section('title','Subscription Plans')
@section('page-title','Subscription Plans')
@section('page-bread','Upgrade for more listings and visibility')

@section('content')
<div class="sub-header">
  <h2>Choose the Right Plan</h2>
  <p>Get more listings, premium visibility and direct leads from verified buyers</p>
</div>

@if($activePlan)
<div style="background:linear-gradient(135deg,var(--green),#15803D);border-radius:var(--r-xl);padding:20px 24px;margin-bottom:28px;display:flex;align-items:center;gap:16px;color:#fff;">
  <div style="width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px;"><i class="fa-solid fa-crown"></i></div>
  <div>
    <div style="font-size:16px;font-weight:800;">{{ $activePlan->plan_name }} — Active</div>
    <div style="font-size:13px;opacity:.85;">Valid until {{ \Carbon\Carbon::parse($activePlan->end_date)->format('d M Y') }}</div>
  </div>
  <div style="margin-left:auto;font-size:22px;font-weight:900;">₹{{ number_format($activePlan->plan_price) }}<span style="font-size:13px;font-weight:400;opacity:.8;">/plan</span></div>
</div>
@endif

@if($packages->count())
{{-- Admin-configured plans from DB --}}
<div class="plans-grid" style="grid-template-columns:repeat({{ min($packages->count(), 4) }},1fr);">
  @foreach($packages as $i => $pkg)
  @php
    $colors    = ['plan-basic','plan-premium','plan-elite','plan-free'];
    $icons     = ['fa-star','fa-fire','fa-crown','fa-user'];
    $taglines  = ['For active sellers','Best for fast results','For builders & developers','Get started'];
    $colorClass = $colors[$i % 4];
    $isCurrent  = $activePlan && $activePlan->package_id == $pkg->id;
    $isPopular  = $i === 1;
  @endphp
  <div class="plan-card {{ $colorClass }} {{ $isCurrent ? 'current' : ($isPopular ? 'popular' : '') }}">
    <div class="plan-icon"><i class="fa-solid {{ $icons[$i % 4] }}"></i></div>
    <div class="plan-name">{{ $pkg->name }}</div>
    <div class="plan-tagline">{{ $taglines[$i % 4] }}</div>
    <div class="plan-price">
      <div class="amount">₹{{ number_format($pkg->price) }}<span>/plan</span></div>
      <div class="period">{{ isset($pkg->duration) ? round($pkg->duration/24).' days' : '30 days' }} access</div>
    </div>
    <ul class="plan-feats">
      @if(!empty($pkg->description ?? null) && property_exists($pkg, 'description'))
        @foreach(explode("\n", $pkg->description) as $line)
        @if(trim($line))
        <li class="included"><i class="fa-solid fa-check"></i> {{ trim($line) }}</li>
        @endif
        @endforeach
      @else
      <li class="included"><i class="fa-solid fa-check"></i> Active Listings</li>
      <li class="included"><i class="fa-solid fa-check"></i> Photo uploads per listing</li>
      <li class="included"><i class="fa-solid fa-check"></i> Priority placement</li>
      <li class="included"><i class="fa-solid fa-check"></i> Direct buyer enquiries</li>
      @endif
    </ul>
    @if($isCurrent)
    <button class="plan-btn current-btn"><i class="fa-solid fa-circle-check"></i> Current Plan</button>
    @else
    <button class="plan-btn" onclick="subscribePlan({{ $pkg->id }}, '{{ addslashes($pkg->name) }}', {{ $pkg->price }})">
      Get {{ $pkg->name }}
    </button>
    @endif
  </div>
  @endforeach
</div>

@else
{{-- Static plans if no packages in DB --}}
<div class="plans-grid">
  @foreach([
    ['Free','For starters','0','Free Forever','plan-free','fa-user',['2 Active Listings','5 Photos per listing','Basic enquiry inbox']],
    ['Basic','For active sellers','499','Billed monthly','plan-basic','fa-star',['10 Active Listings','15 Photos per listing','Priority placement','Basic analytics','Email leads']],
    ['Premium','Best for fast results','999','Billed monthly','plan-premium','fa-fire',['25 Active Listings','20 Photos per listing','Featured badge','Top placement','Full analytics','WhatsApp leads','Video tour']],
    ['Elite','Builders & developers','2999','Billed monthly','plan-elite','fa-crown',['Unlimited Listings','Unlimited Photos','Dedicated account manager','Homepage featured slots','Advanced CRM','Custom brand page','24/7 Priority support']],
  ] as $i => $plan)
  <div class="plan-card {{ $plan[4] }} {{ $i===2?'popular':'' }} {{ (!$activePlan && $i===0)?'current':'' }}">
    <div class="plan-icon"><i class="fa-solid {{ $plan[5] }}"></i></div>
    <div class="plan-name">{{ $plan[0] }}</div>
    <div class="plan-tagline">{{ $plan[1] }}</div>
    <div class="plan-price">
      <div class="amount">{{ $plan[2]==='0'?'Free':'₹'.number_format($plan[2]) }}<span>{{ $plan[2]==='0'?'':'/mo' }}</span></div>
      <div class="period">{{ $plan[3] }}</div>
    </div>
    <ul class="plan-feats">
      @foreach($plan[6] as $feat)
      <li class="included"><i class="fa-solid fa-check"></i> {{ $feat }}</li>
      @endforeach
    </ul>
    @if(!$activePlan && $i===0)
    <button class="plan-btn current-btn"><i class="fa-solid fa-circle-check"></i> Current Plan</button>
    @else
    <button class="plan-btn" @if($plan[2]==='0') disabled @else onclick="showToast('Please add plans in the Admin Panel → Feature Packages to enable subscriptions.', 'success')" @endif>
      {{ $plan[2]==='0' ? 'Already on Free' : 'Get '.$plan[0].' Plan' }}
    </button>
    @endif
  </div>
  @endforeach
</div>

<div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-xl);padding:20px 24px;text-align:center;margin-top:8px;">
  <div style="font-size:14px;color:var(--gray);"><i class="fa-solid fa-circle-info" style="color:var(--blue);margin-right:6px;"></i>No subscription plans configured yet. Contact BigWein admin to set up paid plans.</div>
</div>
@endif

{{-- Comparison table --}}
<div class="card" style="margin-top:28px;">
  <div class="card-head"><div class="card-title"><i class="fa-solid fa-table-list" style="color:var(--red);margin-right:6px;"></i>Feature Comparison</div></div>
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:var(--bg);">
          <th style="text-align:left;padding:12px 20px;color:var(--gray);font-weight:600;">Feature</th>
          <th style="padding:12px 16px;color:var(--gray);font-weight:600;">Free</th>
          <th style="padding:12px 16px;color:var(--blue);font-weight:700;">Basic</th>
          <th style="padding:12px 16px;color:var(--red);font-weight:700;">Premium</th>
          <th style="padding:12px 16px;color:var(--purple);font-weight:700;">Elite</th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['Active Listings','2','10','25','Unlimited'],
          ['Photos / Listing','5','15','20','Unlimited'],
          ['Priority in Search',false,false,true,true],
          ['Featured Badge',false,false,true,true],
          ['WhatsApp Leads',false,false,true,true],
          ['Analytics Dashboard',false,'Basic','Full','Advanced'],
          ['Support','Email','Email','Priority','24/7 Dedicated'],
        ] as $row)
        <tr style="border-top:1px solid var(--border);{{ $loop->even?'background:var(--bg)':'' }}">
          <td style="padding:12px 20px;color:var(--navy);">{{ $row[0] }}</td>
          @foreach([$row[1],$row[2],$row[3],$row[4]] as $val)
          <td style="text-align:center;padding:12px;">
            @if($val === false)<i class="fa-solid fa-xmark" style="color:var(--gray3);"></i>
            @elseif($val === true)<i class="fa-solid fa-check" style="color:var(--green);"></i>
            @else {{ $val }}
            @endif
          </td>
          @endforeach
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
async function subscribePlan(pkgId, name, price) {
    const priceText = price > 0 ? `₹${Number(price).toLocaleString('en-IN')}` : 'Free';
    if (!confirm(`Activate ${name} plan (${priceText})?

This will replace your current plan immediately.`)) return;

    // Show loading state
    const btns = document.querySelectorAll('.plan-btn');
    btns.forEach(b => { b.disabled = true; b.style.opacity = '0.6'; });

    const res = await owFetch(`/owner/subscription/${pkgId}/subscribe`, { method: 'POST' });

    btns.forEach(b => { b.disabled = false; b.style.opacity = '1'; });

    if (res.success) {
        showToast(res.message, 'success');
        // Update UI immediately without reload
        setTimeout(() => {
            window.location.href = '/owner/subscription';
        }, 1800);
    } else {
        showToast(res.message || 'Activation failed. Please try again.', 'error');
    }
}
</script>
@endpush
