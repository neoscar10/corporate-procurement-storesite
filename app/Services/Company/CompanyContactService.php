<?php

namespace App\Services\Company;

use App\Models\Company\Company;
use App\Models\Company\CompanyContact;

class CompanyContactService
{
    public function upsert(Company $company, array $data): CompanyContact
    {
        return CompanyContact::updateOrCreate(
            ['company_id' => $company->id],
            [
                'official_email'  => $data['official_email'] ?? null,
                'alternate_email' => $data['alternate_email'] ?? null,
                'primary_phone'   => $data['primary_phone'] ?? null,
                'contact_mobile'  => $data['contact_mobile'] ?? null,
                'website'         => $data['website'] ?? null,
            ]
        );
    }
}
