<div>
    <x-alerts.flash />

    <div class="row g-3">
        <div class="col-md-6"><x-forms.input label="Full Name" wire:model.defer="full_name" required /></div>
        <div class="col-md-6"><x-forms.input label="Designation" wire:model.defer="designation" /></div>
        <div class="col-md-6"><x-forms.input label="Email" wire:model.defer="email" required /></div>
        <div class="col-md-6"><x-forms.input label="Mobile" wire:model.defer="mobile" required /></div>
        <div class="col-md-6">
            <x-forms.select label="Govt ID Type" wire:model.defer="govt_id_type" required>
                <option value="">Select ID Type</option>
                <option value="aadhaar">Aadhaar</option>
                <option value="pan">PAN</option>
                <option value="passport">Passport</option>
                <option value="voter_id">Voter ID (EPIC)</option>
                <option value="driving_license">Driving Licence</option>
                <option value="nrega_job_card">NREGA Job Card</option>
            </x-forms.select>
        </div>
        <div class="col-md-6"><x-forms.input label="Govt ID Number" wire:model.defer="govt_id_number" /></div>

        {{-- Verification selection in same step (merged) --}}
        <div class="col-12">
            <label class="form-label">Verify via</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="vmEmail" value="email" wire:model="channel">
                    <label class="form-check-label" for="vmEmail">Email</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" id="vmSms" value="sms" wire:model="channel">
                    <label class="form-check-label" for="vmSms">SMS</label>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end">
            <button class="btn btn-light material-shadow-none" wire:click="save" wire:loading.attr="disabled"
                wire:target="save">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save"><x-spinner size="sm" text="Saving..." /></span>
            </button>

            <button class="btn btn-primary text-light waves-effect waves-light" wire:click="saveAndSendOtp"
                wire:loading.attr="disabled" wire:target="saveAndSendOtp">
                <span wire:loading.remove wire:target="saveAndSendOtp">
                    Save & Send Code <i class="mdi mdi-arrow-right align-middle ms-1"></i>
                </span>
                <span wire:loading wire:target="saveAndSendOtp">
                    <x-spinner size="sm" text="Sending..." />
                </span>
            </button>
        </div>
    </div>
</div>