<?php

namespace App\Livewire\Admin\Procurement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\ProcurementService;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string  $search  = '';
    public int     $perPage = 15;
    public ?string $status  = 'published'; // default: published

    // Keep filters in URL (like other working pages)
    protected $queryString = [
        'search'  => ['except' => ''],
        'status'  => ['except' => 'published'],
        'perPage' => ['except' => 15],
        'page'    => ['except' => 1],
    ];

    // Reset page when filters change
    public function updating($name, $value): void
    {
        if (in_array($name, ['search','status','perPage'], true)) {
            $this->resetPage();
        }
    }

    public function render(ProcurementService $svc)
    {
        $q = $svc->queryRequests()
            // Status filter (nullable => all)
            ->when($this->status !== null && $this->status !== '', function (Builder $b) {
                $status = strtolower($this->status);

                // treat "canceled" as "cancelled"
                if ($status === 'canceled') {
                    $status = 'cancelled';
                }

                $b->where('status', $status);
            })
            // Search: by PR id, title, company
            ->when($this->search !== '', function (Builder $b) {
                $search = trim($this->search);
                $like   = '%'.$search.'%';

                $b->where(function (Builder $q) use ($search, $like) {
                    // numeric search -> PR id
                    if (ctype_digit($search)) {
                        $q->where('id', (int) $search);
                    }

                    $q->orWhere('title', 'like', $like)
                      ->orWhereHas('company', function (Builder $q2) use ($like) {
                          $q2->where('brand_name', 'like', $like)
                             ->orWhere('legal_name', 'like', $like);
                      });
                });
            })
            ->orderByDesc('id');

        $rows = $q->paginate($this->perPage)->withQueryString();

        return view('livewire.admin.procurement.index', compact('rows'))
            ->layout('layouts.admin', [
                'title' => 'Super Admin • Procurement Requests | '.config('app.name'),
            ]);
    }
}
