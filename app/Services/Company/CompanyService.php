<?php

namespace App\Services\Company;

use App\Models\Company\Company;
use App\Services\Verification\GovIdVerificationService;

class CompanyService
{
    public function __construct(protected GovIdVerificationService $gov) {}

    public function upsert(array $data): Company
    {
        // Optional basic normalize
        $attrs = [
            'legal_name'         => $data['legal_name'] ?? '',
            'brand_name'         => $data['brand_name'] ?? null,
            'cin'                => $data['cin'] ?? null,
            'pan'                => $data['pan'] ?? null,
            'gstin'              => $data['gstin'] ?? null,
            'organization_type'  => $data['organization_type'] ?? null,
            'industry'           => $data['industry'] ?? null,
            'nature_of_business' => $data['nature_of_business'] ?? null,
        ];

        // No external checks yet; locally mark as "verified" pass-through if present
        $ver = $this->gov->verify($attrs['cin'], $attrs['pan'], $attrs['gstin']);
        $attrs['cin_verified_at']  = $ver['cin_verified_at'];
        $attrs['pan_verified_at']  = $ver['pan_verified_at'];
        $attrs['gstin_verified_at']= $ver['gstin_verified_at'];

        $company = Company::updateOrCreate(
            ['cin' => $attrs['cin']], // pivot key for idempotency if CIN is provided
            $attrs + ['status' => $data['status'] ?? 'pending']
        );

        return $company->fresh();
    }
}
