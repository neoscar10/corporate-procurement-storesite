<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Procurement\ProcurementPreferenceService;
use App\Services\Onboarding\OnboardingProgressService;

class ProcurementForm extends Component
{
    public int $companyId;
    public ?string $avg_monthly_budget = null;
    public string $procurement_type = 'both'; // goods|services|both
    public ?string $frequency = null;         // monthly|quarterly|ad-hoc
    public ?string $preferred_payment_terms = null;
    public array $preferred_vendor_locations = [];

    protected function rules(): array
    {
        return [
            'avg_monthly_budget' => ['nullable','string','max:50'],
            'procurement_type'   => ['required','in:goods,services,both'],
            'frequency'          => ['nullable','in:monthly,quarterly,ad-hoc'],
            'preferred_payment_terms' => ['nullable','string','max:100'],
            'preferred_vendor_locations' => ['array'],
        ];
    }

    // NEW: prefill from existing preference so resubmits show prior values
    public function mount(): void
    {
        $company = Company::with('preference')->findOrFail($this->companyId);
        if ($company->preference) {
            $p = $company->preference;
            $this->avg_monthly_budget      = $p->avg_monthly_budget;
            $this->procurement_type        = $p->procurement_type ?: 'both';
            $this->frequency               = $p->frequency;
            $this->preferred_payment_terms = $p->preferred_payment_terms;
            $this->preferred_vendor_locations = (array) ($p->preferred_vendor_locations ?? []);
        }
    }

    public function save(ProcurementPreferenceService $svc, OnboardingProgressService $progress)
    {
        $data = $this->validate();
        $company = Company::findOrFail($this->companyId);

        $svc->upsert($company, $data);
        $progress->markProcurementDone($company);

        return redirect()
            ->route('company.onboarding', ['step' => 2])
            ->with('success', 'Procurement preferences saved.');
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.procurement-form');
    }
}
