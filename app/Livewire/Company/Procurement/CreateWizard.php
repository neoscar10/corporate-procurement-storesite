<?php

namespace App\Livewire\Company\Procurement;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
use Illuminate\Validation\Rule;
use App\Services\Procurement\ProcurementRequestService;
use App\Enums\Procurement\{RequestType, Priority};
use Livewire\Attributes\On;

class CreateWizard extends Component
{
    public bool $showStep1 = false;
    public bool $showStep2 = false;

    // Step 1 — Details
    public string $title = '';
    public string $type = 'req';         // rfi|req|po|rfp
    public string $priority = 'low';     // low|medium|high|urgent
    public ?string $desired_response_at = null;
    public ?string $expected_delivery_at = null;

    // Step 2 — Budget & Logistics
    public string $currency = 'INR';
    public ?float $budget_min = null;
    public ?float $budget_max = null;
    public ?string $payment_terms = null;         // advance|net_30|net_45|net_50
    public ?string $delivery_location = null;
    public string $vendor_regions_input = '';     // UI text, comma-separated
    public array $preferred_vendor_regions = [];  // actual array stored
    public ?string $notes = null;

    #[On('open-create-wizard')]
    public function openStep1(): void
    {
        logger()->info('CreateWizard::openStep1 received');
        $this->resetForm();
        $this->showStep1 = true;

        // also fire browser event in case your modal relies on it
        $this->dispatch('open-create-step1');
    }

    public function closeStep1(): void { $this->showStep1 = false; }
    public function closeStep2(): void { $this->showStep2 = false; }

    protected function resetForm(): void
    {
        $this->reset([
            'showStep1','showStep2',
            'title','type','priority','desired_response_at','expected_delivery_at',
            'currency','budget_min','budget_max','payment_terms','delivery_location',
            'vendor_regions_input','preferred_vendor_regions','notes'
        ]);
        $this->type = 'req';
        $this->priority = 'low';
        $this->currency = 'INR';
    }

    public function saveStep1(): void
{
    $this->validate([
        'title' => ['required','string','max:255'],
        'type'  => ['required','in:rfi,req,po,rfp'],
        'priority' => ['required','in:low,medium,high,urgent'],
        'desired_response_at'  => ['nullable','date'],
        'expected_delivery_at' => ['nullable','date'],
    ]);

    // flip state
    $this->showStep1 = false;
    $this->showStep2 = true;

    // explicitly close step1 and open step2 (Bootstrap)
    $this->dispatch('close-create-step1');
    $this->dispatch('open-create-step2');

    logger()->info('CreateWizard::saveStep1 passed, opening Step 2');
}

    public function saveStep2(ProcurementRequestService $svc)
    {
        $this->preferred_vendor_regions = $this->parseRegions($this->vendor_regions_input);

        $this->validate([
            'currency' => ['required','in:INR'],
            'budget_min' => ['nullable','numeric','min:0'],
            'budget_max' => ['nullable','numeric','gte:budget_min'],
            'payment_terms' => ['nullable','in:advance,net_30,net_45,net_50'],
            'delivery_location' => ['nullable','string'],
            'preferred_vendor_regions' => ['nullable','array'],
            'preferred_vendor_regions.*' => ['string','max:100'],
            'notes' => ['nullable','string'],
        ]);

        $user = Auth::user();
        $companyId = CompanyMember::where('user_id',$user->id)->where('is_active',true)->value('company_id');

        if (! $companyId) {
            $this->addError('title', 'Company context missing. Complete company onboarding.');
            return;
        }

        // Only now create the draft record
        $req = $svc->createDraft(
            companyId: $companyId,
            creatorId: $user->id,
            step1: [
                'title'   => $this->title,
                'type'    => $this->type,
                'priority'=> $this->priority,
                'desired_response_at'  => $this->desired_response_at ?: null,
                'expected_delivery_at' => $this->expected_delivery_at ?: null,
            ],
            step2: [
                'currency' => $this->currency,
                'budget_min' => $this->budget_min,
                'budget_max' => $this->budget_max,
                'payment_terms' => $this->payment_terms,
                'delivery_location' => $this->delivery_location,
                'preferred_vendor_regions' => $this->preferred_vendor_regions,
                'notes' => $this->notes,
            ]
        );

        $this->showStep2 = false;
        session()->flash('success','Request created. You can now add items and submit for approval.');
        redirect()->route('company.procure.requests.show', $req->id);
    }

    private function parseRegions(?string $input): array
    {
        if (! $input) return [];
        return array_values(array_filter(array_map(fn($s)=>trim($s), explode(',', $input))));
    }

    public function render() { return view('livewire.company.procurement.create-wizard'); }
}
