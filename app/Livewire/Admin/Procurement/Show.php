<?php

namespace App\Livewire\Admin\Procurement;

use Livewire\Component;
use App\Services\Admin\ProcurementService;
use App\Models\Procurement\ProcurementRequest;

class Show extends Component
{
    public int $id; // route param
    public ProcurementRequest $req;

    /** only used to keep items table key stable if you later add admin actions */
    public int $version = 0;

    public function mount(int $id, ProcurementService $svc)
    {
        $this->id  = $id;
        $this->req = $svc->getRequest($id);
    }

    public function render()
    {
        // Compute same derived values the company page shows
        $status = \App\Services\Procurement\ProcurementRequestService::statusString($this->req->status);

        // Expected approvers from the same domain service (read-only)
        $expectedIds = app(\App\Services\Procurement\ProcurementRequestService::class)->resolveApprovers($this->req);
        $expected = empty($expectedIds) ? collect() : \App\Models\User::whereIn('id', $expectedIds)->get(['id','name','email']);
        $byId = $this->req->approvals->keyBy('approver_id');
        $mergedRows = $expected->map(function ($u) use ($byId) {
            $row = $byId->get($u->id);
            return (object)[
                'approver_id' => $u->id,
                'name'        => $u->name ?? ('User #'.$u->id),
                'email'       => $u->email,
                'status'      => $row->status ?? 'pending',
                'comment'     => $row->comment ?? null,
            ];
        });

        return view('livewire.admin.procurement.show', [
            'req'        => $this->req,
            'status'     => $status,
            'mergedRows' => $mergedRows,
        ])->layout('layouts.admin', [
            'title' => 'Request • PR-#'.$this->req->id.' | '.config('app.name'),
        ]);
    }
}
