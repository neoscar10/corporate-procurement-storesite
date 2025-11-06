<?php

namespace App\Livewire\Auth\Register\Steps;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Onboarding\CompanyOnboardingService;

class ContactForm extends Component
{
    public int $companyId;

    public ?string $official_email = null;
    public ?string $alternate_email = null;
    public ?string $primary_phone = null;
    public ?string $contact_mobile = null;
    public ?string $website = null;

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

    public function save(CompanyOnboardingService $svc)
    {
        $data = $this->validate();
        $company = Company::findOrFail($this->companyId);
        $svc->upsertContact($company, $data);
        $this->dispatch('reg.contact.saved');
        session()->flash('success', 'Contact saved.');
        return redirect()
        ->route('register', ['step' => 4, 'company' => $company->id])
        ->with('success', 'Basic info saved.');
    }

    public function render()
    {
        return view('livewire.auth.register.steps.contact-form');
    }
}
