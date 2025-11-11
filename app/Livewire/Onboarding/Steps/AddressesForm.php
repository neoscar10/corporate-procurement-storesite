<?php

namespace App\Livewire\Onboarding\Steps;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Onboarding\CompanyOnboardingService;
use App\Services\Onboarding\OnboardingProgressService;

class AddressesForm extends Component
{
    public int $companyId;

    public array $registered = ['line1'=>'','line2'=>null,'city'=>null,'state'=>null,'pin_code'=>null,'country'=>'India'];
    public array $corporate  = ['line1'=>'','line2'=>null,'city'=>null,'state'=>null,'pin_code'=>null,'country'=>'India'];
    public array $billing    = ['line1'=>'','line2'=>null,'city'=>null,'state'=>null,'pin_code'=>null,'country'=>'India'];

    protected function rules(): array
    {
        $rules = [];
        foreach (['registered','corporate','billing'] as $t) {
            $rules["{$t}.line1"]    = ['required','string','max:255'];
            $rules["{$t}.line2"]    = ['nullable','string','max:255'];
            $rules["{$t}.city"]     = ['nullable','string','max:100'];
            $rules["{$t}.state"]    = ['nullable','string','max:100'];
            $rules["{$t}.pin_code"] = ['nullable','string','max:12'];
            $rules["{$t}.country"]  = ['nullable','string','max:64'];
        }
        return $rules;
    }

    public function save(CompanyOnboardingService $svc, OnboardingProgressService $progress): void
    {
        $this->validate();
        $company = Company::findOrFail($this->companyId);

        $svc->upsertAddresses($company, [
            'registered' => $this->registered,
            'corporate'  => $this->corporate,
            'billing'    => $this->billing,
        ]);

        $progress->markAddressesDone($company);

        $this->dispatch('onboarding.addresses.saved');
        session()->flash('success', 'Addresses saved.');
        redirect()->route('company.onboarding', ['step' => 2]);
    }

    public function render()
    {
        return view('livewire.onboarding.steps.addresses-form');
    }
}
