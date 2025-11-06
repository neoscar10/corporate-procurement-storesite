<?php

namespace App\Livewire\Company\Admin\Onboarding;

use Livewire\Component;
use App\Models\Company\Company;
use App\Models\Company\CompanyBankAccount;
use App\Services\Onboarding\OnboardingProgressService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BillingForm extends Component
{
    public int $companyId;

    public ?int $bank_account_id = null; // the default/current record (if any)
    public string $bank_name = '';
    public string $account_number = '';
    public string $ifsc = '';
    public ?string $branch = null;
    public bool $is_default = true;

    protected function rules(): array
    {
        return [
            'bank_name'      => ['required','string','max:100'],
            'account_number' => ['required','string','max:50'],
            'ifsc'           => ['required','string','max:20'],
            'branch'         => ['nullable','string','max:100'],
            'is_default'     => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $company = Company::findOrFail($this->companyId);

        $acct = CompanyBankAccount::where('company_id', $company->id)
            ->where('is_default', true)
            ->first();

        if (! $acct) {
            $acct = CompanyBankAccount::where('company_id', $company->id)->first();
        }

        if ($acct) {
            $this->bank_account_id = $acct->id;
            $this->bank_name       = (string) $acct->bank_name;
            $this->account_number  = (string) $acct->account_number;
            $this->ifsc            = (string) $acct->ifsc;
            $this->branch          = $acct->branch;
            $this->is_default      = (bool) $acct->is_default;
        } else {
            $this->is_default = true; // first one becomes default
        }
    }

    public function save(OnboardingProgressService $progress)
    {
        $data = $this->validate();
        $company = Company::findOrFail($this->companyId);

        DB::transaction(function () use ($company, $data) {
            // Upsert: update existing default/current or create new
            if ($this->bank_account_id) {
                $acct = CompanyBankAccount::where('company_id', $company->id)
                    ->where('id', $this->bank_account_id)
                    ->first();

                if ($acct) {
                    $acct->forceFill([
                        'bank_name'      => $data['bank_name'],
                        'account_number' => $data['account_number'],
                        'ifsc'           => $data['ifsc'],
                        'branch'         => $data['branch'],
                        'is_default'     => (bool) $data['is_default'],
                    ])->save();
                } else {
                    $acct = CompanyBankAccount::create([
                        'company_id'     => $company->id,
                        'bank_name'      => $data['bank_name'],
                        'account_number' => $data['account_number'],
                        'ifsc'           => $data['ifsc'],
                        'branch'         => $data['branch'],
                        'is_default'     => (bool) $data['is_default'],
                    ]);
                    $this->bank_account_id = $acct->id;
                }
            } else {
                $acct = CompanyBankAccount::create([
                    'company_id'     => $company->id,
                    'bank_name'      => $data['bank_name'],
                    'account_number' => $data['account_number'],
                    'ifsc'           => $data['ifsc'],
                    'branch'         => $data['branch'],
                    'is_default'     => (bool) $data['is_default'],
                ]);
                $this->bank_account_id = $acct->id;
            }

            // Ensure single default
            if ($data['is_default']) {
                CompanyBankAccount::where('company_id', $company->id)
                    ->where('id', '!=', $this->bank_account_id)
                    ->update(['is_default' => false]);
            }
        });

        $progress->markBillingDone($company);

        return redirect()->route('company.onboarding', ['step' => 4])
            ->with('success', 'Billing details saved.');
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding.billing-form');
    }
}
