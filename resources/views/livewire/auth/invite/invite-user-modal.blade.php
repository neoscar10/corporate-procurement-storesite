{{-- resources/views/livewire/auth/invite/invite-user-modal.blade.php --}}
<div
  x-data="{
      open: @entangle('open').live,
      init() {
          const el = document.getElementById('inviteUserModal')
          const modal = bootstrap.Modal.getOrCreateInstance(el)
          // sync LW -> BS
          this.$watch('open', v => v ? modal.show() : modal.hide())
          // sync BS -> LW
          el.addEventListener('hidden.bs.modal', () => { this.open = false })
      }
  }"
>
    <x-ui.modal id="inviteUserModal" title="Invite Company User" size="lg">
        <div class="mb-2">
            <p class="text-muted mb-2">Send an invitation and optionally assign permissions.</p>
            <x-alerts.flash />
        </div>

        <div class="row g-3">
            {{-- inside the <div class="row g-3"> … --}}
                <div class="col-md-8">
                    <x-forms.input label="User Email" type="email" wire:model.defer="email" required
                        placeholder="user@company.com" />
                </div>
            
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-soft-primary w-100 waves-effect waves-light"
                        wire:click="$toggle('showPermissions')">
                        <i class="mdi mdi-shield-key-outline me-1"></i>
                        Select permissions
                    </button>
                </div>
            
                {{-- NEW: password options --}}
                <div class="col-12">
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="autoPwd" wire:model.live="autoPassword">
                                    <label class="form-check-label" for="autoPwd">
                                        Auto-generate password and email to the user
                                    </label>
                                </div>
                                <small class="text-muted ms-3">If unchecked, you must set a password below.</small>
                            </div>
            
                            @if(!$autoPassword)
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <x-forms.input label="Password" type="password" wire:model.defer="password" />
                                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <x-forms.input label="Confirm Password" type="password"
                                            wire:model.defer="password_confirmation" />
                                        @error('password_confirmation')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            
                {{-- keep your permissions card block below as you had it --}}


            @if($showPermissions)
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Permissions</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-ghost-secondary btn-sm material-shadow-none" wire:click="clearAll">
                                    Clear
                                </button>
                                <button type="button" class="btn btn-soft-primary btn-sm waves-effect waves-light" wire:click="selectAll">
                                    Select all
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-2" style="max-height: 240px; overflow:auto;">
                            <div class="row g-2">
                                @foreach($available as $p)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   id="perm_{{ $p['id'] }}"
                                                   wire:click="togglePermission('{{ $p['name'] }}')"
                                                   @checked($selected[$p['name']] ?? false)>
                                            <label class="form-check-label" for="perm_{{ $p['id'] }}">
                                                {{ $p['label'] }}
                                                
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light material-shadow-none"
                    x-on:click="open=false" wire:loading.attr="disabled">
                Cancel
            </button>
            <button type="button" class="btn btn-primary text-light waves-effect waves-light" wire:click="submit"
                wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Send invite</span>
                <span wire:loading wire:target="submit">
                    <x-spinner size="sm" text="Sending..." />
                </span>
            </button>

        </x-slot:footer>
    </x-ui.modal>
</div>
