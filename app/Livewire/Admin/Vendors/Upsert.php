<?php

namespace App\Livewire\Admin\Vendors;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorCategory;
use App\Services\Admin\VendorService;

class Upsert extends Component
{
    public bool $show = false;
    public ?int $editId = null;

    // form
    public string $name = '';
    public string $email = '';
    public ?string $company_name = null;
    public ?string $phone = null;
    public bool $provides_products = false;
    public bool $provides_services = false;
    public bool $is_active = true;

    // selected categories
    public array $product_category_ids = [];
    public array $service_category_ids = [];

    // pickers (modals)
    public bool $showProdModal = false;
    public bool $showSvcModal = false;

    // search inside modals
    public string $productCatSearch = '';
    public string $serviceCatSearch = '';

    #[On('vendor:open-upsert')]
    public function open(array $payload = []): void
    {
        $this->resetForm();

        if (!empty($payload['id'])) {
            $this->loadForEdit((int) $payload['id']);
        }

        $this->show = true;
        $this->dispatch('vendor:open-upsert-js'); // optional JS fallback
    }

    private function resetForm(): void
    {
        $this->reset([
            'show','editId','name','email','company_name','phone',
            'provides_products','provides_services','is_active',
            'product_category_ids','service_category_ids',
            'showProdModal','showSvcModal',
            'productCatSearch','serviceCatSearch',
        ]);

        $this->is_active = true;
    }

    private function loadForEdit(int $id): void
    {
        $this->editId = $id;

        $v = Vendor::with('categories:id,kind')->findOrFail($id);

        $this->name              = (string) $v->name;
        $this->email             = (string) $v->email;
        $this->company_name      = $v->company_name;
        $this->phone             = $v->phone;
        $this->provides_products = (bool) $v->provides_products;
        $this->provides_services = (bool) $v->provides_services;
        $this->is_active         = (bool) $v->is_active;

        $this->product_category_ids = $v->categories->where('kind','product')->pluck('id')->all();
        $this->service_category_ids = $v->categories->where('kind','service')->pluck('id')->all();
    }

    // keep emails locked on edit at the view level

    public function rules(): array
    {
        return [
            'name'                => ['required','string','max:160'],
            'email'               => ['required','email','max:160'],
            'company_name'        => ['nullable','string','max:160'],
            'phone'               => ['nullable','string','max:40'],
            'provides_products'   => ['boolean'],
            'provides_services'   => ['boolean'],
            'is_active'           => ['boolean'],
            'product_category_ids'=> ['array'],
            'service_category_ids'=> ['array'],
        ];
    }

    public function updatedProvidesProducts($val): void
    {
        if (! $val) {
            $this->product_category_ids = [];
            $this->productCatSearch = '';
        }
    }

    public function updatedProvidesServices($val): void
    {
        if (! $val) {
            $this->service_category_ids = [];
            $this->serviceCatSearch = '';
        }
    }

    /** Open pickers */
    public function openProdPicker(): void
    {
        if (! $this->provides_products) return;
        $this->productCatSearch = '';
        $this->showProdModal = true;
        $this->dispatch('vendor:open-prod-cats-js');
    }

    public function openSvcPicker(): void
    {
        if (! $this->provides_services) return;
        $this->serviceCatSearch = '';
        $this->showSvcModal = true;
        $this->dispatch('vendor:open-svc-cats-js');
    }

    /** Close pickers */
    public function closeProdPicker(): void
    {
        $this->showProdModal = false;
        $this->dispatch('vendor:close-cat-modals-js');
    }

    public function closeSvcPicker(): void
    {
        $this->showSvcModal = false;
        $this->dispatch('vendor:close-cat-modals-js');
    }

    /** Toggle a single category id in a given set */
    public function toggleCategory(string $kind, int $id): void
    {
        if ($kind === 'product') {
            $arr = $this->product_category_ids;
            if (in_array($id, $arr, true)) {
                $this->product_category_ids = array_values(array_diff($arr, [$id]));
            } else {
                $this->product_category_ids[] = $id;
            }
        } elseif ($kind === 'service') {
            $arr = $this->service_category_ids;
            if (in_array($id, $arr, true)) {
                $this->service_category_ids = array_values(array_diff($arr, [$id]));
            } else {
                $this->service_category_ids[] = $id;
            }
        }
    }

    /** Select all results currently visible in the modal (respecting search) */
    public function selectAll(string $kind): void
    {
        $q = VendorCategory::query()
            ->where('kind', $kind)
            ->orderBy('name');

        if ($kind === 'product' && $this->productCatSearch !== '') {
            $s = '%' . $this->productCatSearch . '%';
            $q->where('name', 'like', $s);
        }

        if ($kind === 'service' && $this->serviceCatSearch !== '') {
            $s = '%' . $this->serviceCatSearch . '%';
            $q->where('name', 'like', $s);
        }

        $ids = $q->pluck('id')->all();

        if ($kind === 'product') {
            $this->product_category_ids = array_values(
                array_unique(array_merge($this->product_category_ids, $ids))
            );
        } else {
            $this->service_category_ids = array_values(
                array_unique(array_merge($this->service_category_ids, $ids))
            );
        }
    }


    /** Clear all currently selected for a kind */
    public function clearAll(string $kind): void
    {
        if ($kind === 'product') {
            $this->product_category_ids = [];
        } else {
            $this->service_category_ids = [];
        }
    }

    public function submit(VendorService $svc): void
    {
        $data = $this->validate();

        if (! $data['provides_products'] && ! $data['provides_services']) {
            $this->addError('provides_products', 'Select at least one: Products or Services.');
            return;
        }

        if (! $data['provides_products']) $data['product_category_ids'] = [];
        if (! $data['provides_services']) $data['service_category_ids'] = [];

        $svc->upsert($data, $this->editId);

        session()->flash('success', $this->editId ? 'Vendor updated.' : 'Vendor created.');
        $this->show = false;
        $this->dispatch('vendor:refresh');
        $this->dispatch('vendor:close-upsert-js');
    }

    public function render()
    {
        // Options (server-side search for the modals)
        $productOptions = VendorCategory::query()
            ->where('kind', 'product')
            ->when($this->productCatSearch !== '', function ($q) {
                $s = '%' . $this->productCatSearch . '%';
                $q->where('name', 'like', $s);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $serviceOptions = VendorCategory::query()
            ->where('kind', 'service')
            ->when($this->serviceCatSearch !== '', function ($q) {
                $s = '%' . $this->serviceCatSearch . '%';
                $q->where('name', 'like', $s);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        // Selected chips (names)
        $selectedProduct = empty($this->product_category_ids)
            ? collect()
            : VendorCategory::whereIn('id', $this->product_category_ids)
                ->orderBy('name')
                ->get(['id','name']);

        $selectedService = empty($this->service_category_ids)
            ? collect()
            : VendorCategory::whereIn('id', $this->service_category_ids)
                ->orderBy('name')
                ->get(['id','name']);

        return view('livewire.admin.vendors.upsert', compact(
            'productOptions', 'serviceOptions', 'selectedProduct', 'selectedService'
        ));
    }

}
