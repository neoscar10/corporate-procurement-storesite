<?php

namespace App\Livewire\Company\Procurement\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use App\Models\Procurement\ProcurementRequest;
use App\Services\Procurement\ProcurementRequestService;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
class EditDetails extends Component
{
    public int $requestId;
    public bool $show = false;

    public string $title = '';
    public string $type = 'req';
    public string $priority = 'low';
    public ?string $desired_response_at = null;
    public ?string $expected_delivery_at = null;

    #[On('open-edit-details')]
    public function open(int $id): void
    {
        // scope to company to be extra safe
        $companyId = CompanyMember::where('user_id', Auth::id())
            ->where('is_active', true)->value('company_id');

        $req = \App\Models\Procurement\ProcurementRequest::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->findOrFail($id);

        $this->requestId = $req->id;
        $this->title = $req->title;
        $this->type  = (string)($req->type->value ?? $req->type);
        $this->priority = (string)($req->priority->value ?? $req->priority);
        $this->desired_response_at  = optional($req->desired_response_at)->format('Y-m-d\TH:i');
        $this->expected_delivery_at = optional($req->expected_delivery_at)->format('Y-m-d\TH:i');

        $this->show = true;
        $this->dispatch('open-edit-details-js');
    }

    public function close(){ $this->show = false; }

    public function save(ProcurementRequestService $svc)
    {
        $this->validate([
            'title'=>['required','string','max:255'],
            'type'=>['required','in:rfi,req,po,rfp'],
            'priority'=>['required','in:low,medium,high,urgent'],
            'desired_response_at'=>['nullable','date'],
            'expected_delivery_at'=>['nullable','date'],
        ]);

        $req = \App\Models\Procurement\ProcurementRequest::findOrFail($this->requestId);

        $svc->updateDraft($req, [
            'title'=>$this->title,
            'type'=>$this->type,
            'priority'=>$this->priority,
            'desired_response_at'=>$this->desired_response_at ?: null,
            'expected_delivery_at'=>$this->expected_delivery_at ?: null,
        ]);

        $this->dispatch('request-updated');

        // close both ways (reactive + Bootstrap)
        $this->show = false;
        $this->dispatch('hide-edit-details-js');

        session()->flash('success','Details updated.');
    }

    public function render(){ return view('livewire.company.procurement.modals.edit-details'); }
}
