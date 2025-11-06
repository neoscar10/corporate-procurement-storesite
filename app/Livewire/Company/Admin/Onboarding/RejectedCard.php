<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;

class RejectedCard extends Component
{
    public int $companyId;
    public ?string $reason = null; 
    public function resubmit(): void
    {
        // Ask the parent to do the reset + redirect (no browser-events)
        $this->dispatch('onboard.resubmit.requested');
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.rejected-card');
    }
}
