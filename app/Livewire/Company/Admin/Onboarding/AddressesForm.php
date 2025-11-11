<?php

namespace App\Livewire\Company\Admin\Onboarding;

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

    public function mount(): void
    {
        $company = Company::with('addresses')->findOrFail($this->companyId);
        $byType = $company->addresses?->keyBy('type') ?? collect();

        foreach (['registered','corporate','billing'] as $type) {
            if ($row = $byType->get($type)) {
                $this->{$type} = [
                    'line1'    => $row->line1 ?? '',
                    'line2'    => $row->line2,
                    'city'     => $row->city,
                    'state'    => $row->state,
                    'pin_code' => $row->pin_code,
                    'country'  => $row->country ?? 'India',
                ];
            }
        }
    }

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

    public function save(CompanyOnboardingService $svc, OnboardingProgressService $progress)
    {
        $this->validate();
        $company = Company::findOrFail($this->companyId);

        $svc->upsertAddresses($company, [
            'registered' => $this->registered,
            'corporate'  => $this->corporate,
            'billing'    => $this->billing,
        ]);

        $progress->markAddressesDone($company);

        return redirect()
            ->route('company.onboarding', ['step' => 2])
            ->with('success', 'Addresses saved.');
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.addresses-form');
    }
}
