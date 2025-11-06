<?php

namespace App\Livewire\Company\Procurement\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Procurement\ProcurementRequest;
use App\Services\Procurement\ProcurementRequestService;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;

class EditBudget extends Component
{
    public int $requestId;
    public bool $show = false;

    public string $currency = 'INR';
    public ?float $budget_min = null;
    public ?float $budget_max = null;
    public ?string $payment_terms = null;
    public ?string $delivery_location = null;
    public string $vendor_regions_input = '';
    public array $preferred_vendor_regions = [];
    public ?string $notes = null;

    #[On('open-edit-budget')]
    public function open(int $id): void
    {
        $companyId = CompanyMember::where('user_id', Auth::id())
            ->where('is_active', true)->value('company_id');

        $req = \App\Models\Procurement\ProcurementRequest::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->findOrFail($id);

        $this->requestId = $req->id;
        $this->currency = $req->currency ?? 'INR';
        $this->budget_min = $req->budget_min;
        $this->budget_max = $req->budget_max;
        $this->payment_terms = $req->payment_terms;
        $this->delivery_location = $req->delivery_location;
        $this->preferred_vendor_regions = is_array($req->preferred_vendor_regions) ? $req->preferred_vendor_regions : [];
        $this->vendor_regions_input = implode(', ', $this->preferred_vendor_regions);
        $this->notes = $req->notes;

        $this->show = true;
        $this->dispatch('open-edit-budget-js');
    }


    public function close(){ $this->show = false; }

    public function save(\App\Services\Procurement\ProcurementRequestService $svc)
    {
        $this->preferred_vendor_regions = $this->parse($this->vendor_regions_input);

        $this->validate([
            'currency'=>['required','in:INR'],
            'budget_min'=>['nullable','numeric','min:0'],
            'budget_max'=>['nullable','numeric','gte:budget_min'],
            'payment_terms'=>['nullable','in:advance,net_30,net_45,net_50'],
            'delivery_location'=>['nullable','string'],
            'preferred_vendor_regions'=>['nullable','array'],
            'preferred_vendor_regions.*'=>['string','max:100'],
            'notes'=>['nullable','string'],
        ]);

        $req = \App\Models\Procurement\ProcurementRequest::findOrFail($this->requestId);

        $svc->updateDraft($req, [
            'currency'=>$this->currency,
            'budget_min'=>$this->budget_min,
            'budget_max'=>$this->budget_max,
            'payment_terms'=>$this->payment_terms,
            'delivery_location'=>$this->delivery_location,
            'preferred_vendor_regions'=>$this->preferred_vendor_regions,
            'notes'=>$this->notes,
        ]);

        $this->dispatch('request-updated');

        // close both ways
        $this->show = false;
        $this->dispatch('hide-edit-budget-js');

        session()->flash('success','Budget & logistics updated.');
    }

    private function parse(?string $s): array
    {
        if (! $s) return [];
        return array_values(array_filter(array_map(fn($v)=>trim($v), explode(',', $s))));
    }

    public function render(){ return view('livewire.company.procurement.modals.edit-budget'); }
}
