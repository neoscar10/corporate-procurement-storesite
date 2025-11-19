<?php

namespace App\Livewire\Admin\VendorCategories;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Services\Admin\VendorCategoryService;

class Index extends Component
{
    use WithPagination;

    public string $kind = 'product'; // product | service
    public string $search = '';
    public string $active = '';      // '' | '1' | '0'
    public int $perPage = 15;

    public ?int $deleteId = null;

    protected $listeners = [
        'vc:refresh' => '$refresh',
    ];

    public function updatingKind()     { $this->resetPage(); }
    public function updatingSearch()   { $this->resetPage(); }
    public function updatingActive()   { $this->resetPage(); }
    public function updatingPerPage()  { $this->resetPage(); }

    public function openCreate(string $kind = 'product'): void
    {
        $this->dispatch('vc:open-upsert', ['kind' => $kind]);
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('vc:open-upsert', ['id' => $id]);
    }

    public function askDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('vc:open-delete');
    }

    public function delete(VendorCategoryService $svc): void
    {
        if (! $this->deleteId) return;

        try {
            $svc->delete($this->deleteId);
            session()->flash('success', 'Category deleted.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage() ?: 'Delete failed.');
        }

        $this->deleteId = null;
        $this->dispatch('vc:refresh');
    }

    public function toggle(int $id, VendorCategoryService $svc): void
    {
        $svc->toggleActive($id);
        $this->dispatch('vc:refresh');
    }

    public function move(int $id, string $dir, VendorCategoryService $svc): void
    {
        $svc->move($id, $dir === 'up' ? 'up' : 'down');
        $this->dispatch('vc:refresh');
    }

    public function render(VendorCategoryService $svc)
    {
        $rows   = $svc->paginate([
            'kind'   => $this->kind,
            'search' => $this->search,
            'active' => $this->active,
        ], $this->perPage);

        $counts = $svc->counts();

        return view('livewire.admin.vendor-categories.index', compact('rows','counts'))
            ->layout('layouts.admin', [
                'title' => 'Vendor Categories • '.ucfirst($this->kind).' | '.config('app.name')
            ]);
    }
}
