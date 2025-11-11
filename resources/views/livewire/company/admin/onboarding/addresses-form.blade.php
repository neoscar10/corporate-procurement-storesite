<div>
    <x-alerts.flash />

    @php $types = ['registered' => 'Registered', 'corporate' => 'Corporate', 'billing' => 'Billing']; @endphp
    @foreach($types as $key => $label)
        <div class="border rounded p-3 mb-3">
            <h6 class="mb-3">{{ $label }} Address</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <x-forms.input label="Address Line 1" wire:model.defer="{{ $key }}.line1" required />
                    @error($key . '.line1')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6"><x-forms.input label="Address Line 2" wire:model.defer="{{ $key }}.line2" /></div>
                <div class="col-md-6"><x-forms.input label="City" wire:model.defer="{{ $key }}.city" /></div>
                <div class="col-md-6"><x-forms.input label="State" wire:model.defer="{{ $key }}.state" /></div>
                <div class="col-md-6"><x-forms.input label="PIN Code" wire:model.defer="{{ $key }}.pin_code" /></div>
                <div class="col-md-6"><x-forms.input label="Country" wire:model.defer="{{ $key }}.country" /></div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-center">
        <button class="btn w-100 btn-primary text-light waves-effect waves-light" wire:click="save"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save & proceed</span>
            <span wire:loading><x-spinner size="sm" text="Saving..." /></span>
        </button>
    </div>
</div>