<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class WizardPage extends Component
{
    // 1..3 (3 = success)
    public int $step = 1;
    public int $companyId;

    public function mount(): void
    {
        $this->step = max(1, min(3, (int) request()->query('step', 1)));
        $this->companyId = (int) optional(Auth::user())->company_id;
    }

    #[On('onboarding.addresses.saved')]
    public function onAddressesSaved(): void
    {
        $this->step = 2;
    }

    #[On('onboarding.contact.saved')]
    public function onContactSaved(): void
    {
        $this->step = 3;
    }

    public function steps(): array
    {
        $labels = ['Addresses', 'Company Contact', 'Done'];
        $displayStep = min($this->step, 3);

        return collect($labels)->map(function ($label, $i) use ($displayStep) {
            $idx = $i + 1;
            return [
                'label' => $label,
                'state' => $displayStep > $idx ? 'done' : ($displayStep === $idx ? 'current' : 'todo'),
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.onboarding.wizard-page', [
            'steps' => $this->steps(),
            'companyId' => $this->companyId,
        ])->layout('layouts.admin', [
            'title' => 'Complete Onboarding',
        ]);
    }
}
