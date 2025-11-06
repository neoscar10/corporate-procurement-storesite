<?php

namespace App\Services\Company;

use App\Models\Company\Company;
use App\Models\Company\CompanyAddress;

class CompanyAddressService
{
    // $typed = ['registered' => [...], 'corporate' => [...], 'billing' => [...]]
    public function syncTyped(Company $company, array $typed): void
    {
        foreach (['registered','corporate','billing'] as $type) {
            if (!isset($typed[$type])) continue;

            $payload = $typed[$type];
            CompanyAddress::updateOrCreate(
                ['company_id' => $company->id, 'type' => $type],
                [
                    'line1'    => $payload['line1'] ?? '',
                    'line2'    => $payload['line2'] ?? null,
                    'city'     => $payload['city'] ?? null,
                    'state'    => $payload['state'] ?? null,
                    'pin_code' => $payload['pin_code'] ?? null,
                    'country'  => $payload['country'] ?? 'India',
                ]
            );
        }
    }
}
