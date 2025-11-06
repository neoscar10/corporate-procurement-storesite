<div>
    {{-- Titles for layouts.auth --}}
    @section('auth-title', 'Sign in')
    @section('auth-subtitle', 'Use your credentials to access your account')
    
    <x-alerts.flash />
    
    <form class="needs-validation" novalidate wire:submit.prevent="submit">
        <div class="mb-3">
            <x-forms.input label="Email" name="email" type="email" wire:model.defer="email" required
                placeholder="you@company.com" />
        </div>
    
        <div class="mb-2">
            <x-forms.input label="Password" name="password" type="password" wire:model.defer="password" required
                placeholder="••••••••" />
        </div>
    
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input id="remember" class="form-check-input" type="checkbox" wire:model="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a class="text-decoration-underline" href="{{ route('password.request') }}">Forgot password?</a>
        </div>
    
        <button type="submit" class="btn btn-primary w-100 text-light waves-effect waves-light"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading><x-spinner size="sm" text="Signing in..." /></span>
        </button>
    
        <div class="text-center mt-3">
            <a class="text-muted" href="{{ route('register') }}">Create a company account</a>
        </div>
    </form>
</div>