<?php

namespace App\Livewire\Admin\Vendors;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\VendorService;

class Index extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $active   = '';      // '' | '1' | '0'
    public string $provides = '';      // '' | 'products' | 'services'
    public int $perPage     = 15;

    public ?int $deleteId = null;

    protected $listeners = [
        'vendor:refresh' => '$refresh',
    ];

    public function updatingSearch()  { $this->resetPage(); }
    public function updatingActive()  { $this->resetPage(); }
    public function updatingProvides(){ $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->dispatch('vendor:open-upsert');
        $this->dispatch('vendor:open-upsert-js');
    }

    public function openEdit(int $id): void
    {
        $this->dispatch('vendor:open-upsert', ['id' => $id]);
        $this->dispatch('vendor:open-upsert-js');
    }

    public function askDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->dispatch('vendor:open-delete-js');
    }

    public function delete(\App\Services\Admin\VendorService $svc): void
    {
        if (! $this->deleteId) return;
        try {
            $svc->delete($this->deleteId);
            session()->flash('success', 'Vendor deleted.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage() ?: 'Delete failed.');
        }
        $this->deleteId = null;
        $this->dispatch('vendor:refresh');
    }

    public function toggle(int $id, VendorService $svc): void
    {
        $svc->toggleActive($id);
        $this->dispatch('vendor:refresh');
    }

    public function render(VendorService $svc)
    {
        $rows = $svc->paginate([
            'search'   => $this->search,
            'active'   => $this->active,
            'provides' => $this->provides,
        ], $this->perPage);

        return view('livewire.admin.vendors.index', compact('rows'))
            ->layout('layouts.admin', [
                'title' => 'Vendors | '.config('app.name'),
            ]);
    }
}
