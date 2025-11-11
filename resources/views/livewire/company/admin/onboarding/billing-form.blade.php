<div>
    <div class="row g-3">
        <div class="col-md-6">
            <x-forms.input label="Bank Name" name="bank_name" wire:model.defer="bank_name" />
            @error('bank_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <x-forms.input label="Branch" name="branch" wire:model.defer="branch" />
            @error('branch') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <x-forms.input label="Account Number" name="account_number" wire:model.defer="account_number" />
            @error('account_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <x-forms.input label="IFSC" name="ifsc" wire:model.defer="ifsc" />
            @error('ifsc') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_default" wire:model="is_default">
                <label class="form-check-label" for="is_default">Set as default account</label>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <button class="btn btn-primary w-100 text-light waves-effect waves-light" wire:click="save"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save & Continue</span>
            <span wire:loading><x-spinner size="sm" text="Saving..." /></span>
        </button>
    </div>
</div>