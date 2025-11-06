<?php

namespace App\Livewire\Admin\Companies\Requests;

use Livewire\Component;
use App\Models\Company\Company;
use App\Services\Company\CompanyApprovalService;

class Show extends Component
{
    public Company $company;

    public ?string $reason = null; // for reject/cancel

    public function mount(Company $company): void
    {
        $this->company = $company->load([
            'preference',
            'kycDocuments',
            'bankAccounts',
            'representative',
            'onboardingProgress',
            'addresses',    
            'contact',       
        ]);
    }

    // OPEN MODALS (Livewire v3: dispatch -> DOM event)
    public function openApprove(): void
    {
        $this->dispatch('open-modal', id: 'approveModal');
    }

    public function openReject(): void
    {
        $this->dispatch('open-modal', id: 'rejectModal');
    }

    public function openCancel(): void
    {
        $this->dispatch('open-modal', id: 'cancelModal');
    }

    // ACTIONS
    public function approve(CompanyApprovalService $svc)
    {
        $svc->setStatus($this->company, 'approved', null);
        session()->flash('success', 'Company approved.');
        return redirect()->route('admin.company.requests.index');
    }

    public function reject(CompanyApprovalService $svc)
    {
        $this->validate(['reason' => ['required','string','max:500']]);
        $svc->setStatus($this->company, 'rejected', $this->reason);
        session()->flash('success', 'Company rejected.');
        return redirect()->route('admin.company.requests.index');
    }

    public function cancel(CompanyApprovalService $svc)
    {
        $this->validate(['reason' => ['required','string','max:500']]);
        $svc->setStatus($this->company, 'cancelled', $this->reason);
        session()->flash('success', 'Company request cancelled.');
        return redirect()->route('admin.company.requests.index');
    }

    public function render()
    {
        return view('livewire.admin.companies.requests.show')->layout('layouts.admin', [
            'title' => 'Review Company • '.$this->company->legal_name,
        ]);
    }
}
