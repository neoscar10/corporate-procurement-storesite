<div class="text-center py-3">
    <div class="mb-3">
        <i class="mdi mdi-close-octagon-outline text-danger" style="font-size:56px;"></i>
    </div>
    <h5 class="mb-2">Onboarding rejected</h5>
    <p class="text-muted mb-3">
        Your company onboarding was not approved.
    </p>

    @if($reason)
        <div class="alert alert-danger border-0">
            <strong>Reason:</strong> {{ $reason }}
        </div>
    @endif

    <div class="d-flex justify-content-center gap-2">
        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="resubmit">
            Resubmit now
        </button>
        {{-- <a class="btn btn-outline-secondary" href="{{ route('company.onboarding', ['resubmit' => 1]) }}">
            Resubmit via link
        </a> --}}
    </div>
</div>