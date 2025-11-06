@section('auth-title', 'Reset password')
@section('auth-subtitle', 'Choose a new secure password')

 <x-alerts.flash />

  <div class="card">
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email"
               class="form-control @error('email') is-invalid @enderror"
               wire:model.lazy="email"
               @if($email !== '') readonly @endif>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password"
               class="form-control @error('password') is-invalid @enderror"
               wire:model.defer="password">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password"
               class="form-control"
               wire:model.defer="password_confirmation">
      </div>

      {{-- keep token in state; no input needed, Livewire already has it --}}
      <button class="btn btn-primary text-light waves-effect waves-light w-100"
              wire:click="submit" wire:loading.attr="disabled">
        <span wire:loading.remove>Reset Password</span>
        <span wire:loading><x-spinner size="sm" text="Working..." /></span>
      </button>
    </div>
  </div>