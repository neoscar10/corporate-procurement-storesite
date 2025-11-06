<?php

namespace App\Livewire\Company\Procurement\Items;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
use App\Models\Procurement\ProcurementRequest;
use App\Models\Procurement\ProcurementItem;
use App\Services\Procurement\ProcurementItemService;

class Table extends Component
{
    public int $requestId;
    public ?int $deleteId = null;

    /** Whether items are editable (request is not locked) */
    public bool $canMutate = false;

    public function mount(int $requestId): void
    {
        $this->requestId = $requestId;
        $this->computeCanMutate();
    }

    protected $listeners = [
        'table-refresh'    => '$refresh',
        'request-updated'  => '$refresh',
    ];


    #[On('items-refresh')]
    public function refreshItems(): void
    {
        $this->computeCanMutate();
        $this->dispatch('$refresh');
    }

    public function askDelete(int $id): void
    {
        $this->deleteId = $id;
        // Explicitly open the modal (ensures visibility regardless of confirm impl)
        $this->dispatch('open-item-delete-modal');
    }

    public function deleteItem(ProcurementItemService $svc): void
    {
        if (! $this->deleteId) return;

        if (! $this->canMutate) {
            $this->deleteId = null;
            session()->flash('error', 'This request can no longer be modified.');
            return;
        }

        $userId = Auth::id();
        $companyId = CompanyMember::where('user_id',$userId)->where('is_active',true)->value('company_id');

        $item = ProcurementItem::where('procurement_request_id', $this->requestId)
            ->whereHas('request', fn($q)=>$q->where('company_id',$companyId))
            ->findOrFail($this->deleteId);

        $svc->deleteItem($item);

        $this->deleteId = null;
        session()->flash('success', 'Item deleted.');

        $this->refreshItems();
        $this->dispatch('request-updated');
    }

    private function computeCanMutate(): void
    {
        $userId = Auth::id();
        $companyId = CompanyMember::where('user_id',$userId)->where('is_active',true)->value('company_id');

        $req = ProcurementRequest::query()
            ->where('company_id', $companyId)
            ->findOrFail($this->requestId, ['id','status']);

        $status = $this->normalizeStatus($req->status);

        // Editable until approved/published (tweak if needed)
        $this->canMutate = ! in_array($status, ['approved','published'], true);
    }

    private function normalizeStatus($raw): string
    {
        if ($raw instanceof \BackedEnum) return strtolower($raw->value);
        if ($raw instanceof \UnitEnum)   return strtolower($raw->name);
        return strtolower((string)$raw);
    }

    public function render()
    {
        $userId = Auth::id();
        $companyId = CompanyMember::where('user_id',$userId)->where('is_active',true)->value('company_id');

        // Ascending by id (natural numbering)
        $items = ProcurementItem::with(['productSpec','serviceSpec','attachments'])
            ->where('procurement_request_id', $this->requestId)
            ->whereHas('request', fn($q)=>$q->where('company_id',$companyId))
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.company.procurement.items.table', [
            'items'     => $items,
            'canMutate' => $this->canMutate,
        ]);
    }
}
