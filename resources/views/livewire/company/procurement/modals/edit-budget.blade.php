<x-ui.modal id="editBudget" :show="$show" size="xl" wire:key="edit-budget-modal-{{ $requestId ?? 'new' }}">    <x-slot:title>Edit Request — Budget & Logistics</x-slot:title>

    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Currency</label>
            <select class="form-select" wire:model.defer="currency">
                <option value="INR">INR (₹)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Budget Min (₹)</label>
            <input type="number" step="0.01" class="form-control" wire:model.defer="budget_min">
        </div>
        <div class="col-md-3">
            <label class="form-label">Budget Max (₹)</label>
            <input type="number" step="0.01" class="form-control" wire:model.defer="budget_max">
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment Terms</label>
            <select class="form-select" wire:model.defer="payment_terms">
                <option value="">—</option>
                <option value="advance">Advance</option>
                <option value="net_30">30 days</option>
                <option value="net_45">45 days</option>
                <option value="net_50">50 days</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Delivery Location</label>
            <textarea class="form-control" rows="2" wire:model.defer="delivery_location"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Preferred Vendor Regions</label>
            <input type="text" class="form-control" wire:model.defer="vendor_regions_input"
                placeholder="e.g., Maharashtra, Gujarat">
            <small class="text-muted">Comma-separated; saved as a list.</small>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea class="form-control" rows="2" wire:model.defer="notes"></textarea>
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
            wire:click="close">Close</button>
        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="save"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save</span>
            <span wire:loading><x-ui.spinner size="sm" text="Saving..." /></span>
        </button>
    </x-slot:footer>
</x-ui.modal>

<script>
    window.addEventListener('open-edit-budget-js', () => {
        const el = document.getElementById('editBudget');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    window.addEventListener('hide-edit-budget-js', () => {
        const el = document.getElementById('editBudget');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
</script>