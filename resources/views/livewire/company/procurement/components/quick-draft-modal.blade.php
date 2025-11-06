<div wire:key="quick-draft-modal-root">
    <x-ui.modal id="quickDraftModal" title="Create New Request" size="lg" staticBackdrop>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" wire:model.defer="title"
                    placeholder="e.g., Laptops for Engineering Team (Q2)">
                @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model.defer="type">
                    <option value="REQ">REQ</option>
                    <option value="RFI">RFI</option>
                    <option value="RFP">RFP</option>
                    <option value="PO">PO</option>
                </select>
                @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select class="form-select" wire:model.defer="priority">
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                </select>
                @error('priority')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <x-slot:footer>
            <button class="btn btn-light material-shadow-none" wire:click="$set('show', false)"
                wire:loading.attr="disabled">
                Cancel
            </button>
            <button class="btn btn-primary waves-effect waves-light" wire:click="create" wire:loading.attr="disabled">
                <span wire:loading.remove>Create Draft & Continue</span>
                <span wire:loading><x-ui.spinner size="sm" text="Creating..." /></span>
            </button>
        </x-slot:footer>
    </x-ui.modal>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                const getModal = () => {
                    const el = document.getElementById('quickDraftModal');
                    return el ? bootstrap.Modal.getOrCreateInstance(el) : null;
                };

                Livewire.on('quick-draft:show', () => {
                    const m = getModal(); if (m) m.show();
                });

                Livewire.on('quick-draft:hide', () => {
                    const m = getModal(); if (m) m.hide();
                });
            });
        </script>
    @endpush
</div>