<?php

namespace App\Livewire\Admin\Vendors;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\VendorCategory;
use App\Services\Admin\VendorService;
use Illuminate\Validation\Rule;

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

    // category selects
    public array $product_category_ids = [];
    public array $service_category_ids = [];

    // search within lists
    public string $productCatSearch = '';
    public string $serviceCatSearch = '';

    #[On('vendor:open-upsert')]
    public function open(array $payload = []): void
    {
        $this->resetForm();

        if (!empty($payload['id'])) {
            $this->loadForEdit((int)$payload['id']);
        }

        $this->show = true;
    }

    private function resetForm(): void
    {
        $this->reset([
            'show','editId','name','email','company_name','phone',
            'provides_products','provides_services','is_active',
            'product_category_ids','service_category_ids',
            'productCatSearch','serviceCatSearch',
        ]);
        $this->is_active = true;
    }

    private function loadForEdit(int $id): void
    {
        $this->editId = $id;
        $v = Vendor::with('categories:id,kind')->findOrFail($id);

        $this->name              = (string)$v->name;
        $this->email             = (string)$v->email;  // locked on update (disable in view)
        $this->company_name      = $v->company_name;
        $this->phone             = $v->phone;
        $this->provides_products = (bool)$v->provides_products;
        $this->provides_services = (bool)$v->provides_services;
        $this->is_active         = (bool)$v->is_active;

        $this->product_category_ids = $v->categories->where('kind','product')->pluck('id')->all();
        $this->service_category_ids = $v->categories->where('kind','service')->pluck('id')->all();
    }

    public function rules(): array
    {
        $emailRule = $this->editId
            ? ['required','email','max:160'] // not changing user email during edit
            : ['required','email','max:160'];

        return [
            'name'               => ['required','string','max:160'],
            'email'              => $emailRule,
            'company_name'       => ['nullable','string','max:160'],
            'phone'              => ['nullable','string','max:40'],
            'provides_products'  => ['boolean'],
            'provides_services'  => ['boolean'],
            'is_active'          => ['boolean'],
            'product_category_ids' => ['array'],
            'service_category_ids' => ['array'],
        ];
    }

    public function submit(VendorService $svc): void
    {
        $data = $this->validate();

        // UX guard: must select at least one provide flag
        if (! $data['provides_products'] && ! $data['provides_services']) {
            $this->addError('provides_products', 'Select at least one: Products or Services.');
            return;
        }

        // if a side is disabled, clear its categories
        if (! $data['provides_products']) $data['product_category_ids'] = [];
        if (! $data['provides_services']) $data['service_category_ids'] = [];

        $svc->upsert($data, $this->editId);

        session()->flash('success', $this->editId ? 'Vendor updated.' : 'Vendor created.');
        $this->show = false;
        $this->dispatch('vendor:refresh');
        $this->dispatch('vendor:close-upsert-js');
    }

    public function render(VendorService $svc)
    {
        $options = $svc->categoryOptions();

        // filter by search
        $productOptions = collect($options['product'])->when($this->productCatSearch !== '', function($c){
            $s = mb_strtolower($this->productCatSearch);
            return $c->filter(fn($x) => str_contains(mb_strtolower($x->name), $s));
        });

        $serviceOptions = collect($options['service'])->when($this->serviceCatSearch !== '', function($c){
            $s = mb_strtolower($this->serviceCatSearch);
            return $c->filter(fn($x) => str_contains(mb_strtolower($x->name), $s));
        });

        return view('livewire.admin.vendors.upsert', [
            'productOptions' => $productOptions,
            'serviceOptions' => $serviceOptions,
        ]);
    }
}
