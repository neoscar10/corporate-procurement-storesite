<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Auth • ' . config('app.name', 'Corporate Procurement') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Velzon head assets (order matters) --}}
    <script src="{{ asset('velzon/assets/js/layout.js') }}"></script>
    <link rel="shortcut icon" href="{{ asset('velzon/assets/images/favicon.ico') }}">
    <link href="{{ asset('velzon/assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('velzon/assets/css/icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('velzon/assets/css/app.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('velzon/assets/css/custom.min.css') }}" rel="stylesheet" />

  

    @stack('styles')
    @livewireStyles
</head>

{{-- ... head unchanged ... --}}

<body data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none"
    data-preloader="disable" data-theme="default" data-theme-colors="default">
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                {{-- Top logo / topline --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                @hasSection('auth-logo')
                                    @yield('auth-logo')
                                @else
                                    <a href="{{ url('/') }}" class="d-inline-block auth-logo">
                                        <img src="{{ asset('velzon/assets/images/logo-light.png') }}" alt="Logo"
                                            height="20">
                                    </a>
                                @endif
                            </div>
                            <p class="mt-3 fs-15 fw-medium">
                                {{ $authTopline ?? View::yieldContent('auth-topline', 'Secure access to Corporate Procurement') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card --}}
                <div class="row justify-content-center">
                    {{-- <div class="col-md-8 col-lg-6 col-xl-5"> --}}
                    <div class="col-md-8 col-lg-6 col-xl-6">
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">
                                        {{ $authTitle ?? View::yieldContent('auth-title', 'Welcome') }}
                                    </h5>
                                    <p class="text-muted">
                                        {{ $authSubtitle ?? View::yieldContent('auth-subtitle', 'Please continue below.') }}
                                    </p>
                                </div>

                                <div class="p-2 mt-4">
                                    {{-- Prefer Blade section if defined; otherwise render Livewire slot --}}
                                    @if (View::hasSection('form'))
                                        @yield('form')
                                    @else
                                        {{ $slot ?? '' }}
                                    @endif
                                </div>

                                {{-- Optional extras under the form --}}
                                @if (View::hasSection('form-extras'))
                                    <div class="mt-4 text-center">
                                        @yield('form-extras')
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Optional switch link (you can also render this inside your Livewire view) --}}
                        @if (View::hasSection('switch-link'))
                            <div class="mt-4 text-center">
                                @yield('switch-link')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer unchanged --}}
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                &copy;
                                <script>document.write(new Date().getFullYear())</script>
                                {{ config('app.name', 'Corporate Procurement') }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>