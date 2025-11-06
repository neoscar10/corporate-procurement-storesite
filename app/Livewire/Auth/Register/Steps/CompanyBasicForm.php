<?php

namespace App\Livewire\Auth\Register\Steps;

use App\Livewire\Auth\Register\WizardPage;

use Livewire\Component;
use App\Services\Onboarding\CompanyOnboardingService;

class CompanyBasicForm extends Component
{
    public string $legal_name = '';
    public ?string $brand_name = null;
    public ?string $cin = null;
    public ?string $pan = null;
    public ?string $gstin = null;
    public ?string $organization_type = null;
    public ?string $industry = null;
    public ?string $nature_of_business = null;

    protected function rules(): array
    {
        return [
            'legal_name' => ['required','string','max:255'],
            'brand_name' => ['nullable','string','max:255'],
            'cin' => ['nullable','string','max:21'],
            'pan' => ['nullable','string','max:10'],
            'gstin' => ['nullable','string','max:15'],
            'organization_type' => ['nullable','string','max:100'],
            'industry' => ['nullable','string','max:100'],
            'nature_of_business' => ['nullable','string','max:255'],
        ];
    }

    public function testPing(): void
    {
        $this->dispatch('debug-ping', now()->toDateTimeString())->to(WizardPage::class);
    }

    public function save(CompanyOnboardingService $svc)
    {
        $data = $this->validate();
        $company = $svc->upsertBasicInfo($data);

        $this->dispatch('reg-basic-saved', $company->id)->to(WizardPage::class);

        return redirect()
        ->route('register', ['step' => 2, 'company' => $company->id])
        ->with('success', value: 'Basic info saved.');

        // session()->flash('success', 'Basic info saved.');

        
    }

    public function render()
    {
        return view('livewire.auth.register.steps.company-basic-form');
    }
}


// CompanyBasicForm.php

