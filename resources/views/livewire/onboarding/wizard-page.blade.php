<div>
    <x-ui.page-header :title="'Company Onboarding'" :subtitle="'Finish these steps to unlock all features.'">
        <x-slot:actions>
            <a href="" class="btn btn-light btn-sm">
                <i class="mdi mdi-view-dashboard"></i> Dashboard
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.stepper :steps="$steps" :current="$step" class="mb-4" />
    
    <div>
        @if($step === 1)
            <livewire:onboarding.steps.addresses-form :company-id="$companyId" :key="'onbd-step-1'" />
        @elseif($step === 2)
            <livewire:onboarding.steps.contact-form :company-id="$companyId" :key="'onbd-step-2'" />
        @else
            <div class="text-center py-5">
                <i class="mdi mdi-check-circle-outline text-success" style="font-size:48px;"></i>
                <h5 class="mt-3">Onboarding updated</h5>
                <p class="text-muted">Thanks! You can continue exploring the platform.</p>
                <a href="" class="btn btn-primary waves-effect waves-light">
                    <i class="mdi mdi-view-dashboard"></i> Go to Dashboard
                </a>
            </div>
        @endif
    </div>
</div>