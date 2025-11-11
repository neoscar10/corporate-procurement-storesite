<div>
    <x-ui.stepper :steps="$steps" :current="min($step, 3)" class="mb-3" />

    <div id="wizard-step-container">
        @if((int) $step === 1)
            <livewire:auth.register.steps.company-basic-form :key="'reg-step-1'" />
        @elseif((int) $step === 2)
            <livewire:auth.register.steps.authorized-user-form :company-id="$companyId" :key="'reg-step-2'" />
        @elseif((int) $step === 3)
            <livewire:auth.register.steps.otp-verify :company-id="$companyId" :otp-id="$otpId" :key="'reg-step-3'" />
        @elseif((int) $step === 4)
            <livewire:auth.register.steps.success-card :key="'reg-step-4'" />
        @endif
    </div>

    <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-soft-secondary waves-effect waves-light" wire:click="back" wire:loading.attr="disabled"
            wire:target="back" @disabled($step === 1)>
            <span wire:loading.remove wire:target="back">Back</span>
            <span wire:loading wire:target="back"><x-spinner size="sm" text="..." /></span>
        </button>
        <div></div>
    </div>
</div>