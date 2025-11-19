<x-ui.modal id="vcUpsert" wire:model.live="show" size="lg" wire:ignore.self>
    <x-slot:title>
        {{ $editId ? 'Edit Category' : 'New Category' }}
    </x-slot:title>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kind</label>
            <select class="form-select" wire:model.live="kind">
                <option value="product">Product</option>
                <option value="service">Service</option>
            </select>
            @error('kind')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-5">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g., Electronics">
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" wire:model.defer="slug" placeholder="auto if empty">
            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Order</label>
            <input type="number" class="form-control" wire:model.defer="display_order" min="0">
            @error('display_order')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="vc_active"
                    wire:model.defer="is_active">
                <label class="form-check-label" for="vc_active">Active</label>
            </div>
            @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" wire:model.defer="description" placeholder="Optional"></textarea>
            @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
            wire:click="$set('show', false)">
            Cancel
        </button>

        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="submit"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save</span>
            <span wire:loading><x-ui.spinner size="sm" text="Saving..." /></span>
        </button>
    </x-slot:footer>
</x-ui.modal>


<script>
    window.addEventListener('vc:open-upsert-js', () => {
        const el = document.getElementById('vcUpsert');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    window.addEventListener('vc:close-upsert-js', () => {
        const el = document.getElementById('vcUpsert');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
</script>