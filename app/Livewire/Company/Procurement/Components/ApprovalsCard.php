<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\ProcurementRequest;
use App\Models\User;
use App\Services\Procurement\ProcurementRequestService as PRService;

class ApprovalsCard extends Component
{
    public int $requestId;

    // UI states for modals
    public bool $confirmApproveOpen = false;
    public bool $rejectOpen = false;
    public string $rejectComment = '';
    protected $listeners = [
        'approvals-refresh' => '$refresh',
        'request-updated'   => '$refresh',
    ];


    public function getReqProperty(): ProcurementRequest
    {
        return ProcurementRequest::with(['approvals.approver'])
            ->findOrFail($this->requestId);
    }

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
        // Creator can publish ONLY after all approvals are fulfilled and not yet published
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

    /** Whether the current user is in the expected approver set */
    public function getIsExpectedApproverProperty(): bool
    {
        $me = Auth::id();
        return $me ? in_array((int)$me, $this->expectedApproverIds, true) : false;
    }

    /** Creator-only: create approval rows & notify */
    public function requestApprovals(PRService $svc): void
    {
        if (! $this->isCreator) {
            session()->flash('error', 'Only the request creator can start approvals.');
            return;
        }

        if ($this->req->approvals()->exists()) {
            session()->flash('success', 'Approvals already requested.');
            $this->dispatch('request-updated');
            return;
        }

        $svc->submitForApproval($this->req->fresh());
        session()->flash('success', 'Approvals requested. Approvers have been notified.');
        $this->dispatch('request-updated');
    }

    /** Approver action: open confirms */
    public function openApproveConfirm(): void
    {
        if (! $this->isExpectedApprover) return;
        $this->confirmApproveOpen = true;
        $this->dispatch('open-approve-confirm-js');
    }

    public function openReject(): void
    {
        if (! $this->isExpectedApprover) return;
        $this->rejectOpen = true;
        $this->rejectComment = '';
        $this->dispatch('open-reject-modal-js');
    }

    /** Approver action: commit */
    public function approve(PRService $svc): void
    {
        if (! $this->isExpectedApprover) return;

        $svc->recordApproval($this->req->fresh(), Auth::user(), true, null);

        $this->confirmApproveOpen = false;
        session()->flash('success', 'You approved this request.');
        $this->dispatch('request-updated');
        $this->dispatch('close-approve-confirm-js');
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
        $this->dispatch('request-updated');
        $this->dispatch('close-reject-modal-js');
    }

    /** Creator-only publish (after approvals completed) */
    public function publish(PRService $svc): void
    {
        if (! $this->canPublish) return;

        $svc->publish($this->req->fresh());
        session()->flash('success', 'Request published.');
        $this->dispatch('request-updated');
    }

    /** Company Admin fast-track publish */
    public function publishNow(PRService $svc): void
    {
        if (! $this->canPublishNow) return;

        $svc->publishByAdmin($this->req->fresh(), Auth::user());
        session()->flash('success', 'Request published by Company Admin.');
        $this->dispatch('request-updated');
    }

    public function render()
    {
        return view('livewire.company.procurement.components.approvals-card', [
            'req'               => $this->req,
            'status'            => $this->status,
            'isCreator'         => $this->isCreator,
            'canPublish'        => $this->canPublish,
            'canPublishNow'     => $this->canPublishNow,
            'isExpectedApprover'=> $this->isExpectedApprover,
            'mergedRows'        => $this->mergedRows,
        ]);
    }
}
