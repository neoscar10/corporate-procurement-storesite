<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;
use App\Models\Company\Company;
use Livewire\Attributes\On; // Livewire v3 attribute (optional)

class RejectedCard extends Component
{
    public int $companyId;
    public ?string $reason = null;

    public function mount(int $companyId): void
    {
        $this->companyId = $companyId;
        $this->reason = Company::query()->whereKey($companyId)->value('status_reason');
    }

    // If something else updates the status/reason and broadcasts an event, re-pull:
    #[On('company-status-updated')]
    public function refreshReason(): void
    {
        $this->reason = Company::query()->whereKey($this->companyId)->value('status_reason');
    }

    public function resubmit(): void
    {
        $this->dispatch('onboard.resubmit.requested'); // parent handles reset/redirect
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.rejected-card');
    }
}
