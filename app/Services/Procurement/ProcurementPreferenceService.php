<?php

namespace App\Services\Procurement;

use App\Models\Company\Company;
use App\Models\Company\ProcurementPreference;

class ProcurementPreferenceService
{
    public function upsert(Company $company, array $data): ProcurementPreference
    {
        return ProcurementPreference::updateOrCreate(
            ['company_id' => $company->id],
            [
                'avg_monthly_budget'      => $data['avg_monthly_budget'] ?? null,
                'procurement_type'        => $data['procurement_type'] ?? 'both',
                'frequency'               => $data['frequency'] ?? null,
                'preferred_payment_terms' => $data['preferred_payment_terms'] ?? null,
                'preferred_vendor_locations' => $data['preferred_vendor_locations'] ?? [],
            ]
        );
    }
}
