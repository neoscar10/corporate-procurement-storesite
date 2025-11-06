<?php

namespace App\Services\Company;

use App\Models\Company\Company;
use App\Models\Company\CompanyRepresentative;

class CompanyRepresentativeService
{
    public function upsert(Company $company, array $data): CompanyRepresentative
    {
        return CompanyRepresentative::updateOrCreate(
            ['company_id' => $company->id],
            [
                'full_name'      => $data['full_name'] ?? '',
                'designation'    => $data['designation'] ?? null,
                'email'          => $data['email'] ?? null,
                'mobile'         => $data['mobile'] ?? null,
                'govt_id_type'   => $data['govt_id_type'] ?? null,
                'govt_id_number' => $data['govt_id_number'] ?? null,
                'signature_path' => $data['signature_path'] ?? null,
                'user_id'        => $data['user_id'] ?? null,
            ]
        );
    }
}
