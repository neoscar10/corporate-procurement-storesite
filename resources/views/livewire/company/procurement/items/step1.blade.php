<div class="row g-3" wire:key="step1">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" wire:model.defer="name">
        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Priority</label>
        <select class="form-select" wire:model.defer="priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">Urgent</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Unit</label>
        <input type="text" class="form-control" wire:model.defer="unit" placeholder="e.g., pcs, hr">
    </div>
    <div class="col-md-3">
        <label class="form-label">Date Required</label>
        <input type="date" class="form-control" wire:model.defer="date_required">
    </div>
    <div class="col-md-3">
        <label class="form-label">Budget Amount (₹)</label>
        <input type="number" step="0.01" class="form-control" wire:model.defer="budget_amount">
    </div>
    <div class="col-9">
        <label class="form-label">Short Description</label>
        <textarea class="form-control" rows="2" wire:model.defer="short_description"></textarea>
    </div>

    @if($kind === 'service')
        <div class="col-md-3">
            <label class="form-label">Budget Mode</label>
            <select class="form-select" wire:model.defer="service_budget_mode">
                <option value="">—</option>
                <option value="per_hour">Per Hour</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Payment Type</label>
            <select class="form-select" wire:model.defer="service_payment_type">
                <option value="">—</option>
                <option value="per_hour">Per Hour</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>
    @endif
    </div>
    <div class="mt-3 d-flex justify-content-end">
        <button class="btn btn-primary waves-effect waves-light" wire:click="saveDetail">
            Save & Continue
        </button>
    </div>
</div>