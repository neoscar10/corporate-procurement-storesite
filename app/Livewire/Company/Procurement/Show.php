<?php

namespace App\Livewire\Company\Procurement;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\ProcurementRequest;
use App\Models\Company\CompanyMember;
use App\Models\User;
use App\Services\Procurement\ProcurementRequestService as PRService;

class Show extends Component
{
    public int $requestId;
    public ProcurementRequest $req;

    /** bump to force remount of items list only */
    public int $version = 0;

    // --- Approvals UI state ---
    public bool $confirmApproveOpen = false;
    public bool $rejectOpen = false;
    public string $rejectComment = '';
    // view reason modal state
   public bool $viewReasonOpen = false;
    public string $viewReasonText = '';
    public ?int $viewReasonBy = null;

    protected $listeners = [
        'request-updated'   => 'onRequestUpdated',
        'items-refresh'     => 'onRequestUpdated',
        'approvals-refresh' => 'onRequestUpdated',
        'structure-changed' => 'onStructureChanged',
    ];

    public function mount(int $requestId)
    {
        $this->requestId = $requestId;
        $this->req = $this->loadForCompany($requestId);
    }

    protected function loadForCompany(int $id): ProcurementRequest
    {
        $user = Auth::user();
        $companyId = CompanyMember::where('user_id', $user->id)->where('is_active', true)->value('company_id');

        return ProcurementRequest::with([
                'creator:id,name,email', 
                'items.productSpec',
                'items.serviceSpec',
                'approvals.approver',
                'attachments'
            ])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    /** ---------- Derived (computed) props ---------- */

    public function getStatusProperty(): string
    {
        return PRService::statusString($this->req->status);
    }

    public function getIsCreatorProperty(): bool
    {
        return (int) $this->req->created_by === (int) (Auth::id() ?? 0);
    }

    public function getCanPublishNowProperty(): bool
    {
        $me = Auth::user();
        return $me ? app(PRService::class)->isCompanyAdminFor($me, $this->req->company_id) : false;
    }

    public function getIsFullyApprovedProperty(): bool
    {
        return $this->req->isFullyApproved();
    }

    public function getCanPublishProperty(): bool
    {
        return $this->isCreator && $this->isFullyApproved && $this->status !== 'published';
    }

    /** Expected approvers (IDs) without creating DB rows yet */
    public function getExpectedApproverIdsProperty(): array
    {
        return app(PRService::class)->resolveApprovers($this->req);
    }

    public function getExpectedApproversProperty()
    {
        $ids = $this->expectedApproverIds;
        return empty($ids)
            ? collect()
            : User::whereIn('id', $ids)->get(['id','name','email']);
    }

    public function getMergedRowsProperty()
    {
        $byId = $this->req->approvals->keyBy('approver_id');

        return $this->expectedApprovers->map(function (User $u) use ($byId) {
            $row = $byId->get($u->id);
            return (object) [
                'approver_id' => $u->id,
                'name'        => $u->name ?? ('User #'.$u->id),
                'email'       => $u->email,
                'status'      => $row->status ?? 'pending',
                'approved_at' => $row->approved_at ?? null,
                'rejected_at' => $row->rejected_at ?? null,
                'comment'     => $row->comment ?? null,
                'exists'      => (bool) $row,
                'is_me'       => (int) $u->id === (int) (Auth::id() ?? 0),
            ];
        });
    }

    public function getIsExpectedApproverProperty(): bool
    {
        $me = Auth::id();
        return $me ? in_array((int)$me, $this->expectedApproverIds, true) : false;
    }

    /** ---------- Global refresh handlers ---------- */

    public function onRequestUpdated(): void
    {
        $this->req = $this->loadForCompany($this->requestId);

        // Nudge children (summary/items table) to refresh their data if needed
        $this->dispatch('summary-refresh');
        $this->dispatch('table-refresh');
        $this->dispatch('approvals-refresh');
    }

    public function onStructureChanged(): void
    {
        // Only items list remounts on structure changes
        $this->version++;
    }

    /** ---------- Top buttons (Details/Budget/Items) ---------- */

    #[On('resume-item')]
    public function handleResumeItem(int $id): void
    {
        if ($id <= 0) return;

        $this->dispatch('open-item-wizard-resume', $id)
            ->to('company.procurement.items.wizard');

        $this->dispatch('open-item-wizard-js');
    }
    public function openReason(int $approverId): void
    {
        $row = $this->req->fresh('approvals')->approvals->firstWhere('approver_id', $approverId);

        $this->viewReasonBy   = $approverId;
        $this->viewReasonText = trim((string) ($row->comment ?? '')) ?: 'No reason provided.';
        $this->viewReasonOpen = true;

        // match the pattern you already use: *-js
        $this->dispatch('open-view-reason-js');
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

    /** ---------- Approvals actions (merged here) ---------- */

    public function requestApprovals(PRService $svc): void
    {
        if (! $this->isCreator) {
            session()->flash('error', 'Only the request creator can start approvals.');
            return;
        }

        if (! $this->req->items->count()) {
            session()->flash('error', 'Add at least one item before requesting approvals.');
            return;
        }

        if ($this->req->approvals()->exists()) {
            session()->flash('success', 'Approvals already requested.');
            $this->dispatch('lw:refresh-all');
            $this->dispatch('request-updated');
            return;
        }

        $svc->submitForApproval($this->req->fresh());
        session()->flash('success', 'Approvals requested. Approvers have been notified.');
        $this->dispatch('lw:refresh-all');
        $this->dispatch('request-updated');
    }

   public function openApproveConfirm(): void
    {
        if (! $this->isExpectedApprover) return;

        $this->confirmApproveOpen = true; // keep the boolean in sync
        $this->dispatch('open-approve-confirm'); // <-- force Bootstrap modal open
    }

    public function openReject(): void
    {
        if (! $this->isExpectedApprover) return;

        $this->rejectOpen = true;         // keep the boolean in sync
        $this->rejectComment = '';
        $this->dispatch('open-reject-modal'); // <-- force Bootstrap modal open
    }


    public function approve(PRService $svc): void
    {
        if (! $this->isExpectedApprover) return;

        $svc->recordApproval($this->req->fresh(), Auth::user(), true, null);

        $this->confirmApproveOpen = false;
        session()->flash('success', 'You approved this request.');
        $this->dispatch('lw:refresh-all');
        $this->dispatch('request-updated');
    }

    public function reject(PRService $svc): void
    {
        if (! $this->isExpectedApprover) return;

        $comment = trim($this->rejectComment);
        if ($comment === '') {
            session()->flash('error', 'Please add a reason for rejection.');
            return;
        }

        $svc->recordApproval($this->req->fresh(), Auth::user(), false, $comment);

        $this->rejectOpen = false;
        $this->rejectComment = '';
        session()->flash('success', 'You rejected this request.');
        $this->dispatch('lw:refresh-all');
        $this->dispatch('request-updated');
    }

    public function publish(PRService $svc): void
    {
        if (! $this->canPublish) return;

        $svc->publish($this->req->fresh());
        session()->flash('success', 'Request published.');
        $this->dispatch('lw:refresh-all');
        $this->dispatch('request-updated');
    }

    public function publishNow(PRService $svc): void
    {
        if (! $this->canPublishNow) return;

        $svc->publishByAdmin($this->req->fresh(), Auth::user());
        session()->flash('success', 'Request published by Company Admin.');
        $this->dispatch('lw:refresh-all');
        $this->dispatch('request-updated');
    }

    public function render()
    {
        return view('livewire.company.procurement.show', [
            'req'               => $this->req,
            // expose computed so Blade can use simple variables
            'status'            => $this->status,
            'isCreator'         => $this->isCreator,
            'canPublish'        => $this->canPublish,
            'canPublishNow'     => $this->canPublishNow,
            'isExpectedApprover'=> $this->isExpectedApprover,
            'mergedRows'        => $this->mergedRows,
        ])->layout('layouts.admin', [
            'title' => 'Request • PR-#'.$this->req->id.' | '.config('app.name')
        ]);
    }
}
