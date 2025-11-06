<x-alerts.flash />

<div class="row g-3">
    <div class="col-md-6"><x-forms.input label="Official Email" wire:model.defer="official_email" /></div>
    <div class="col-md-6"><x-forms.input label="Alternate Email" wire:model.defer="alternate_email" /></div>
    <div class="col-md-6"><x-forms.input label="Primary Phone" wire:model.defer="primary_phone" /></div>
    <div class="col-md-6"><x-forms.input label="Contact Mobile" wire:model.defer="contact_mobile" /></div>
    <div class="col-md-12"><x-forms.input label="Website" wire:model.defer="website" /></div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary text-light waves-effect waves-light w-100" wire:click="save" wire:loading.attr="disabled"
            wire:target="save">
            <span wire:loading.remove wire:target="save">Proceed <i class="mdi mdi-arrow-right align-middle ms-1"></i></span>
            <span wire:loading wire:target="save">
                <x-spinner size="sm" text="Saving..." />
            </span>
        </button>
    </div>

</div>