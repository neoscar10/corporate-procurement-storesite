<x-ui.modal id="editDetails" :show="$show" size="lg" wire:key="edit-details-modal-{{ $requestId ?? 'new' }}">
    <x-slot:title>Edit Request — Details</x-slot:title>
    <x-slot:title>Edit Request — Details</x-slot:title>

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" wire:model.defer="title">
            @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select class="form-select" wire:model.defer="type">
                <option value="rfi">RFI</option>
                <option value="req">REQ</option>
                <option value="po">PO</option>
                <option value="rfp">RFP</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Priority</label>
            <select class="form-select" wire:model.defer="priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Desired Response At</label>
            <input type="datetime-local" class="form-control" wire:model.defer="desired_response_at">
        </div>
        <div class="col-md-4">
            <label class="form-label">Expected Delivery At</label>
            <input type="datetime-local" class="form-control" wire:model.defer="expected_delivery_at">
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
    // open fallback (already existed)
    window.addEventListener('open-edit-details-js', () => {
        const el = document.getElementById('editDetails');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    // hide after save
    window.addEventListener('hide-edit-details-js', () => {
        const el = document.getElementById('editDetails');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
</script>