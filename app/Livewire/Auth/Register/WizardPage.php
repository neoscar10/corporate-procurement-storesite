<?php

namespace App\Livewire\Auth\Register;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class WizardPage extends Component
{
    // Now 1..6 (6 = success). Only 1..5 are visible in the stepper.
    public int $step = 1;
    public ?int $companyId = null; // set after basic info
    public ?string $otpId = null;  // set after issuing OTP
    public ?string $channel = null;

    public function mount(): void
    {
        $this->step      = max(1, min(6, (int) request()->query('step', $this->step)));
        $this->companyId = request()->integer('company', $this->companyId);
        $this->otpId     = request()->query('otpId', $this->otpId);
        $this->channel   = request()->query('channel', $this->channel);
    }

    #[On('debug-ping')]
    public function onDebugPing($ts): void
    {
        Log::info('LW DEBUG PING', ['ts' => $ts]);
        $this->step = 2;
        $this->companyId = 1;
    }

    #[On('reg-basic-saved')]
    public function onBasicSaved($companyId): void
    {
        $this->companyId = (int) $companyId;
        $this->step = 2;
    }

    #[On('reg.addresses.saved')]
    public function onAddressesSaved(): void
    {
        $this->step = 3;
    }

    #[On('reg.contact.saved')]
    public function onContactSaved(): void
    {
        $this->step = 4; // goes to merged step: Authorized User & Verify
    }

    #[On('reg.rep.saved')]
    public function onRepSaved(): void
    {
        // Stay on step 4 (same merged step). No separate page for verification.
        $this->step = 4;
    }

    #[On('reg.otp.issued')]
    public function onOtpIssued(string $otpId, string $channel): void
    {
        $this->otpId = $otpId;
        $this->channel = $channel;
        $this->step = 5; // Verify OTP
    }

    #[On('reg.otp.verified')]
    public function onOtpVerified(): void
    {
        $this->step = 6; // Success
    }

    public function back(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function steps(): array
    {
        // Only 5 visible steps (success hidden from the counter)
        $labels = [
            'Basic Info',
            'Addresses',
            'Company Contact',
            'Authorized User',
            'Verify OTP',
        ];

        // Map current logical step (1..6) to display step (1..5)
        $displayStep = min($this->step, 5);

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
            'authTitle' => 'Create your company account',
            'authSubtitle' => 'Let’s get your organization onboarded',
            'title' => 'Register • ' . config('app.name'),
        ]);
    }
}
