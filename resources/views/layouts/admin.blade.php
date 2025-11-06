<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ ($title ?? View::yieldContent('title', 'Dashboard')) }} |
        {{ config('app.name', 'Corporate Procurement') }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Favicon --}}
    <link rel="shortcut icon" href="{{ asset('velzon/assets/images/favicon.ico') }}">

    {{-- Velzon / Template CSS & head JS (order matters) --}}
    <link rel="stylesheet" href="{{ asset('velzon/assets/libs/jsvectormap/jsvectormap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('velzon/assets/libs/swiper/swiper-bundle.min.css') }}">
    <script src="{{ asset('velzon/assets/js/layout.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('velzon/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('velzon/assets/css/icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('velzon/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('velzon/assets/css/custom.min.css') }}">

    {{-- IMPORTANT: ensure app.scss does NOT re-import bootstrap to avoid double CSS --}}
    {{-- @vite(['resources/scss/app.scss', 'resources/js/app.js']) --}}

    @stack('styles')
    @livewireStyles

    {{-- Page transition loader styles --}}
    <style>
        :root {
            --loader-bg: rgba(255, 255, 255, .72);
            --loader-shadow: 0 .25rem .75rem rgba(16, 24, 40, .12);
            --loader-size: 44px;
            --loader-ring: 3px;
        }

        [data-bs-theme="dark"] :root,
        html[data-bs-theme="dark"] {
            --loader-bg: rgba(17, 20, 24, .72);
        }

        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 5000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--loader-bg);
            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, visibility .16s ease;
        }

        body.page-loading .page-loader {
            opacity: 1;
            visibility: visible;
            pointer-events: all;
        }

        .page-loader .loader-card {
            background: #fff;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            box-shadow: var(--loader-shadow);
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        [data-bs-theme="dark"] .page-loader .loader-card {
            background: #0f1216;
        }

        .loader-ring {
            width: var(--loader-size);
            height: var(--loader-size);
            border-radius: 50%;
            border: var(--loader-ring) solid rgba(13, 110, 253, .18);
            border-top-color: var(--vz-primary, #405189);
            animation: loader-spin .8s linear infinite;
        }

        @keyframes loader-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            font-weight: 600;
            font-size: .95rem;
        }

        .loader-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--vz-primary, #405189);
            animation: loader-pulse 1s ease-in-out infinite;
        }

        @keyframes loader-pulse {

            0%,
            100% {
                transform: scale(.75);
                opacity: .6;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .page-loader {
                transition: none;
            }

            .loader-ring,
            .loader-dot {
                animation: none;
            }
        }

        .btn-outline-success:hover,
        .btn-outline-success:focus {
            color: #fff !important;
        }
    </style>
</head>

<body data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none"
    data-preloader="disable" data-theme="default" data-theme-colors="default">
    <div id="layout-wrapper">
        @include('partials.topbar')
        @include('partials.sidebar')

        {{-- Required by Velzon for vertical layout overlay/closing sidebar --}}
        <div class="vertical-overlay"></div>

        {{-- Global page loader --}}
        <div id="page-loader" class="page-loader" aria-hidden="true">
            <div class="loader-card">
                <div class="loader-ring" aria-hidden="true"></div>
                <div class="loader-text">Loading…</div>
                <div class="loader-dot" aria-hidden="true"></div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @hasSection('content') @yield('content') @else {{ $slot ?? '' }} @endif
                </div>
            </div>
            @include('partials.footer')
        </div>
    </div>

    {{-- Optional: Theme customizer/offcanvas --}}
    @includeWhen(config('app.show_customizer', false), 'partials.admin.customizer')

    {{-- Core vendor JS (order matters) --}}
    <script src="{{ asset('velzon/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('velzon/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('velzon/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('velzon/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('velzon/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('velzon/assets/js/plugins.js') }}"></script>

    {{-- Heavy libs removed from global; push only on pages that need them --}}
    <script src="{{ asset('velzon/assets/js/app.js') }}"></script>

    {{-- Sidebar toggle + ROUTE-ONLY loader controller with modal-safe guards --}}
    <script>
        (function () {
            // ---------- Loader: route-only ----------
            let showTimer = null, visibleSince = 0, killTimer = null;
            const SPA_DELAY = 60, MIN_VISIBLE = 120, MAX_VISIBLE = 2500;
            const isVisible = () => document.body.classList.contains('page-loading');
            const reallyShow = () => { if (!isVisible()) { visibleSince = Date.now(); document.body.classList.add('page-loading'); } };
            const forceHide = () => document.body.classList.remove('page-loading');
            const show = (delayed = true) => { clearTimeout(showTimer); if (delayed) { showTimer = setTimeout(reallyShow, SPA_DELAY); } else { reallyShow(); } clearTimeout(killTimer); killTimer = setTimeout(forceHide, MAX_VISIBLE + 500); };
            const hide = () => {
                clearTimeout(showTimer); clearTimeout(killTimer);
                if (!isVisible()) return;
                const el = Date.now() - (visibleSince || 0);
                const wait = el >= MIN_VISIBLE ? 0 : (MIN_VISIBLE - el);
                setTimeout(() => document.body.classList.remove('page-loading'), wait);
            };

            // ---------- Sidebar toggle ----------
            const initSidebar = () => {
                const btn = document.querySelector('.vertical-menu-btn');
                const overlay = document.querySelector('.vertical-overlay');
                const toggleSidebar = () => {
                    document.body.classList.toggle('sidebar-enable');
                    if (window.innerWidth >= 992) {
                        const cur = document.body.getAttribute('data-sidebar-size') || 'lg';
                        document.body.setAttribute('data-sidebar-size', cur === 'sm' ? 'lg' : 'sm');
                    }
                };
                if (btn && !btn.dataset.bound) { btn.addEventListener('click', toggleSidebar); btn.dataset.bound = '1'; }
                if (overlay && !overlay.dataset.bound) { overlay.addEventListener('click', () => document.body.classList.remove('sidebar-enable')); overlay.dataset.bound = '1'; }
                if (!document.body.getAttribute('data-sidebar-size')) document.body.setAttribute('data-sidebar-size', 'lg');
            };

            // ---------- Helpers ----------
            const isMod = (e) => e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0;
            const sameOrigin = (href) => { try { return new URL(href, location.href).origin === location.origin; } catch { return false; } };
            // Treat modal & dropdown UI as non-navigation (ignore in route loader)
            const isUiAction = (el) => {
                return !!(
                    el.closest('[data-bs-toggle="modal"]') ||
                    el.closest('[data-bs-target]') ||
                    el.closest('[data-bs-dismiss="modal"]') ||
                    el.closest('[data-bs-toggle="dropdown"]') ||  // dropdown toggles
                    el.closest('.dropdown-menu') ||               // any click inside menu
                    el.closest('.dropdown')                       // dropdown container
                );
            };

            // ---------- Livewire SPA navigation only ----------
            const bindLivewireRouteLoader = () => {
                document.removeEventListener('livewire:navigating', document._lwNavStart || (() => { }));
                document.removeEventListener('livewire:navigated', document._lwNavEnd || (() => { }));

                document._lwNavStart = () => { show(true); };
                document._lwNavEnd = () => { hide(); setTimeout(forceHide, MAX_VISIBLE); };

                document.addEventListener('livewire:navigating', document._lwNavStart);
                document.addEventListener('livewire:navigated', document._lwNavEnd);
            };

            // ---------- Intercept normal internal link clicks (non-SPA) ----------
            const bindLinkLoader = () => {
                document.removeEventListener('click', document._pageLinkHandler || (() => { }), true);
                document._pageLinkHandler = (e) => {
                    // Ignore dropdowns & modals entirely
                    if (isUiAction(e.target)) return;

                    const a = e.target.closest('a[href]'); if (!a) return;
                    if (isMod(e)) return;

                    const href = a.getAttribute('href') || '';
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                    if (a.target === '_blank') return;
                    if (!sameOrigin(href)) return;
                    if (a.hasAttribute('data-no-loader')) return;

                    // SPA links: handled by Livewire events
                    if (a.hasAttribute('wire:navigate') || a.getAttribute('data-navigate') === 'true') return;

                    // Non-SPA route change
                    show(false);
                };
                document.addEventListener('click', document._pageLinkHandler, true);

                // Final fallback
                window.addEventListener('beforeunload', () => show(false));
                document.addEventListener('visibilitychange', () => { if (!document.hidden) forceHide(); });
            };

            // ---------- Modal guards ----------
            const bindModalGuards = () => {
                document.addEventListener('show.bs.modal', () => forceHide(), true);
                document.addEventListener('shown.bs.modal', () => forceHide(), true);
                document.addEventListener('hide.bs.modal', () => forceHide(), true);
                document.addEventListener('hidden.bs.modal', () => forceHide(), true);
            };

            const init = () => { initSidebar(); bindLivewireRouteLoader(); bindLinkLoader(); bindModalGuards(); };
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
            document.addEventListener('livewire:navigated', init);
        })();
    </script>

   

    @stack('scripts')
    @livewireScripts
</body>

</html>