<div class="card border-danger-subtle">
    <div class="card-body text-center py-4">
        <div class="mb-2">
            <i class="mdi mdi-close-octagon-outline text-danger" style="font-size:56px;"></i>
        </div>
        <h5 class="mb-2">Your company account was rejected</h5>
        <p class="text-muted">
            Please review the reason below and resubmit your details for approval.
        </p>

        @if(!empty($reason))
            <div class="alert alert-danger text-start mx-auto" style="max-width:680px;">
                <div class="fw-semibold mb-1">Reason</div>
                <div>{{ $reason }}</div>
            </div>
        @endif

        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="resubmit">
            Resubmit
        </button>
    </div>
</div>