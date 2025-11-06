<?php

namespace App\Livewire\Auth\Register\Steps;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Onboarding\CompanyOnboardingService;

class OtpVerify extends Component
{
    public int $companyId;
    public ?string $otpId = null;
    public string $code = '';

    protected function rules(): array
    {
        return ['code' => ['required','string','size:6']];
    }

    public function verify(CompanyOnboardingService $svc)
    {
        $this->validate();
        $company = Company::with('representative')->findOrFail($this->companyId);
        $svc->verifyOtpAndCreateFirstUser($company, $this->otpId, $this->code);
        $this->dispatch('reg.otp.verified');
        session()->flash('success', 'Account created successfully.');
        return redirect()->route('register', [
        'step'    => 7,
        'company' => $company->id,
        ])->with('success', 'Account created');
    }

    public function render()
    {
        return view('livewire.auth.register.steps.otp-verify');
    }
}
