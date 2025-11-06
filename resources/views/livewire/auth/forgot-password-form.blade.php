@section('auth-title', 'Forgot password')
@section('auth-subtitle', 'We’ll email you a reset link')

<x-alerts.flash />

<form class="needs-validation" novalidate wire:submit.prevent="send">
    <div class="mb-3">
        <x-forms.input label="Email" name="email" type="email" wire:model.defer="email" required
            placeholder="you@company.com" />
    </div>

    <button type="submit" class="btn btn-primary w-100 text-light waves-effect waves-light"
        wire:loading.attr="disabled">
        <span wire:loading.remove>Send reset link</span>
        <span wire:loading><x-spinner size="sm" text="Sending..." /></span>
    </button>

    <div class="text-center mt-3">
        <a class="text-muted" href="{{ route('login') }}">Back to sign in</a>
    </div>
</form>