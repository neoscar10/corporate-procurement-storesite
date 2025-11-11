<?php

namespace App\Livewire\Auth\Register;

use Livewire\Component;
use Livewire\Attributes\On;

class WizardPage extends Component
{
    // 1..4 (4 = success). Only 1..3 are visible.
    public int $step = 1;

    public ?int $companyId = null; // set after basic info
    public ?string $otpId = null;  // set after issuing OTP
    public ?string $channel = null;

    public function mount(): void
    {
        $this->step      = max(1, min(4, (int) request()->query('step', $this->step)));
        $this->companyId = request()->integer('company', $this->companyId);
        $this->otpId     = request()->query('otpId', $this->otpId);
        $this->channel   = request()->query('channel', $this->channel);
    }

    #[On('reg-basic-saved')]
    public function onBasicSaved($companyId): void
    {
        $this->companyId = (int) $companyId;
        $this->step = 2; // Authorized Signatory
    }

    #[On('reg.rep.saved')]
    public function onRepSaved(): void
    {
        // remain on step 2 — (save without sending OTP)
        $this->step = 2;
    }

    #[On('reg.otp.issued')]
    public function onOtpIssued(string $otpId, string $channel): void
    {
        $this->otpId = $otpId;
        $this->channel = $channel;
        $this->step = 3; // Verify OTP
    }

    #[On('reg.otp.verified')]
    public function onOtpVerified(): void
    {
        $this->step = 4; // Success (hidden in stepper)
    }

    public function back(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function steps(): array
    {
        $labels = ['Basic Info', 'Authorized Signatory', 'Verify OTP'];
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
        return view('livewire.auth.register.wizard-page', [
            'steps' => $this->steps(),
        ])->layout('layouts.auth', [
            'authTitle'    => 'Create your company account',
            'authSubtitle' => 'Let’s get your organization onboarded',
            'title'        => 'Register • ' . config('app.name'),
        ]);
    }
}
