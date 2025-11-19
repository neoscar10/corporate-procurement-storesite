<?php

namespace App\Livewire\Admin\VendorCategories;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Admin\VendorCategoryService;
use App\Models\Vendor\VendorCategory;

class Upsert extends Component
{
    public bool $show = false;
    public ?int $editId = null;

    // single-layer form (no parent_id)
    public string $kind = 'product';
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public bool $is_active = true;
    public int $display_order = 0;

    #[On('vc:open-upsert')]
    public function open(array $payload = []): void
    {
        $this->resetForm();

        if (!empty($payload['id'])) {
            $this->loadForEdit((int)$payload['id']);
        } else {
            $k = $payload['kind'] ?? 'product';
            $this->kind = in_array($k, ['product','service'], true) ? $k : 'product';
        }

        $this->show = true;
        $this->dispatch('vc:open-upsert-js');
    }

    private function loadForEdit(int $id): void
    {
        $this->editId = $id;
        $c = VendorCategory::findOrFail($id);

        $this->kind          = (string)$c->kind;
        $this->name          = (string)$c->name;
        $this->slug          = (string)$c->slug;
        $this->description   = $c->description;
        $this->is_active     = (bool)$c->is_active;
        $this->display_order = (int)$c->display_order;
    }

    private function resetForm(): void
    {
        $this->reset(['show','editId','kind','name','slug','description','is_active','display_order']);
        $this->kind = 'product';
        $this->is_active = true;
        $this->display_order = 0;
    }

    public function rules(): array
    {
        return [
            'kind'          => 'required|in:product,service',
            'name'          => 'required|string|max:120',
            'slug'          => 'nullable|string|max:140',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
            'display_order' => 'integer|min:0',
        ];
    }

    public function submit(VendorCategoryService $svc): void
    {
        $data = $this->validate();
        $svc->upsert($data, $this->editId);

        session()->flash('success', $this->editId ? 'Category updated.' : 'Category created.');
        $this->show = false;
        $this->dispatch('vc:refresh');
        $this->dispatch('vc:close-upsert-js');
    }

    public function render()
    {
        return view('livewire.admin.vendor-categories.upsert');
    }
}
