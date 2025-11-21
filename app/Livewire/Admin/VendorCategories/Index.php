<?php

namespace App\Livewire\Admin\VendorCategories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\VendorCategoryService;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $kind    = 'product'; // product | service
    public string $search  = '';
    public string $active  = '';        // '' | '1' | '0'
    public int    $perPage = 15;

    public ?int $deleteId = null;

    protected $listeners = [
        'vc:refresh' => '$refresh',
    ];

    // Keep state in URL (similar to Company Requests)
    protected $queryString = [
        'kind'    => ['except' => 'product'],
        'search'  => ['except' => ''],
        'active'  => ['except' => ''],
        'perPage' => ['except' => 15],
        'page'    => ['except' => 1],
    ];

    // Single hook to reset page when filters change
    public function updating($name, $value): void
    {
        if (in_array($name, ['kind','search','active','perPage'], true)) {
            $this->resetPage();
        }
    }

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
        $q = $svc->query($this->kind)
            // Search by name/slug
            ->when($this->search !== '', function (Builder $b) {
                $s = '%'. $this->search .'%';
                $b->where(function (Builder $q) use ($s) {
                    $q->where('name', 'like', $s)
                      ->orWhere('slug', 'like', $s);
                });
            })
            // Active filter
            ->when($this->active !== '', function (Builder $b) {
                $b->where('is_active', $this->active === '1' ? 1 : 0);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('name', 'asc');

        $rows   = $q->paginate($this->perPage)->withQueryString();
        $counts = $svc->counts();

        return view('livewire.admin.vendor-categories.index', compact('rows','counts'))
            ->layout('layouts.admin', [
                'title' => 'Vendor Categories • '.ucfirst($this->kind).' | '.config('app.name'),
            ]);
    }
}
