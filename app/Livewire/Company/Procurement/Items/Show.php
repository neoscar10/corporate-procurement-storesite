<?php

namespace App\Livewire\Company\Procurement\Items;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
use App\Models\Procurement\ProcurementItem;
use App\Models\Procurement\ProcurementRequest;

class Show extends Component
{
    public int $requestId;
    public int $itemId;

    public ProcurementRequest $req;
    public ProcurementItem $item;

    // Accept models (implicit binding)
    public function mount(ProcurementRequest $request, ProcurementItem $item)
    {
        $user = Auth::user();
        $companyId = CompanyMember::where('user_id', $user->id)
            ->where('is_active', true)
            ->value('company_id');

        // Company scope check
        abort_if((int) $request->company_id !== (int) $companyId, 403);

        // Ensure item belongs to this request
        abort_if((int) $item->procurement_request_id !== (int) $request->id, 404);

        // Save ids
        $this->requestId = (int) $request->id;
        $this->itemId    = (int) $item->id;

        // Eager load what we need
        $this->req  = $request->loadMissing(['attachments']);
        $this->item = $item->loadMissing(['productSpec','serviceSpec','attachments']);
    }

    private function money(?float $amount): string
    {
        if (is_null($amount)) return '—';
        $ccy = strtoupper($this->req->currency ?? 'INR');
        $sym = $ccy === 'INR' ? '₹' : $ccy;
        return $sym . number_format($amount, 2);
    }

    public function render()
    {
        return view('livewire.company.procurement.items.show', [
            'money' => fn($v) => $this->money($v),
        ])->layout('layouts.admin', [
            'title' => 'Item • #'.$this->item->id.' | PR-#'.$this->req->id.' | '.config('app.name'),
        ]);
    }
}
