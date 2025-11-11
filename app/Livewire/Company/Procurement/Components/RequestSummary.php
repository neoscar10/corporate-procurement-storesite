<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Procurement\ProcurementRequest;

class RequestSummary extends Component
{
    public int $requestId;

    public function getReqProperty(): ProcurementRequest
    {
        return ProcurementRequest::withCount(['items', 'attachments'])->findOrFail($this->requestId);
    }

    #[On('summary-refresh')]
    #[On('request-updated')]
    public function nudge(): void {}

    public function render()
    {
        return view('livewire.company.procurement.components.request-summary', [
            'req' => $this->req,
        ]);
    }
}

