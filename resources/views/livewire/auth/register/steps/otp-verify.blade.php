<div>
    <x-alerts.flash />
    
    <div class="row g-3">
        <div class="col-md-6">
            {{-- This now updates $code reliably --}}
            <x-forms.otp-input name="code" wireModel="code" length="6" idPrefix="reg_otp_" />
        </div>
        <div>
            <span><a href="">Resend OTP</a></span>
        </div>
    
        <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary text-light waves-effect waves-light" wire:click="verify"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Verify & Create Account</span>
                <span wire:loading><x-spinner size="sm" text="Verifying..." /></span>
            </button>
        </div>
    </div>
</div>