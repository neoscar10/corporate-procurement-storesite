{{-- resources/views/partials/sidebar.blade.php --}}
<div class="app-menu navbar-menu">
    {{-- LOGO --}}
    <div class="navbar-brand-box">
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm"><img src="{{ asset('velzon/assets/images/logo-sm.png') }}" alt="Logo"
                    height="22"></span>
            <span class="logo-lg"><img src="{{ asset('velzon/assets/images/logo-dark.png') }}" alt="Logo"
                    height="17"></span>
        </a>
        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm"><img src="{{ asset('velzon/assets/images/logo-sm.png') }}" alt="Logo"
                    height="22"></span>
            <span class="logo-lg"><img src="{{ asset('velzon/assets/images/logo-light.png') }}" alt="Logo"
                    height="17"></span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="mdi mdi-record-circle-outline"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>

            @php
                use Illuminate\Support\Facades\Auth;
                use Illuminate\Support\Facades\Route;
                use App\Models\Company\CompanyMember;

                $user = Auth::user();
                $isAdmin = $user && (int) ($user->is_admin ?? 0) === 1;

                // Latest active membership (if any)
                $membership = $user
                    ? CompanyMember::with('company')->where('user_id', $user->id)->where('is_active', true)->latest('id')->first()
                    : null;

                $company = optional($membership)->company;
                $companyStatus = $company->status ?? null;
                $showOnboarding = $companyStatus === 'pending';

                // Route helpers + active states
                $active = fn(string $pattern) => request()->routeIs($pattern) ? 'active' : '';

                $adminDashboardHref = Route::has('admin.dashboard') ? route('admin.dashboard') : '#';
                $adminRequestsHref = Route::has('admin.company.requests.index') ? route('admin.company.requests.index') : '#';
                $companyOnboardingHref = Route::has('company.onboarding') ? route('company.onboarding') : '#';
                $companyAdminDashHref = Route::has('company.admin.dashboard') ? route('company.admin.dashboard') : '#';
                $companyUserDashHref = Route::has('company.user.dashboard') ? route('company.user.dashboard') : '#';

                // Procurements route: prefer your provided (typo) name, fallback to corrected name if you fix it later
                $companyProcHref = Route::has('comapany.procurements')
                    ? route('comapany.procurements')
                    : (Route::has('company.procurements') ? route('company.procurements') : '#');
            @endphp

            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>

                {{-- Company area (visible if user has a membership) --}}
                @if($membership)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $active('company.admin.dashboard') }} {{ $active('company.user.dashboard') }}"
                            href="{{ $membership->role === 'company_admin' ? $companyAdminDashHref : $companyUserDashHref }}">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- Procurements --}}
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $active('comapany.procurements') }} {{ $active('company.procurements') }}"
                            href="{{ $companyProcHref }}">
                            <i class="mdi mdi-clipboard-list-outline"></i>
                            <span>Procurements</span>
                        </a>
                    </li>

                    @if($showOnboarding)
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $active('company.onboarding') }}"
                                href="{{ $companyOnboardingHref }}">
                                <i class="mdi mdi-clipboard-text-outline"></i>
                                <span>Onboarding</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- Super Admin section --}}
                @if($isAdmin)
                    <li class="menu-title"><span>Admin</span></li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $active('admin.dashboard') }}" href="{{ $adminDashboardHref }}">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            <span>Admin Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $active('admin.company.requests.*') }}"
                            href="{{ $adminRequestsHref }}">
                            <i class="mdi mdi-clipboard-text-outline"></i>
                            <span>Registration Requests</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>