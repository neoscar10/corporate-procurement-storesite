<?php

namespace App\Livewire\Auth\Register\Steps;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Onboarding\CompanyOnboardingService;


class VerificationMethod extends Component
{
    public int $companyId;
    public string $channel = 'email'; // email|sms

    public function issue(CompanyOnboardingService $svc)
    {
        $company = Company::with('representative')->findOrFail($this->companyId);
        $res = $svc->registerAuthorizedUserAndSendOtp($company, [
            // rep already saved; nothing extra needed here
        ], $this->channel);

        $this->dispatch('reg.otp.issued', otpId: $res['otp_id'], channel: $this->channel);
        // session()->flash('success', 'Verification code sent.');
        return redirect()->route('register', [
        'step'    => 6,
        'company' => $company->id,
        'otpId'   => $res['otp_id'],
        'channel' => $this->channel,
        ])->with('success', 'Verification code sent.');
    }

    public function render()
    {
        return view('livewire.auth.register.steps.verification-method');
    }
}
