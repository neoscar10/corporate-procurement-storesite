<?php

namespace App\Livewire\Auth\Register\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
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

        // Create first user (and email credentials) but DO NOT log them in
        $svc->verifyOtpAndCreateFirstUser($company, $this->otpId ?? '', $this->code);

        // Tell the wizard to move to the success screen
        $this->dispatch('reg.otp.verified');

        // Land on the success card (step 4 in the 3-step + success flow)
        return redirect()->route('register', [
            'step'    => 4,              // <-- important: success card
            'company' => $company->id,
        ])->with('success', 'Account created. Your login details have been emailed.');
    }

    public function render()
    {
        return view('livewire.auth.register.steps.otp-verify');
    }
}
