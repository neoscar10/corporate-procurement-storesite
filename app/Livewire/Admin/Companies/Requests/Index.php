<?php

namespace App\Livewire\Admin\Companies\Requests;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    // Filters
    public string  $search    = '';
    public string  $status    = 'all'; // default to ALL
    public ?string $from      = null;  // Y-m-d
    public ?string $to        = null;  // Y-m-d
    public int     $perPage   = 15;

    // Sorting
    public string $sortField  = 'companies.created_at';
    public string $sortDir    = 'desc';

    // Keep state in the URL (clean defaults)
    protected $queryString = [
        'search'    => ['except' => ''],
        'status'    => ['except' => 'all'],
        'from'      => ['except' => null],
        'to'        => ['except' => null],
        'perPage'   => ['except' => 15],
        'sortField' => ['except' => 'companies.created_at'],
        'sortDir'   => ['except' => 'desc'],
        'page'      => ['except' => 1],
    ];

    public function updating($name, $value): void
    {
        if (in_array($name, ['search','status','from','to','perPage'], true)) {
            $this->resetPage();
        }
    }

    public function updatedStatus($value): void
    {
        // Guard against unexpected values
        $allowed = ['pending','approved','rejected','cancelled','all'];
        if (! in_array($value, $allowed, true)) {
            $this->status = 'all';
        }
    }

    public function resetFilters(): void
    {
        $this->search   = '';
        $this->status   = 'all';
        $this->from     = null;
        $this->to       = null;
        $this->perPage  = 15;
        $this->sortField = 'companies.created_at';
        $this->sortDir   = 'desc';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $q = Company::query()
            ->with(['onboardingProgress'])
            // Status filter (skip when "all")
            ->when($this->status !== 'all', fn (Builder $b) => $b->where('status', $this->status))
            // Search across key identity fields
            ->when($this->search !== '', function (Builder $b) {
                $s = '%' . $this->search . '%';
                $b->where(function (Builder $q) use ($s) {
                    $q->where('legal_name', 'like', $s)
                      ->orWhere('brand_name', 'like', $s)
                      ->orWhere('cin', 'like', $s)
                      ->orWhere('pan', 'like', $s)
                      ->orWhere('gstin', 'like', $s);
                });
            })
            // Date range (created_at on companies)
            ->when($this->from, fn (Builder $b) => $b->whereDate('companies.created_at', '>=', $this->from))
            ->when($this->to,   fn (Builder $b) => $b->whereDate('companies.created_at', '<=', $this->to))
            // Sorting
            ->orderBy($this->sortField, $this->sortDir);

        $companies = $q->paginate($this->perPage);

        return view('livewire.admin.companies.requests.index', compact('companies'))
            ->layout('layouts.admin', ['title' => 'Company Requests • ' . config('app.name')]);
    }
}
