<div class="onboarding-nag-container" wire:key="onboarding-nag-bar">
    @if($show)
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">

            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong> <i class="mdi mdi-alert fs-4 me-2"></i> Finish your onboarding</strong>
                        @if(!empty($missing))
                            — <span class="text-muted">Pending: {{ implode(', ', $missing) }}</span>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('company.onboarding') }}"
                            class="text-decoration-underline">
                            Continue Onboarding
                        </a>
                    </div>
                </div>

                <div class="progress mt-2" style="height:6px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;"
                        aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
   
</div>