<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Onboarding\CompanyOnboardingService;
use App\Services\Onboarding\OnboardingProgressService;

class ContactForm extends Component
{
    public int $companyId;

    public ?string $official_email = null;
    public ?string $alternate_email = null;
    public ?string $primary_phone  = null;
    public ?string $contact_mobile = null;
    public ?string $website        = null;

    public function mount(): void
    {
        $company = Company::with('contact')->findOrFail($this->companyId);
        if ($company->contact) {
            $c = $company->contact;
            $this->official_email  = $c->official_email;
            $this->alternate_email = $c->alternate_email;
            $this->primary_phone   = $c->primary_phone;
            $this->contact_mobile  = $c->contact_mobile;
            $this->website         = $c->website;
        }
    }

    protected function rules(): array
    {
        return [
            'official_email'  => ['nullable','email','max:255'],
            'alternate_email' => ['nullable','email','max:255'],
            'primary_phone'   => ['nullable','string','max:32'],
            'contact_mobile'  => ['nullable','string','max:32'],
            'website'         => ['nullable','string','max:255'],
        ];
    }

    public function save(CompanyOnboardingService $svc, OnboardingProgressService $progress)
    {
        $data = $this->validate();
        $company = Company::findOrFail($this->companyId);

        $svc->upsertContact($company, $data);
        $progress->markContactDone($company);

        return redirect()
            ->route('company.onboarding', ['step' => 3])
            ->with('success', 'Company contact saved.');
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.contact-form');
    }
}
