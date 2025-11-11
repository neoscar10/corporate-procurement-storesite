<div>
    <x-alerts.flash />

    <div class="border rounded p-3 mb-3">
        <h6 class="mb-3">Company Contact</h6>
        <div class="row g-3">
            <div class="col-md-6"><x-forms.input label="Official Email" wire:model.defer="official_email" /></div>
            <div class="col-md-6"><x-forms.input label="Alternate Email" wire:model.defer="alternate_email" /></div>
            <div class="col-md-6"><x-forms.input label="Primary Phone" wire:model.defer="primary_phone" /></div>
            <div class="col-md-6"><x-forms.input label="Contact Mobile" wire:model.defer="contact_mobile" /></div>
            <div class="col-md-12"><x-forms.input label="Website" wire:model.defer="website" /></div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
        <button class="btn w-100 btn-primary text-light waves-effect waves-light" wire:click="save"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save & proceed</span>
            <span wire:loading><x-spinner size="sm" text="Saving..." /></span>
        </button>
    </div>
</div>