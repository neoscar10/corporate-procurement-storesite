<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" wire:key="approve-modal">
            <div class="modal-header">
                <h5 class="modal-title text-success"><i class="mdi mdi-check-decagram-outline me-1"></i> Approve company
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Approve this company? The status will change to <strong>Approved</strong>.
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-success text-light" wire:click="approve">Approve</button>
            </div>
        </div>
    </div>
</div>