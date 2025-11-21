<?php

namespace App\Livewire\Admin\Vendors;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\VendorService;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public string $search   = '';
    public string $active   = '';      // '' | '1' | '0'
    public string $provides = '';      // '' | 'products' | 'services'
    public int    $perPage  = 15;

    public ?int $deleteId = null;

    protected $listeners = [
        'vendor:refresh' => '$refresh',
    ];

    // Keep state in query string (optional but matches working flow)
    protected $queryString = [
        'search'   => ['except' => ''],
        'active'   => ['except' => ''],
        'provides' => ['except' => ''],
        'perPage'  => ['except' => 15],
        'page'     => ['except' => 1],
    ];

    // Reset page whenever a filter changes
    public function updating($name, $value): void
    {
        if (in_array($name, ['search', 'active', 'provides', 'perPage'], true)) {
            $this->resetPage();
        }
    }

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

    public function delete(VendorService $svc): void
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
        $q = $svc->query()
            // Search in vendor fields + category names
            ->when($this->search !== '', function (Builder $b) {
                $s = '%'.$this->search.'%';

                $b->where(function (Builder $q) use ($s) {
                    $q->where('name', 'like', $s)
                      ->orWhere('email', 'like', $s)
                      ->orWhere('phone', 'like', $s)
                      ->orWhere('company_name', 'like', $s)
                      ->orWhereHas('categories', function (Builder $q2) use ($s) {
                          $q2->where('name', 'like', $s);
                      });
                });
            })
            // Active filter ('' | '1' | '0')
            ->when($this->active !== '', function (Builder $b) {
                $b->where('is_active', $this->active === '1' ? 1 : 0);
            })
            // Provides filter
            ->when($this->provides === 'products', function (Builder $b) {
                $b->where('provides_products', true);
            })
            ->when($this->provides === 'services', function (Builder $b) {
                $b->where('provides_services', true);
            })
            ->orderByDesc('id');

        $rows = $q->paginate($this->perPage)->withQueryString();

        return view('livewire.admin.vendors.index', compact('rows'))
            ->layout('layouts.admin', [
                'title' => 'Vendors | ' . config('app.name'),
            ]);
    }
}
