<?php

namespace App\Livewire\Admin\Procurement;

use Livewire\Component;
use App\Services\Admin\ProcurementService;

class ItemShow extends Component
{
    public int $requestId;
    public int $itemId;

    public $item; // ProcurementItem (model instance)
    public $req;  // ProcurementRequest (from $item->request)

    public function mount(int $request, int $item, ProcurementService $svc): void
    {
        $this->requestId = (int) $request;
        $this->itemId    = (int) $item;

        $this->item = $svc->getItem($this->requestId, $this->itemId);
        $this->req  = $this->item->request; // already eager-loaded
    }

    public function render()
    {
        return view('livewire.admin.procurement.item-show', [
            'item' => $this->item,
            'req'  => $this->req,
        ])->layout('layouts.admin', [
            'title' => 'Admin • Item #'.$this->item->id.' (PR-'.$this->req->id.') | '.config('app.name'),
        ]);
    }
}
