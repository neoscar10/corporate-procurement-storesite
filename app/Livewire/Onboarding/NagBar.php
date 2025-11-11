<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\Company;

class NagBar extends Component
{
    /** Optionally passed from layout; we’ll also try to infer. */
    public ?int $companyId = null;

    public function mount(?int $companyId = null): void
    {
        // 1) Explicit prop from layout wins
        if ($companyId) {
            $this->companyId = $companyId;
            return;
        }

        // 2) Try common places on the user model
        $user = Auth::user();
        if (! $user) return;

        // a) plain column
        if (! $this->companyId && isset($user->company_id) && $user->company_id) {
            $this->companyId = (int) $user->company_id;
        }

        // b) belongsTo relation: $user->company
        if (! $this->companyId && method_exists($user, 'company')) {
            $rel = $user->company()->first();
            if ($rel) $this->companyId = (int) $rel->id;
        }

        // c) membership table fallback (if your app uses it)
        if (! $this->companyId && class_exists(\App\Models\Company\CompanyMember::class)) {
            $member = \App\Models\Company\CompanyMember::where('user_id', $user->id)->first();
            if ($member) $this->companyId = (int) $member->company_id;
        }
    }

    public function render(\App\Services\Onboarding\OnboardingProgressService $progress)
    {
        $show = false;
        $missing = [];
        $percent = 0;

        if ($this->companyId) {
            $company = Company::find($this->companyId);

            if ($company) {
                $p = $progress->for($company); // firstOrCreate row if missing

                // Compute completion state (anything not true counts as pending)
                $states = [
                    'Addresses'               => (bool) $p->addresses_done,
                    'Company Contact'         => (bool) $p->contact_done,
                    'Procurement Preferences' => (bool) $p->procurement_done,
                    'KYC'                     => (bool) $p->kyc_done,
                    'Billing'                 => (bool) $p->billing_done,
                ];

                $done    = collect($states)->filter()->count();
                $missing = collect($states)->reject()->keys()->all();
                $percent = (int) round(($done / 5) * 100);
                $show    = $done < 5;
            }
        }

        // View-only locals
        return view('livewire.onboarding.nag-bar', compact('show', 'missing', 'percent'));
    }
}
