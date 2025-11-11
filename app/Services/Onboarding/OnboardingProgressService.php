<?php

namespace App\Services\Onboarding;

use App\Models\Company\Company;
use App\Models\Company\CompanyOnboardingProgress;

class OnboardingProgressService
{
    public function for(Company $company): CompanyOnboardingProgress
    {
        return CompanyOnboardingProgress::firstOrCreate(['company_id' => $company->id]);
    }

    public function markAddressesDone(Company $company): void
    {
        $p = $this->for($company);
        $p->forceFill(['addresses_done' => true, 'addresses_done_at' => now()])->save();
        $this->maybeMarkCompleted($p);
    }

    public function markContactDone(Company $company): void
    {
        $p = $this->for($company);
        $p->forceFill(['contact_done' => true, 'contact_done_at' => now()])->save();
        $this->maybeMarkCompleted($p);
    }

    public function markProcurementDone(Company $company): void
    {
        $p = $this->for($company);
        $p->forceFill(['procurement_done' => true, 'procurement_done_at' => now()])->save();
        $this->maybeMarkCompleted($p);
    }

    public function markKycDone(Company $company): void
    {
        $p = $this->for($company);
        $p->forceFill(['kyc_done' => true, 'kyc_done_at' => now()])->save();
        $this->maybeMarkCompleted($p);
    }

    public function markBillingDone(Company $company): void
    {
        $p = $this->for($company);
        $p->forceFill(['billing_done' => true, 'billing_done_at' => now()])->save();
        $this->maybeMarkCompleted($p);
    }

    public function isComplete(Company $company): bool
    {
        return (bool) $this->for($company)->completed_at;
    }

    public function currentStep(Company $company): int
    {
        $p = $this->for($company);
        if ($p->completed_at) return 6; // success
        if (! $p->addresses_done)  return 1;
        if (! $p->contact_done)    return 2;
        if (! $p->procurement_done) return 3;
        if (! $p->kyc_done)         return 4;
        if (! $p->billing_done)     return 5;
        return 6;
    }

    public function resetForResubmission(Company $company): void
    {
        $p = $this->for($company);

        if (! $p->completed_at && ! $p->addresses_done && ! $p->contact_done &&
            ! $p->procurement_done && ! $p->kyc_done && ! $p->billing_done) {
            return;
        }

        $p->forceFill([
            'addresses_done'      => false,
            'contact_done'        => false,
            'procurement_done'    => false,
            'kyc_done'            => false,
            'billing_done'        => false,
            'addresses_done_at'   => null,
            'contact_done_at'     => null,
            'procurement_done_at' => null,
            'kyc_done_at'         => null,
            'billing_done_at'     => null,
            'completed_at'        => null,
        ])->save();
    }

    private function maybeMarkCompleted(CompanyOnboardingProgress $p): void
    {
        if ($p->addresses_done && $p->contact_done && $p->procurement_done
            && $p->kyc_done && $p->billing_done && ! $p->completed_at) {
            $p->forceFill(['completed_at' => now()])->save();
        }
    }
}
