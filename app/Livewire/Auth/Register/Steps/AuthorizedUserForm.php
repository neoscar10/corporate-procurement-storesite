<?php

namespace App\Livewire\Auth\Register\Steps;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Company\CompanyRepresentativeService;
use App\Services\Onboarding\CompanyOnboardingService;

class AuthorizedUserForm extends Component
{
    public int $companyId;

    public string $full_name = '';
    public ?string $designation = null;
    public string $email = '';
    public string $mobile = '';
    public ?string $govt_id_type = null;
    public ?string $govt_id_number = null;

    public string $channel = 'email'; // email|sms

    protected function rules(): array
    {
        return [
            'full_name'      => ['required','string','max:200'],
            'designation'    => ['nullable','string','max:100'],
            'email'          => ['required','email','max:255'],
            'mobile'         => ['required','string','max:32'],
            'govt_id_type'   => ['nullable','string','max:50'],
            'govt_id_number' => ['nullable','string','max:64'],
            'channel'        => ['required','in:email,sms'],
        ];
    }

    public function save(CompanyRepresentativeService $svc): void
    {
        $data = $this->validate([
            'full_name'      => $this->rules()['full_name'],
            'designation'    => $this->rules()['designation'],
            'email'          => $this->rules()['email'],
            'mobile'         => $this->rules()['mobile'],
            'govt_id_type'   => $this->rules()['govt_id_type'],
            'govt_id_number' => $this->rules()['govt_id_number'],
        ]);

        $company = Company::findOrFail($this->companyId);
        $svc->upsert($company, $data);

        $this->dispatch('reg.rep.saved');
        session()->flash('success', 'Authorized signatory saved.');
    }

    public function saveAndSendOtp(
        CompanyRepresentativeService $repSvc,
        CompanyOnboardingService $onSvc
    ) {
        $data = $this->validate();
        $company = Company::findOrFail($this->companyId);

        // 1) Upsert representative
        $repData = $data;
        unset($repData['channel']);
        $repSvc->upsert($company, $repData);

        // 2) Issue OTP
        $res = $onSvc->registerAuthorizedUserAndSendOtp($company, [], $this->channel);

        // Notify wizard and push to Verify step (3)
        $this->dispatch('reg.otp.issued', otpId: $res['otp_id'], channel: $this->channel);

        return redirect()->route('register', [
            'step'    => 3,
            'company' => $company->id,
            'otpId'   => $res['otp_id'],
            'channel' => $this->channel,
        ])->with('success', 'Verification code sent.');
    }

    public function render()
    {
        return view('livewire.auth.register.steps.authorized-user-form');
    }
}
