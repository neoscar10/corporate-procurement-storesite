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

        // If an explicit resubmission query is present, perform reset + pend and jump to step 1 (edit mode)
        if (request()->boolean('resubmit', false)) {
            $progress->resetForResubmission($company);
            if ($company->status !== 'pending') {
                $company->forceFill(['status' => 'pending'])->save();
            }
            // Update local cached status
            $this->companyStatus = 'pending';
            $this->readOnlySuccess = false;
            $this->step = 1;

            session()->flash('success', 'Please review your details and resubmit.');
            return;
        }

        // If approved, skip onboarding entirely
        if ($this->companyStatus === 'approved') {
            redirect()->route('company.admin.dashboard')->send();
            return;
        }

        // (Optional) reconcile existing data -> progress (keep if already implemented)
        if (method_exists($progress, 'reconcile')) {
            $progress->reconcile($company);
        }

        $forceEdit = request()->boolean('edit', false);
        $queryStep = (int) request()->query('step', 1);

        // If currently rejected and not explicitly editing, show the rejected card (no stepper)
        if ($this->companyStatus === 'rejected' && ! $forceEdit) {
            $this->readOnlySuccess = false;
            $this->step = 0; // sentinel meaning "show rejected card"
            return;
        }

        // Normal path
        $completed = $progress->isComplete($company);

        if ($completed && ! $forceEdit) {
            // Completed but not editing -> show the success card only
            $this->readOnlySuccess = true;
            $this->step = 4;
            return;
        }

        if ($forceEdit) {
            // Editing mode -> only steps 1..3 make sense
            $this->readOnlySuccess = false;
            $this->step = max(1, min(3, $queryStep));
            return;
        }

        // Drive from persisted progress, but allow query to jump forward (never backward)
        $persistedStep = $progress->currentStep($company); // 1..4
        $this->step = max($persistedStep, $queryStep);
    }

    #[On('onboard.resubmit.requested')]
    public function startResubmission(OnboardingProgressService $progress): mixed
    {
        // Run the same safe path as the query handler, but redirect explicitly
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
        $labels = ['Procurement Preferences', 'KYC Documents', 'Financial / Billing', 'Done'];
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
