{{-- resources/views/partials/topbar.blade.php --}}
<header id="page-topbar">
  <div class="layout-width">
    <div class="navbar-header">
      <div class="d-flex">
        {{-- LOGO --}}
        <div class="navbar-brand-box horizontal-logo">
          <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
              <img src="{{ asset('velzon/assets/images/logo-sm.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
              <img src="{{ asset('velzon/assets/images/logo-dark.png') }}" alt="Logo" height="17">
            </span>
          </a>

          <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm">
              <img src="{{ asset('velzon/assets/images/logo-sm.png') }}" alt="Logo" height="22">
            </span>
            <span class="logo-lg">
              <img src="{{ asset('velzon/assets/images/logo-light.png') }}" alt="Logo" height="17">
            </span>
          </a>
        </div>

        {{-- Sidebar toggle --}}
        <button type="button"
                class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none"
                id="topnav-hamburger-icon">
          <span class="hamburger-icon">
            <span></span><span></span><span></span>
          </span>
        </button>
      </div>

      <div class="d-flex align-items-center">
        @php
            $user = Auth::user();
            $name = $user?->name ?? $user?->full_name ?? $user?->email ?? 'User';
            $avatar = $user?->avatar_url
                ?? $user?->profile_photo_url
                ?? asset('velzon/assets/images/users/avatar-1.jpg');

            // Profile route candidates used in this project
            $profileRoute = collect(['profile', 'profile.edit', 'account.profile', 'user.profile'])
                ->first(fn($r) => \Illuminate\Support\Facades\Route::has($r));
            $profileHref = $profileRoute ? route($profileRoute) : '#';

            // Same logout route for all users (single guard)
            $logoutAction = \Illuminate\Support\Facades\Route::has('logout')
                ? route('logout')
                : url('/logout'); // fallback if named route not set
        @endphp

        {{-- USER DROPDOWN --}}
        <div class="dropdown ms-sm-3 header-item topbar-user">
          <button type="button" class="btn material-shadow-none"
                  id="page-header-user-dropdown"
                  data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center">
              <img class="rounded-circle header-profile-user" src="{{ $avatar }}" alt="Avatar">
              <span class="text-start ms-xl-2">
                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $name }}</span>
              </span>
            </span>
          </button>

          <div class="dropdown-menu dropdown-menu-end">
            {{-- Profile --}}
            <a class="dropdown-item" href="{{ $profileHref }}">
              <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
              <span class="align-middle">Profile</span>
            </a>

            <div class="dropdown-divider"></div>

            {{-- Logout (POST to the same route used across the app) --}}
            <form method="POST" action="{{ $logoutAction }}">
              @csrf
              <button type="submit" class="dropdown-item">
                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                <span class="align-middle">Logout</span>
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</header>
