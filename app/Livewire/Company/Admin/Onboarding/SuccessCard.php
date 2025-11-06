<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;

class SuccessCard extends Component
{
    public int $companyId;

    public function render()
    {
        return view('livewire.company.admin.onboarding.success-card');
    }
}
