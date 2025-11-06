<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" wire:key="reject-modal">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="mdi mdi-close-octagon-outline me-1"></i> Reject company
                </h5>
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
                <button class="btn btn-danger text-light" wire:click="reject">Reject</button>
            </div>
        </div>
    </div>
</div>