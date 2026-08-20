<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">

        <div class="sidebar-header" style="padding:16px;border-bottom:1px solid #F1F5F9;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#E5343A;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if(system_setting('company_logo'))
                        <img src="{{ url('assets/images/logo/'.system_setting('company_logo')) }}" alt="Logo" style="height:22px;width:auto;object-fit:contain;"/>
                    @else
                        <i class="bi bi-house" style="font-size:16px;color:#fff;"></i>
                    @endif
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#0F172A;line-height:1.2;">{{ system_setting('company_name') ?? 'BigWein' }}</div>
                    <div style="font-size:10px;color:#94A3B8;">Admin Panel</div>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu" id="sidebarMenu">

                <li style="list-style:none;padding:10px 16px 2px;font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.7px;">Main</li>

                @if(has_permissions('read','dashboard'))
                <li class="sidebar-item {{ request()->is('home') ? 'active' : '' }}">
                    <a href="{{ url('home') }}" class="sidebar-link">
                        <i class="bi bi-grid-1x2"></i><span class="menu-item">Dashboard</span>
                    </a>
                </li>
                @endif

                <li style="list-style:none;padding:10px 16px 2px;font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.7px;">Properties</li>

                @if(has_permissions('read','property'))
                <li class="sidebar-item {{ request()->is('property*') && !request()->is('property-approval*') ? 'active' : '' }}">
                    <a href="{{ url('property') }}" class="sidebar-link">
                        <i class="bi bi-building"></i><span class="menu-item">All Properties</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->is('project*') ? 'active' : '' }}">
                    <a href="{{ url('project') }}" class="sidebar-link">
                        <i class="bi bi-kanban"></i><span class="menu-item">Projects</span>
                        @php try { $pjPending = \DB::table('projects')->where('request_status','pending')->count(); } catch(\Exception $e){ $pjPending=0; } @endphp
                        @if($pjPending > 0)<span class="badge bg-warning ms-1" style="font-size:10px;vertical-align:middle;">{{ $pjPending }}</span>@endif
                    </a>
                </li>
                @endif

                <li class="sidebar-item {{ request()->is('property-approval*') ? 'active' : '' }}">
                    <a href="{{ url('property-approval') }}" class="sidebar-link">
                        <i class="bi bi-patch-check"></i>
                        <span class="menu-item">Approvals
                            @php try { $pc = \DB::table('propertys')->where('request_status','pending')->count(); } catch(\Exception $e){ $pc=0; } @endphp
                            @if($pc > 0)<span class="badge bg-danger ms-1" style="font-size:10px;vertical-align:middle;">{{ $pc }}</span>@endif
                        </span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->is('owner-management*') ? 'active' : '' }}">
                    <a href="{{ url('owner-management') }}" class="sidebar-link">
                        <i class="bi bi-house-door"></i><span class="menu-item">Owner Management</span>
                    </a>
                </li>

                @if(has_permissions('read','customer'))
                <li class="sidebar-item {{ request()->is('customer*') ? 'active' : '' }}">
                    <a href="{{ url('customer') }}" class="sidebar-link">
                        <i class="bi bi-people"></i><span class="menu-item">Customers</span>
                    </a>
                </li>
                @endif

                @if(has_permissions('read','verify_agent'))
                <li class="sidebar-item {{ request()->is('verify_agent*') ? 'active' : '' }}">
                    <a href="{{ url('verify_agent') }}" class="sidebar-link">
                        <i class="bi bi-shield-check"></i><span class="menu-item">Verify Agent</span>
                    </a>
                </li>
                @endif

                <li style="list-style:none;padding:10px 16px 2px;font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.7px;">Catalogue</li>

                @if(has_permissions('read','categories'))
                <li class="sidebar-item {{ request()->is('categories*') ? 'active' : '' }}">
                    <a href="{{ url('categories') }}" class="sidebar-link">
                        <i class="bi bi-tag"></i><span class="menu-item">Categories</span>
                    </a>
                </li>
                @endif

                <li class="sidebar-item {{ request()->is('amenities*') ? 'active' : '' }}">
                    <a href="{{ url('amenities') }}" class="sidebar-link">
                        <i class="bi bi-stars"></i><span class="menu-item">Amenities</span>
                    </a>
                </li>

                @if(has_permissions('read','facility'))
                <li class="sidebar-item {{ request()->is('outdoor_facilities*') ? 'active' : '' }}">
                    <a href="{{ url('outdoor_facilities') }}" class="sidebar-link">
                        <i class="bi bi-geo-alt"></i><span class="menu-item">Near By Places</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->is('parameters*') ? 'active' : '' }}">
                    <a href="{{ url('parameters') }}" class="sidebar-link">
                        <i class="bi bi-sliders"></i><span class="menu-item">Parameters</span>
                    </a>
                </li>
                @endif

                @if(has_permissions('read','slider'))
                <li class="sidebar-item {{ request()->is('slider*') ? 'active' : '' }}">
                    <a href="{{ url('slider') }}" class="sidebar-link">
                        <i class="bi bi-images"></i><span class="menu-item">Sliders / Banners</span>
                    </a>
                </li>
                @endif

                <li style="list-style:none;padding:10px 16px 2px;font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.7px;">Insights</li>

                <li class="sidebar-item {{ request()->is('reports*') ? 'active' : '' }}">
                    <a href="{{ url('reports') }}" class="sidebar-link">
                        <i class="bi bi-bar-chart-line"></i><span class="menu-item">Reports & Analytics</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->is('demo-settings*') ? 'active' : '' }}">
                    <a href="{{ url('demo-settings') }}" class="sidebar-link">
                        <i class="bi bi-hdd-network"></i>
                        <span class="menu-item">Demo Data
                            @php try { $demoOn = \DB::table('settings')->where('type','demo_mode_enabled')->value('data') === '1'; } catch(\Exception $e){ $demoOn=false; } @endphp
                            @if($demoOn)<span class="badge bg-success ms-1" style="font-size:10px;vertical-align:middle;">ON</span>@endif
                        </span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->is('search-settings*') ? 'active' : '' }}">
                    <a href="{{ url('search-settings') }}" class="sidebar-link">
                        <i class="bi bi-search"></i><span class="menu-item">Search Settings</span>
                    </a>
                </li>

                <li style="list-style:none;padding:10px 16px 2px;font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.7px;">Settings</li>

                @php
                    $sRoutes = ['about-us','privacy-policy','terms-conditions','language','system-settings','app-settings','web-settings','seo_settings','firebase_settings','notification-setting','email-configurations','email-templates','admin/appointment','log-viewer','user-accounts','system-version'];
                    $sActive = collect($sRoutes)->contains(fn($r) => request()->is($r.'*') || request()->is($r));
                @endphp

                <li class="sidebar-item has-sub {{ $sActive ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-gear"></i><span class="menu-item">Settings</span>
                    </a>
                    <ul class="submenu {{ $sActive ? 'active' : '' }}">

                        @if(has_permissions('read','user_accounts'))
                        <li class="submenu-item {{ request()->is('user-accounts*') ? 'active' : '' }}">
                            <a href="{{ url('user-accounts') }}"><i class="bi bi-person-badge me-1"></i>Users Accounts</a>
                        </li>
                        @endif

                        <li style="list-style:none;padding:2px 16px 0 32px;font-size:9px;font-weight:700;color:#CBD5E1;text-transform:uppercase;letter-spacing:.5px;">Content</li>

                        @if(has_permissions('read','about_us'))
                        <li class="submenu-item {{ request()->is('about-us*') ? 'active' : '' }}">
                            <a href="{{ url('about-us') }}"><i class="bi bi-info-circle me-1"></i>About Us</a>
                        </li>
                        @endif
                        @if(has_permissions('read','privacy_policy'))
                        <li class="submenu-item {{ request()->is('privacy-policy*') ? 'active' : '' }}">
                            <a href="{{ url('privacy-policy') }}"><i class="bi bi-shield-lock me-1"></i>Privacy Policy</a>
                        </li>
                        @endif
                        @if(has_permissions('read','terms_conditions'))
                        <li class="submenu-item {{ request()->is('terms-conditions*') ? 'active' : '' }}">
                            <a href="{{ url('terms-conditions') }}"><i class="bi bi-file-text me-1"></i>Terms & Condition</a>
                        </li>
                        @endif

                        <li style="list-style:none;padding:2px 16px 0 32px;font-size:9px;font-weight:700;color:#CBD5E1;text-transform:uppercase;letter-spacing:.5px;">System</li>

                        @if(has_permissions('read','language'))
                        <li class="submenu-item {{ request()->is('language*') ? 'active' : '' }}">
                            <a href="{{ url('language') }}"><i class="bi bi-translate me-1"></i>Languages</a>
                        </li>
                        @endif
                        @if(has_permissions('read','system_settings'))
                        <li class="submenu-item {{ request()->is('system-settings*') ? 'active' : '' }}">
                            <a href="{{ url('system-settings') }}"><i class="bi bi-toggles me-1"></i>System Settings</a>
                        </li>
                        @endif
                        @if(has_permissions('read','app_settings'))
                        <li class="submenu-item {{ request()->is('app-settings*') ? 'active' : '' }}">
                            <a href="{{ url('app-settings') }}"><i class="bi bi-phone me-1"></i>App Settings</a>
                        </li>
                        @endif
                        @if(has_permissions('read','web_settings'))
                        <li class="submenu-item {{ request()->is('web-settings*') ? 'active' : '' }}">
                            <a href="{{ url('web-settings') }}"><i class="bi bi-globe me-1"></i>Web Settings</a>
                        </li>
                        @endif
                        @if(has_permissions('read','seo_settings'))
                        <li class="submenu-item {{ request()->is('seo_settings*') ? 'active' : '' }}">
                            <a href="{{ url('seo_settings') }}"><i class="bi bi-search me-1"></i>SEO Settings</a>
                        </li>
                        @endif

                        <li style="list-style:none;padding:2px 16px 0 32px;font-size:9px;font-weight:700;color:#CBD5E1;text-transform:uppercase;letter-spacing:.5px;">Integrations</li>

                        @if(has_permissions('read','firebase_settings'))
                        <li class="submenu-item {{ request()->is('firebase_settings*') ? 'active' : '' }}">
                            <a href="{{ url('firebase_settings') }}"><i class="bi bi-fire me-1"></i>Firebase Settings</a>
                        </li>
                        @endif
                        @if(has_permissions('read','notification_settings'))
                        <li class="submenu-item {{ request()->is('notification-setting*') ? 'active' : '' }}">
                            <a href="{{ route('notification-setting-index') }}"><i class="bi bi-bell me-1"></i>Notification Settings</a>
                        </li>
                        @endif
                        @if(has_permissions('read','email_configurations'))
                        <li class="submenu-item {{ request()->is('email-configurations*') ? 'active' : '' }}">
                            <a href="{{ route('email-configurations-index') }}"><i class="bi bi-envelope-check me-1"></i>Email Configurations</a>
                        </li>
                        @endif
                        @if(has_permissions('read','email_templates'))
                        <li class="submenu-item {{ request()->is('email-templates*') ? 'active' : '' }}">
                            <a href="{{ route('email-templates.index') }}"><i class="bi bi-envelope-open me-1"></i>Email Templates</a>
                        </li>
                        @endif
                        @if(has_permissions('read','admin_appointment_preferences'))
                        <li class="submenu-item {{ request()->is('admin/appointment*') ? 'active' : '' }}">
                            <a href="{{ route('admin.appointment.index') }}"><i class="bi bi-calendar-event me-1"></i>Appointment Settings</a>
                        </li>
                        @endif

                        @if(has_permissions('read','system_settings'))
                        <li class="submenu-item {{ request()->is('log-viewer*') ? 'active' : '' }}">
                            <a href="{{ url('log-viewer') }}"><i class="bi bi-terminal me-1"></i>Log Viewer</a>
                        </li>
                        @endif
                        @if(has_permissions('read','system_update'))
                        <li class="submenu-item {{ request()->is('system-version*') ? 'active' : '' }}">
                            <a href="{{ url('system-version') }}"><i class="bi bi-cloud-arrow-down me-1"></i>System Update</a>
                        </li>
                        @endif

                    </ul>
                </li>

            </ul>
        </div>
    </div>
</div>
