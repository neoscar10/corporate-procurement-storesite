<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" wire:key="cancel-modal">
            <div class="modal-header">
                <h5 class="modal-title text-secondary"><i class="mdi mdi-cancel me-1"></i> Cancel request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <x-forms.textarea label="Reason" name="reason" rows="4" wire:model.defer="reason" />
                    @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-secondary text-light" wire:click="cancel">Cancel Request</button>
            </div>
        </div>
    </div>
</div>