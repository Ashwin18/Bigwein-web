<!DOCTYPE html>

@if($language)
    @if ($language->rtl)
        <html lang="en" dir="rtl">
    @else
        <html lang="en">
    @endif
@else
    <html lang="en">
@endif

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="shortcut icon" href="{{ url('assets/images/logo/' . (system_setting('favicon_icon') ?? null)) }}" type="image/x-icon">
    <title>@yield('title') || {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @include('layouts.include')
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-redesign-v23.css') }}?v=23.1">
    <link rel="stylesheet" href="{{ asset('assets/css/sidebar-layout-v5.css') }}?v=5.1.2">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-workflow-review.css') }}?v=2.1.0">
    @yield('css')
    
    <!-- Global Language Data for TinyMCE RTL Support -->
    <script>
        window.globalLanguageData = {
            current: {
                rtl: {{ $language && $language->rtl ? 'true' : 'false' }},
                code: "{{ $language ? $language->code : 'en' }}",
                name: "{{ $language ? $language->name : 'English' }}"
            },
            all: {
                @if(isset($allLanguages) && $allLanguages->count() > 0)
                    @foreach($allLanguages as $lang)
                        "{{ $lang->id }}": {
                            "rtl": {{ $lang->rtl ? 'true' : 'false' }},
                            "code": "{{ $lang->code }}",
                            "name": "{{ $lang->name }}"
                        }{{ $loop->last ? '' : ',' }}
                    @endforeach
                @endif
            }
        };
    </script>
</head>

<body>
    <div id="app">
        @include('layouts.sidebar')
        
        <!-- Mobile sidebar overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div id="main" class='layout-navbar'>
            @include('layouts.topbar')
            <div id="main-content">
                <div class="page-heading">

                    @yield('page-title')
                </div>
                @yield('content')

            </div>

        </div>
        <div class="wrapper mt-5">
            <div class="content">
                @include('layouts.footer')

                <!-- Your page content here -->
            </div>
        </div>
        {{-- <div>
            @include('layouts.footer')
        </div> --}}
    </div>

    @include('layouts.footer_script')
    <script>
    /* Auto-reload on approval success in detail pages */
    (function () {
        var origOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function (method, url) {
            this.addEventListener('load', function () {
                if ((url.includes('update-property-request-status') ||
                     url.includes('update-project-request-status')) &&
                     window.location.href.includes('/detail')) {
                    try {
                        var r = JSON.parse(this.responseText);
                        if (!r.error) setTimeout(function () { window.location.reload(); }, 900);
                    } catch (e) { setTimeout(function () { window.location.reload(); }, 900); }
                }
            });
            origOpen.apply(this, arguments);
        };
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.href.includes('/detail')) {
                window.editFormSuccessFunction = function () {
                    setTimeout(function () { window.location.reload(); }, 900);
                };
            }
        });
    })();
    </script>
    @yield('js')
    @yield('script')

    <script>
    /* Keep the desktop workspace aligned with the actual sidebar state. */
    (function () {
        function syncBigWeinSidebarLayout() {
            var sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            if (window.innerWidth >= 768) {
                document.body.classList.toggle('bw-sidebar-collapsed', !sidebar.classList.contains('active'));
            } else {
                document.body.classList.remove('bw-sidebar-collapsed');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var sidebar = document.getElementById('sidebar');
            syncBigWeinSidebarLayout();

            if (sidebar && window.MutationObserver) {
                new MutationObserver(function (mutations) {
                    for (var i = 0; i < mutations.length; i++) {
                        if (mutations[i].attributeName === 'class') {
                            syncBigWeinSidebarLayout();
                            break;
                        }
                    }
                }).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
            }

            var toggle = document.getElementById('sidebarToggle');
            if (toggle) {
                toggle.addEventListener('click', function () {
                    setTimeout(syncBigWeinSidebarLayout, 0);
                    setTimeout(syncBigWeinSidebarLayout, 80);
                });
            }

            window.addEventListener('resize', syncBigWeinSidebarLayout);
        });
    })();
    </script>
</body>

</html>
