<div>
    <x-alerts.flash />
    
    <div class="row g-3">
        <div class="col-md-4">
            <x-forms.input label="Average Monthly Budget" name="avg_monthly_budget" wire:model.defer="avg_monthly_budget"
                placeholder="e.g., ₹10,00,000" />
        </div>
        <div class="col-md-4">
            <label class="form-label">Procurement Type</label>
            <select class="form-select" wire:model.defer="procurement_type" name="procurement_type">
                <option value="both">Both</option>
                <option value="goods">Goods</option>
                <option value="services">Services</option>
            </select>
            @error('procurement_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    
        <div class="col-md-4">
            <label class="form-label">Frequency</label>
            <select class="form-select" wire:model.defer="frequency" name="frequency">
                <option value="">Select...</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="ad-hoc">Ad-hoc</option>
            </select>
            @error('frequency')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    
        <div class="col-md-6">
            <x-forms.input label="Preferred Payment Terms" name="preferred_payment_terms"
                wire:model.defer="preferred_payment_terms" placeholder="e.g., Net 30 / Net 45" />
        </div>
    
        <div class="col-6">
            <x-forms.input label="Preferred Vendor Locations (comma separated)" name="preferred_vendor_locations"
                wire:model.defer="preferred_vendor_locationsString" placeholder="Mumbai, Delhi, Bengaluru" />
            @push('scripts')
                <script>

                </script>
            @endpush
        </div>
    
        <div class="d-flex justify-content-center">
            <button class="btn w-25 btn-primary text-light waves-effect waves-light" wire:click="save"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Save & proceed</span>
                <span wire:loading><x-spinner size="sm" text="Saving..." /></span>
            </button>
        </div>
    </div>
</div>