<?php

namespace App\Livewire\Admin\Procurement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\ProcurementService;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;
    public ?string $status = 'published'; // default to published for SA

    public function updatingSearch()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }
    public function updatingStatus()  { $this->resetPage(); }

    public function render(ProcurementService $svc)
    {
        $rows = $svc->listRequests([
            'search' => $this->search,
            'status' => $this->status ?: null,
        ], $this->perPage);

        return view('livewire.admin.procurement.index', compact('rows'))
            ->layout('layouts.admin', [
                'title' => 'Super Admin • Procurement Requests | '.config('app.name'),
            ]);
    }
}
