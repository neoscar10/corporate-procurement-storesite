<div>
<x-alerts.flash />

@php $types = ['registered' => 'Registered', 'corporate' => 'Corporate', 'billing' => 'Billing']; @endphp

@foreach($types as $key => $label)
    <div class="border rounded p-3 mb-3">
        <h6 class="mb-3">{{ $label }} Address</h6>
        <div class="row g-3">
            <div class="col-md-6"><x-forms.input label="Address Line 1" wire:model.defer="{{ $key }}.line1" required />
            </div>
            <div class="col-md-6"><x-forms.input label="Address Line 2" wire:model.defer="{{ $key }}.line2" /></div>
            <div class="col-md-6"><x-forms.input label="City" wire:model.defer="{{ $key }}.city" /></div>
            <div class="col-md-6"><x-forms.input label="State" wire:model.defer="{{ $key }}.state" /></div>
            <div class="col-md-6"><x-forms.input label="PIN Code" wire:model.defer="{{ $key }}.pin_code" /></div>
            <div class="col-md-6"><x-forms.input label="Country" wire:model.defer="{{ $key }}.country" /></div>
        </div>
    </div>
@endforeach

<div class="d-flex justify-content-end">
    <button class="btn btn-primary text-light waves-effect waves-light w-100" wire:click="save" wire:loading.attr="disabled"
        wire:target="save">
        <span wire:loading.remove wire:target="save">
            Proceed <i class="mdi mdi-arrow-right align-middle ms-1"></i>
        </span>
        <span wire:loading wire:target="save">
            <x-spinner size="sm" text="Saving..." />
        </span>
    </button>

</div>

</div>