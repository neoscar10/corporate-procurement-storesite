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
        $p = $this->for($company);
        return (bool) $p->completed_at;
    }

    public function currentStep(Company $company): int
    {
        $p = $this->for($company);
        if ($p->completed_at) return 4; // success
        if (! $p->procurement_done) return 1;
        if (! $p->kyc_done) return 2;
        if (! $p->billing_done) return 3;
        return 4;
    }

    public function resetForResubmission(Company $company): void
    {
        $p = $this->for($company);

        // If already “not completed”, avoid rewriting timestamps unnecessarily
        if (! $p->completed_at && ! $p->procurement_done && ! $p->kyc_done && ! $p->billing_done) {
            return;
        }

        $p->forceFill([
            'procurement_done' => false,
            'kyc_done'         => false,
            'billing_done'     => false,
            'procurement_done_at' => null,
            'kyc_done_at'         => null,
            'billing_done_at'     => null,
            'completed_at'        => null,
        ])->save();
    }

    private function maybeMarkCompleted(CompanyOnboardingProgress $p): void
    {
        if ($p->procurement_done && $p->kyc_done && $p->billing_done && ! $p->completed_at) {
            $p->forceFill(['completed_at' => now()])->save();
        }
    }
}
