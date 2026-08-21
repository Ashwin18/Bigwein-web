 <header>
     <nav class="navbar navbar-expand navbar-light" style="background-color: white;">
         <div class="container-fluid">
             <a href="#" class="burger-btn d-block" id="sidebarToggle">
                 <i class="bi bi-justify fs-3"></i>
             </a>
            @php
                $emailConfigStatus = DB::table('settings')->select('data')->where('type','email_configuration_verification')->first();
                if($emailConfigStatus){
                    $data = $emailConfigStatus->data;
                }else{
                    $data = 0;
                }
            @endphp
            @if($data == 0)
                @if(has_permissions('update', 'email_configurations'))
                    <a class="bw-email-status d-none d-xl-inline-flex" href="{{ route('email-configurations-index') }}" title="Open email configuration">
                        <span class="bw-email-status-icon"><i class="bi bi-envelope-exclamation"></i></span>
                        <span class="bw-email-status-copy"><strong>Email not verified</strong><small>Review configuration</small></span>
                    </a>
                @endif
            @endif
             <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                 data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                 aria-label="Toggle navigation">
                 <span class="navbar-toggler-icon"></span>
             </button>
             <div class="collapse navbar-collapse" id="navbarSupportedContent">
                 <div class="bw-topbar-context d-none d-lg-flex align-items-center me-auto">
                     <div>
                         <div class="bw-topbar-eyebrow">BigWein workspace</div>
                         <div class="bw-topbar-title">{{ trim($__env->yieldContent('title')) ?: 'Administration' }}</div>
                     </div>
                 </div>

                 <div class="dropdown bw-quick-actions d-none d-md-block">
                     <button class="btn bw-quick-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                         <i class="bi bi-plus-circle me-1"></i> Quick Add
                     </button>
                     <ul class="dropdown-menu dropdown-menu-end topbarUserDropdown">
                         <li><a class="dropdown-item" href="{{ url('property/create') }}"><i class="bi bi-building-add me-2"></i>Add Property</a></li>
                         <li><a class="dropdown-item" href="{{ url('project/create') }}"><i class="bi bi-kanban me-2"></i>Add Project</a></li>
                         <li><a class="dropdown-item" href="{{ url('slider/create') }}"><i class="bi bi-images me-2"></i>Add Banner</a></li>
                         <li><hr class="dropdown-divider"></li>
                         <li><a class="dropdown-item" href="{{ url('property-approval') }}"><i class="bi bi-patch-check me-2"></i>Review Approvals</a></li>
                     </ul>
                 </div>

                 <a href="{{ url('/') }}" target="_blank" class="btn bw-view-site-btn d-none d-md-inline-flex align-items-center">
                     <i class="bi bi-box-arrow-up-right me-1"></i> View Site
                 </a>

                 <!-- Pending listing notifications -->
                 <div class="dropdown bw-admin-notifications me-2">
                     <button class="btn btn-light position-relative rounded-circle" type="button" id="bwNotificationBell" data-bs-toggle="dropdown" aria-expanded="false" style="width:42px;height:42px;border:1px solid #e5e7eb;">
                         <i class="bi bi-bell"></i>
                         <span id="bwNotificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:9px;min-width:18px;">0</span>
                     </button>
                     <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0" aria-labelledby="bwNotificationBell" style="width:360px;max-width:90vw;border-radius:14px;overflow:hidden;z-index:2000;">
                         <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
                             <div><div class="fw-bold">Notifications</div><div class="small text-muted">Listings waiting for review</div></div>
                             <a href="{{ url('property-approval') }}" class="small text-danger fw-bold text-decoration-none">View all</a>
                         </div>
                         <div id="bwNotificationList" style="max-height:360px;overflow:auto;"><div class="p-4 text-center text-muted small">Loading…</div></div>
                     </div>
                 </div>

                 <div class="dropdown language-dropdown">
                     <a href="#" id="languageDropdown"
                         class="user-dropdown d-flex align-items-center dropend dropdown-toggle"
                         data-bs-toggle="dropdown" aria-expanded="false">
                         <div class="avatar avatar-md2">
                             <i class="bi bi-translate"></i>
                         </div>
                         <div class="text">
                         </div>
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end topbarUserDropdown"
                         aria-labelledby="languageDropdown">
                         @foreach (get_language() as $key => $language)
                             <li>
                                 <a class="dropdown-item"
                                     href="{{ url('set-language') . '/' . $language->getRawOriginal('code') }}">{{ $language->name }}</a>
                             </li>
                         @endforeach
                     </ul>
                 </div>
                 <div class="global-search position-relative d-none d-md-block" style="min-width:300px; max-width:460px; width:100%;">
                     <input type="text" id="globalSearchInput" class="form-control" placeholder="{{ __('Search menus (Ctrl+/)') }}">
                     <div id="globalSearchResults" class="list-group position-absolute w-100 shadow" style="z-index: 1050; display: none; max-height: 360px; overflow:auto;"></div>
                 </div>
                <div class="global-search-mobile">
                    <button type="button" id="openGlobalSearch" class="btn btn-outline-secondary d-inline d-md-none">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                        
                 <!-- Global Search Modal (Mobile) -->
                 <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true">
                     <div class="modal-dialog modal-dialog-centered">
                         <div class="modal-content">
                             <div class="modal-header">
                                 <h5 class="modal-title">Search</h5>
                                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                             </div>
                             <div class="modal-body">
                                 <input type="text" id="globalSearchInputMobile" class="form-control" placeholder="Search menus">
                                 <div id="globalSearchResultsMobile" class="list-group mt-2" style="max-height:360px; overflow:auto;"></div>
                             </div>
                         </div>
                     </div>
                 </div>
                 
                 <div class="dropdown">
                    <a href="#" id="topbarUserDropdown" class="user-dropdown d-flex align-items-center dropend dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-md2">
                            <img src="{{ !empty(Auth::user()->getRawOriginal("profile")) ? Auth::user()->profile : url('assets/images/faces/2.jpg') }}">
                        </div>
                        <div class="text">
                            <h6 class="user-dropdown-name">{{ Auth::user()->name }}</h6>
                            <p class="user-dropdown-status text-sm text-muted"> </p>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-start-md topbarUserDropdown" aria-labelledby="topbarUserDropdown">
                        {{-- Change Password --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('changepassword') }}">
                                <i class="icon-mid bi bi-gear me-2"></i>
                                {{ __('Change Password') }}
                            </a>
                        </li>

                        {{-- Change Profile --}}
                        @if (Auth::user()->type == 0)
                            <li>
                                <a class="dropdown-item" href="{{ route('changeprofile') }}">
                                    <i class="icon-mid bi bi-person me-2"></i>
                                    {{ __('Change Profile') }}
                                </a>
                            </li>
                        @endif

                        {{-- Logout --}}
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }} " onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="icon-mid bi bi-box-arrow-left me-2"></i>
                                {{ __('Logout') }}
                            </a>
                        </li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        {{ csrf_field() }}
                        </form>
                     </ul>
                 </div>
             </div>
         </div>
     </nav>
 
<script>
(function(){
    const list = document.getElementById('bwNotificationList');
    const badge = document.getElementById('bwNotificationBadge');
    if (!list || !badge) return;
    const esc = (v) => String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
    async function loadBwNotifications(){
        try{
            const r = await fetch('{{ url('bw-admin/notifications') }}', {headers:{'Accept':'application/json'}});
            if(!r.ok) return;
            const data = await r.json();
            const count = Number(data.pending_count || 0);
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
            if(!data.items || !data.items.length){
                list.innerHTML = '<div class="p-4 text-center"><i class="bi bi-check-circle text-success fs-3"></i><div class="small text-muted mt-2">No new admin notifications.</div></div>';
                return;
            }
            list.innerHTML = data.items.map(item => `
                <a href="${esc(item.url)}" class="d-flex gap-3 px-3 py-3 border-bottom text-decoration-none text-dark" style="background:#fff;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:${item.type==='pending'?'#fff7ed':item.type==='enquiry'?'#eff6ff':'#f0fdf4'};color:${item.type==='pending'?'#ea580c':item.type==='enquiry'?'#2563eb':'#16a34a'}"><i class="bi ${item.type==='pending'?'bi-hourglass-split':item.type==='enquiry'?'bi-chat-dots':'bi-person-plus'}"></i></div>
                    <div class="min-w-0"><div class="fw-bold small">${esc(item.title)}</div><div class="small text-muted text-truncate">${esc(item.body)}</div></div>
                </a>`).join('');
        }catch(e){}
    }
    loadBwNotifications();
    setInterval(loadBwNotifications, 30000);
})();
</script>

 </header>
