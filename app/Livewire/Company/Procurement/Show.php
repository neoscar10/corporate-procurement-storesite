<?php

namespace App\Livewire\Company\Procurement;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\ProcurementRequest;
use App\Models\Company\CompanyMember;

class Show extends Component
{
    public int $requestId;
    public ProcurementRequest $req;

    /** bump to force remount of children (wire:key) */
    public int $version = 0;

    protected $listeners = [
        // generic refresh hooks that children dispatch after actions
        'request-updated'   => 'onRequestUpdated',
        'items-refresh'     => 'onRequestUpdated',
        'approvals-refresh' => 'onRequestUpdated',
    ];

    public function mount(int $requestId)
    {
        $this->requestId = $requestId;
        $this->req = $this->loadForCompany($requestId);
    }

    protected function loadForCompany(int $id): ProcurementRequest
    {
        $user = Auth::user();
        $companyId = CompanyMember::where('user_id',$user->id)->where('is_active',true)->value('company_id');

        return ProcurementRequest::with([
                'items.productSpec',
                'items.serviceSpec',
                'approvals.approver',
                'attachments'
            ])
            ->where('company_id',$companyId)
            ->findOrFail($id);
    }

    /** Centralized refresh from children */
    public function onRequestUpdated(): void
    {
        // hard refresh + eager load relations
        $this->req->refresh();
        $this->req->load([
            'items.productSpec',
            'items.serviceSpec',
            'approvals.approver',
            'attachments'
        ]);

        // bump version key so Livewire remounts nested components
        $this->version++;

        // also bounce a table refresh for any child listening
        $this->dispatch('table-refresh');
        $this->dispatch('approvals-refresh');
    }

    #[On('resume-item')]
    public function handleResumeItem(int $id): void
    {
        if ($id <= 0) return;

        // open item wizard on the existing item
        $this->dispatch('open-item-wizard-resume', $id)
            ->to('company.procurement.items.wizard');

        // Visual fallback (Bootstrap)
        $this->dispatch('open-item-wizard-js');
    }

    public function openEditDetails(): void
    {
        $this->dispatch('open-edit-details', $this->req->id)
            ->to('company.procurement.modals.edit-details');

        $this->dispatch('open-edit-details-js');
    }

    public function openEditBudget(): void
    {
        $this->dispatch('open-edit-budget', $this->req->id)
            ->to('company.procurement.modals.edit-budget');

        $this->dispatch('open-edit-budget-js');
    }

    public function openItemWizard(string $kind = 'product'): void
    {
        $this->dispatch('open-item-wizard', $kind)
            ->to('company.procurement.items.wizard');

        $this->dispatch('open-item-wizard-js', ['kind' => $kind]);
    }

    public function render()
    {
        return view('livewire.company.procurement.show', ['req'=>$this->req])
            ->layout('layouts.admin', ['title'=>'Request • PR-#'.$this->req->id.' | '.config('app.name')]);
    }
}
