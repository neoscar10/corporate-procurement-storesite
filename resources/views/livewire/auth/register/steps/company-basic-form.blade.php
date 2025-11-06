<x-alerts.flash />



<form class="needs-validation" novalidate wire:submit.prevent="save">
    <div class="mb-3">
        <x-forms.input label="Legal Name" name="legal_name" wire:model.defer="legal_name" required
            placeholder="Registered legal name of the company as per government records." />
    </div>

    <div class="mb-3">
        <x-forms.input label="Brand Name" name="brand_name" wire:model.defer="brand_name" placeholder="The business or trade name used publicly if different from the registered name." />
    </div>

    <div class="row">

        <div class="mb-3 col-md-6">
            <x-forms.input label="CIN" name="cin" wire:model.defer="cin" maxlength="21" placeholder="21-char Company CIN" />
        
        </div>
        
        <div class="mb-3 col-md-6">
            <x-forms.input label="PAN" name="pan" wire:model.defer="pan" maxlength="10"
                placeholder="10-char PAN (ABCDE1234F)" />
        </div>
        
        
    </div>

    <div class="row">
        <div class="mb-3 col-md-6">
            <x-forms.input label="GSTIN" name="gstin" wire:model.defer="gstin" maxlength="15" placeholder="15-char GSTIN" />
        </div>

        <div class="mb-3 col-md-6">
            <x-forms.input label="Organization Type" name="organization_type" wire:model.defer="organization_type"
                placeholder="e.g., Private Limited" />
        </div>
    </div>

    <div class="row">
        <div class="mb-3 col-md-6">
            <x-forms.input label="Industry" name="industry" wire:model.defer="industry"
                placeholder="e.g., FMCG / Healthcare / IT" />
        </div>
        
        <div class="mb-4 col-md-6">
            <x-forms.input label="Nature of Business" name="nature_of_business" wire:model.defer="nature_of_business"
                placeholder="e.g., Manufacturing / Services" />
        </div>
    </div>
    

    <div class="mt-4">
        <button type="submit" class="btn btn-primary w-100 text-light waves-effect waves-light"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Continue</span>
            <span wire:loading><x-spinner size="sm" text="Saving..." /></span>
        </button>
    </div>
</form>