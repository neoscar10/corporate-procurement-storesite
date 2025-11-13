<?php

namespace App\Livewire\Admin\Procurement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Procurement\ProcurementRequest;


class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    // Reset pagination when filters change
    public function updatingSearch()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function render()
    {
        $q = ProcurementRequest::query()
            ->withCount(['items'])
            ; // handle enum-backed too at DB level

        if ($this->search !== '') {
            $s = '%'.$this->search.'%';
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', $s)
                   ->orWhere('id', 'like', $s);
            });
        }

        $rows = $q->orderByDesc('id')->paginate($this->perPage);

        return view('livewire.admin.procurement.index', compact('rows'))
            ->layout('layouts.admin', [
                'title' => 'Super Admin • Published Requests | '.config('app.name'),
            ]);
    }
}
