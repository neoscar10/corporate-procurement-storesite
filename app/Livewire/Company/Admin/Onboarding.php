<?php

namespace App\Livewire\Company\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
use App\Models\Company\Company;
use App\Services\Onboarding\OnboardingProgressService;

class Onboarding extends Component
{
    public int $step = 1;
    public int $companyId;
    public string $companyName = '';
    public string $companyStatus = 'pending';
    public bool $readOnlySuccess = false;

    public function mount(OnboardingProgressService $progress): void
    {
        $membership = CompanyMember::with('company')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->latest('id')
            ->firstOrFail();

        $company = $membership->company;

        $this->companyId = $company->id;
        $this->companyName = $company->legal_name ?: ($company->brand_name ?: 'Company');
        $this->companyStatus = (string) $company->status;

        if (request()->boolean('resubmit', false)) {
            $progress->resetForResubmission($company);
            if ($company->status !== 'pending') {
                $company->forceFill(['status' => 'pending'])->save();
            }
            $this->companyStatus = 'pending';
            $this->readOnlySuccess = false;
            $this->step = 1;
            session()->flash('success', 'Please review your details and resubmit.');
            return;
        }

        if ($this->companyStatus === 'approved') {
            redirect()->route('company.admin.dashboard');
            return;
        }

        if (method_exists($progress, 'reconcile')) {
            $progress->reconcile($company);
        }

        $forceEdit = request()->boolean('edit', false);
        $queryStep = (int) request()->query('step', 1);

        if ($this->companyStatus === 'rejected' && ! $forceEdit) {
            $this->readOnlySuccess = false;
            $this->step = 0; // rejected card
            return;
        }

        $completed = $progress->isComplete($company);
        if ($completed && ! $forceEdit) {
            $this->readOnlySuccess = true;
            $this->step = 6; // now 6 = success
            return;
        }

        if ($forceEdit) {
            $this->readOnlySuccess = false;
            $this->step = max(1, min(5, $queryStep)); // editing shows 1..5
            return;
        }

        // Persisted progress (1..6) -> map to wizard steps (1..6)
        $persistedStep = $progress->currentStep($company);
        $this->step = max($this->mapPersistedToWizardStep($persistedStep), $queryStep);
    }

    private function mapPersistedToWizardStep(int $persisted): int
    {
        // Progress order expected:
        // 1: addresses, 2: contact, 3: procurement, 4: kyc, 5: billing, 6: done
        return match (true) {
            $persisted <= 1 => 1, // Addresses
            $persisted === 2 => 2, // Contact
            $persisted === 3 => 3, // Procurement
            $persisted === 4 => 4, // KYC
            $persisted === 5 => 5, // Billing
            default => 6,          // Done
        };
    }

    #[On('onboard.resubmit.requested')]
    public function startResubmission(OnboardingProgressService $progress): mixed
    {
        $company = Company::findOrFail($this->companyId);
        $progress->resetForResubmission($company);
        if ($company->status !== 'pending') {
            $company->forceFill(['status' => 'pending'])->save();
        }

        return redirect()
            ->route('company.onboarding', ['edit' => 1, 'step' => 1])
            ->with('success', 'Please review your details and resubmit.');
    }

    public function steps(): array
    {
        $labels = [
            'Company Addresses',        // 1
            'Company Contact',          // 2
            'Procurement Preferences',  // 3
            'KYC Documents',            // 4
            'Financial / Billing',      // 5
            'Done',                     // 6
        ];

        return collect($labels)->map(function ($label, $i) {
            $idx = $i + 1;
            return [
                'label' => $label,
                'state' => $this->step > $idx ? 'done' : ($this->step === $idx ? 'current' : 'todo'),
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.company.admin.onboarding', [
            'steps' => $this->steps(),
        ])->layout('layouts.admin', [
            'title' => 'Company Onboarding • ' . config('app.name'),
        ]);
    }
}
