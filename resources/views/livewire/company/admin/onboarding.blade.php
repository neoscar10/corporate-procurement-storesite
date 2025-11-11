@php
$me = auth()->user();
$canResubmit = false;

if ($me) {
    // Prefer company membership for the current company
    $canResubmit = \App\Models\Company\CompanyMember::where('company_id', (int) $companyId)
        ->where('user_id', (int) $me->id)
        ->where('role_label', 'CompanyAdmin')
        ->where('is_active', true)
        ->exists();
}
@endphp


<div>
    <x-ui.page-header title="Company Onboarding" subtitle="Complete these steps to get approved">
        <x-slot:actions>
            <span
                class="badge bg-{{ $companyStatus === 'approved' ? 'success' : ($companyStatus === 'pending' ? 'warning' : 'secondary')  }}">
                {{ ucfirst($companyStatus) }}
            </span>
            <span class="badge bg-light text-dark">{{ $companyName }}</span>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Successs banner --}}
    <div x-data="{show:false, msg:'', type:'success'}" x-on:banner.window="
            msg = $event.detail.message ?? '';
            type = $event.detail.type ?? 'success';
            show = true;
            setTimeout(() => show = false, 10000);
         ">
        <div x-cloak x-show="show" class="alert alert-dismissible fade show"
            :class="type === 'success' ? 'alert-success' : 'alert-danger'" role="alert">
            <span x-text="msg"></span>
            <button type="button" class="btn-close" @click="show=false" aria-label="Close"></button>
        </div>
    </div>
    
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>



    @if($companyStatus === 'pending' && !$readOnlySuccess && $step !== 0)
        <div class="alert alert-warning mb-4">
            Your company account is yet to be approved, please complete these steps to get approved.
        </div>
    @endif

    <div class="card card-bg-fill">
        <div class="card-body">

            {{-- Rejected state (no stepper) --}}
            @if($step === 0 && $companyStatus === 'rejected')
                <livewire:company.admin.onboarding.rejected-card :company-id="$companyId" :key="'ob-rejected'" />
            @elseif($readOnlySuccess)
                {{-- Completed: show only success card --}}
                <livewire:company.admin.onboarding.success-card :company-id="$companyId" :key="'ob-step-4'" />

                @if ($canResubmit)
                    <div class="text-center mt-2">
                        <a href="{{ route('company.onboarding', ['resubmit' => 1]) }}" class="text-muted small text-decoration-underline">
                            Resubmit documents
                        </a>
                    </div>
                @endif


            @else
                    {{-- Normal wizard --}}
                    <div>
                        <x-ui.stepper :steps="$steps" :current="$step" />
                    </div>

                <div class="mt-3">
                    @if((int) $step === 1)
                        {{-- NEW: Step 1 -> Addresses --}}
                        <livewire:company.admin.onboarding.addresses-form :company-id="$companyId" :key="'ob-step-1'" />

                    @elseif((int) $step === 2)
                        {{-- NEW: Step 2 -> Contact --}}
                        <livewire:company.admin.onboarding.contact-form :company-id="$companyId" :key="'ob-step-2'" />

                    @elseif((int) $step === 3)
                        <livewire:company.admin.onboarding.procurement-form :company-id="$companyId" :key="'ob-step-3'" />

                    @elseif((int) $step === 4)
                        <livewire:company.admin.onboarding.kyc-form :company-id="$companyId" :key="'ob-step-4'" />

                    @elseif((int) $step === 5)
                        <livewire:company.admin.onboarding.billing-form :company-id="$companyId" :key="'ob-step-5'" />

                    @elseif((int) $step === 6)
                        <livewire:company.admin.onboarding.success-card :company-id="$companyId" :key="'ob-step-6'" />
                        @if ($canResubmit)
                            <div class="text-center mt-2">
                                <a href="{{ route('company.onboarding', ['resubmit' => 1]) }}"
                                    class="text-muted small text-decoration-underline">
                                    Resubmit documents
                                </a>
                            </div>
                        @endif
                    @endif
                </div>

            @endif
                {{-- Invite user modal --}}
            <livewire:auth.invite.invite-user-modal :company-id="$companyId" :key="'invite-modal-root'" />

        </div>
    </div>
</div>

@push('styles')
    <style>
        .wizard-stepper .wizard-track {
            justify-content: center !important;
        }
    </style>
@endpush