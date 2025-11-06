<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Component;
use App\Models\Procurement\ProcurementRequest;

class RequestSummary extends Component
{
    public int $requestId;
    public ProcurementRequest $req;

    public function mount(int $requestId)
    {
        $this->req = ProcurementRequest::withCount(['items','attachments'])->findOrFail($requestId);
    }
    protected $listeners = [
        'request-updated' => '$refresh',
    ];


    public function render()
    {
        return view('livewire.company.procurement.components.request-summary', [
            'req' => $this->req
        ]);
    }
}
