<?php

namespace App\Livewire\Company\Procurement\Items;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use App\Models\Procurement\ProcurementRequest;
use App\Models\Procurement\ProcurementItem;
use App\Services\Procurement\ProcurementRequestService;
use App\Services\Procurement\ProcurementItemService;

class Wizard extends Component
{
    use WithFileUploads;

    public int $requestId;
    public string $kind = 'product';
    public bool $show = false;

    // Step control
    public int $step = 1;

    // quantity defaulting to 1
     public ?int $quantity = 1; 

    // Common detail fields
    public string $name = '';
    public ?string $short_description = null;
    public ?string $priority = 'low';
    public ?string $unit = null;
    public ?string $date_required = null;
    public ?float $budget_amount = null;

    // Service extras
    public ?string $service_budget_mode = null; // per_hour|fixed
    public ?string $service_payment_type = null; // per_hour|fixed

    // Product specs
    public ?string $brand = null;
    public ?string $model = null;
    public ?string $quality_level = null;
    public ?string $packaging_requirement = null;
    public bool $inspection_required = false;
    public array $technical_specs = [['key'=>'','value'=>'']];

    // Service specs
    public ?string $scope_of_work = null;
    public array $deliverables = [['milestone'=>'','criteria'=>'','due_date'=>null]];
    public array $key_personnels = [['role'=>'','count'=>1,'qualification'=>null]];

    // Attachments
    public array $files = [];

    //  Persisted across steps
    public ?int $itemId = null;   // <-- use this instead of a protected $item

    #[On('open-item-wizard')]
    public function open(string $kind = 'product'): void
    {
        // Reset for a fresh item; keep requestId intact
        $this->resetExcept(['requestId']);

        $kind = strtolower($kind);
        $this->kind = in_array($kind, ['product','service'], true) ? $kind : 'product';

        $this->itemId = null;      // new item
        $this->show = true;
        $this->step = 1;

        $this->dispatch('open-item-wizard-js');
    }

    /**
     * Resume an existing draft item by id (product or service).
     */
    #[On('open-item-wizard-resume')]
    public function resume(int $itemId): void
    {
        $item = ProcurementItem::with(['productSpec','serviceSpec', 'attachments'])
            ->where('procurement_request_id', $this->requestId) // scope to this request
            ->findOrFail($itemId);

        // Fill shared fields
        $this->itemId           = $item->id;
        $this->kind             = $item->kind;
        $this->name             = (string) $item->name;
        $this->short_description= $item->short_description;
        $this->priority         = $item->priority ?? 'low';
        $this->unit             = $item->unit;
        $this->quantity         = max(1, (int) ($item->quantity ?? 1));
        $this->date_required    = optional($item->date_required)->format('Y-m-d');
        $this->budget_amount    = $item->budget_amount ? (float) $item->budget_amount : null;

        if ($item->kind === 'product') {
            $ps = $item->productSpec;
            $this->brand                 = $ps->brand ?? null;
            $this->model                 = $ps->model ?? null;
            $this->quality_level         = $ps->quality_level ?? null;
            $this->packaging_requirement = $ps->packaging_requirement ?? null;
            $this->inspection_required   = (bool) ($ps->inspection_required ?? false);
            $this->technical_specs       = $ps && is_array($ps->technical_specs ?? null)
                                            ? array_values($ps->technical_specs)
                                            : [['key'=>'','value'=>'']];
        } else { // service
            $ss = $item->serviceSpec;
            $this->service_budget_mode   = $item->service_budget_mode ?? null;
            $this->service_payment_type  = $item->service_payment_type ?? null;
            $this->scope_of_work         = $ss->scope_of_work ?? null;
            $this->deliverables          = $ss && is_array($ss->deliverables ?? null)
                                            ? array_values($ss->deliverables)
                                            : [['milestone'=>'','criteria'=>'','due_date'=>null]];
            $this->key_personnels        = $ss && is_array($ss->key_personnels ?? null)
                                            ? array_values($ss->key_personnels)
                                            : [['role'=>'','count'=>1,'qualification'=>null]];
        }

        // Decide next step: if no spec => step 2, else step 3 (attachments)
        $hasSpec = $item->kind === 'product'
            ? (bool) $item->productSpec
            : (bool) $item->serviceSpec;

        $this->step = $hasSpec ? 3 : 2;

        $this->show = true;
        $this->dispatch('open-item-wizard-js');
    }

    // func to remove preview files from file upload modal
    public function removeSelectedFile(int $i): void
    {
        if (isset($this->files[$i])) {
            unset($this->files[$i]);
            $this->files = array_values($this->files);
        }
    }

    public function close(): void
    {
        $this->show = false;
        $this->dispatch('hide-item-wizard-js');
        $this->dispatch('request-updated');
        $this->dispatch('items-refresh');
    }

    public function addTechRow(){ $this->technical_specs[] = ['key'=>'','value'=>'']; }
    public function removeTechRow($i){ unset($this->technical_specs[$i]); $this->technical_specs = array_values($this->technical_specs); }
    public function addDeliverable(){ $this->deliverables[] = ['milestone'=>'','criteria'=>'','due_date'=>null]; }
    public function removeDeliverable($i){ unset($this->deliverables[$i]); $this->deliverables = array_values($this->deliverables); }
    public function addPersonnel(){ $this->key_personnels[] = ['role'=>'','count'=>1,'qualification'=>null]; }
    public function removePersonnel($i){ unset($this->key_personnels[$i]); $this->key_personnels = array_values($this->key_personnels); }

    public function saveDetail(ProcurementRequestService $svc): void
    {
        $this->validate([
            'name'=>['required','string','max:255'],
            'priority'=>['nullable', Rule::in(['low','medium','high','urgent'])],
            'unit'=>['nullable','string','max:50'],
            'date_required'=>['nullable','date'],
            'budget_amount'=>['nullable','numeric','min:0'],
            'service_budget_mode'=>['nullable', Rule::in(['per_hour','fixed'])],
            'service_payment_type'=>['nullable', Rule::in(['per_hour','fixed'])],
        ]);

        // Quantity only for product
        if ($this->kind === 'product') {

            $this->validate([
                'quantity'=>['required','integer','min:1'],
            ]);  
        }


        $req = ProcurementRequest::findOrFail($this->requestId);

        $item = $svc->addItemDraft($req, [
            'kind'=>$this->kind,
            'name'=>$this->name,
            'short_description'=>$this->short_description,
            'priority'=>$this->priority,
            'unit'=>$this->unit,
            'quantity'=> $this->kind === 'product' ? ($this->quantity ?: 1) : 1,
            'date_required'=>$this->date_required,
            'budget_amount'=>$this->budget_amount,
            'service_budget_mode'=>$this->service_budget_mode,
            'service_payment_type'=>$this->service_payment_type,
        ]);

        $this->itemId = $item->id;   // <-- persist id (public)
        $this->step = 2;
    }

    public function saveSpecs(ProcurementRequestService $svc): void
    {
        if (!$this->itemId) abort(400, 'Item not initialized.');
        $item = ProcurementItem::findOrFail($this->itemId); // <-- re-fetch

        if ($this->kind === 'product') {
            $this->validate([
                'technical_specs'=>'array',
                'technical_specs.*.key'=>'nullable|string|max:100',
                'technical_specs.*.value'=>'nullable|string|max:255',
                'brand'=>'nullable|string|max:100',
                'model'=>'nullable|string|max:100',
                'quality_level'=>'nullable|string|max:100',
                'packaging_requirement'=>'nullable|string|max:150',
                'inspection_required'=>'boolean',
            ]);
            $svc->saveItemSpecs($item, [
                'brand'=>$this->brand,
                'model'=>$this->model,
                'quality_level'=>$this->quality_level,
                'packaging_requirement'=>$this->packaging_requirement,
                'inspection_required'=>$this->inspection_required,
                'technical_specs'=>array_values(array_filter(
                    $this->technical_specs,
                    fn($r)=>($r['key']??'')!=='' || ($r['value']??'')!==''
                )),
            ]);
        } else { // service
            $this->validate([
                'scope_of_work'=>'nullable|string',
                'deliverables'=>'array',
                'deliverables.*.milestone'=>'nullable|string|max:120',
                'deliverables.*.criteria'=>'nullable|string|max:255',
                'deliverables.*.due_date'=>'nullable|date',
                'key_personnels'=>'array',
                'key_personnels.*.role'=>'nullable|string|max:120',
                'key_personnels.*.count'=>'nullable|integer|min:1',
                'key_personnels.*.qualification'=>'nullable|string|max:120',
            ]);
            $svc->saveItemSpecs($item, [
                'scope_of_work'=>$this->scope_of_work,
                'deliverables'=>array_values($this->deliverables),
                'key_personnels'=>array_values($this->key_personnels),
            ]);
        }

        $this->step = 3;
    }

    public function saveAttachments(ProcurementItemService $fileSvc): void
    {
        if (!$this->itemId) abort(400,'Item not initialized.');
        $item = ProcurementItem::findOrFail($this->itemId); // <-- re-fetch

        $this->validate(['files.*'=>'file|max:10240']); // 10MB each

        $fileSvc->attachFiles($item, $this->files);
        app(ProcurementRequestService::class)->finalizeItem($item);

        $this->dispatch('request-updated');
        $this->dispatch('items-refresh');

        session()->flash('success','Item added.');
        $this->itemId = null;
        $this->close();
    }

    public function render(){ return view('livewire.company.procurement.items.wizard'); }
}
