<?php

namespace App\Services\Finance;

use App\Models\Company\Company;
use App\Models\Company\CompanyBankAccount;

class CompanyBankAccountService
{
    public function addOrUpdate(Company $company, array $data): CompanyBankAccount
    {
        $acc = CompanyBankAccount::updateOrCreate(
            [
                'company_id'    => $company->id,
                'account_number'=> $data['account_number'],
                'ifsc'          => $data['ifsc'] ?? null,
            ],
            [
                'bank_name'              => $data['bank_name'] ?? '',
                'branch'                 => $data['branch'] ?? null,
                'account_holder_name'    => $data['account_holder_name'] ?? null,
                'preferred_payment_method'=> $data['preferred_payment_method'] ?? null,
                'credit_term'            => $data['credit_term'] ?? null,
                'is_default'             => (bool) ($data['is_default'] ?? false),
            ]
        );

        if ($acc->is_default) {
            CompanyBankAccount::where('company_id', $company->id)
                ->where('id', '!=', $acc->id)
                ->update(['is_default' => false]);
        }

        return $acc;
    }
}
