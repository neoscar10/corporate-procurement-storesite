<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\CompanyMember;
use App\Models\Procurement\ProcurementRequest;
use Illuminate\Support\Str; 


class Table extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $type   = 'all';
    public ?string $from  = null;
    public ?string $to    = null;
    public int $perPage   = 10;
    public string $domId;

    /** Holds the row we’re about to delete */
    public ?int $confirmingDeleteId = null;

    protected $listeners = ['table-refresh' => '$refresh'];

    public function mount(): void
    {
        // stable per component instance
        $this->domId = 'pr-table-'.Str::uuid()->toString();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
        // Open Bootstrap modal via JS (teleport-safe)
        $this->dispatch('table:confirm-delete:open');
    }

    public function deleteConfirmed(): void
    {
        $this->deleteDraft($this->confirmingDeleteId);
    }

    public function deleteDraft(?int $id): void
    {
        if (! $id) return;

        $user = Auth::user();
        $companyId = CompanyMember::where('user_id', $user->id)
            ->where('is_active', true)
            ->value('company_id');

        $row = ProcurementRequest::query()
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first(['id','status']);

        if (! $row) {
            session()->flash('error', 'Request not found or not allowed.');
            $this->dispatch('table:confirm-delete:close');
            return;
        }

        $statusRaw = $row->status instanceof \BackedEnum ? strtolower($row->status->value) : strtolower((string) $row->status);
        if (! in_array($statusRaw, ['draft','cancelled','canceled'], true)) {
            session()->flash('error', 'Only drafts or cancelled requests can be deleted.');
            $this->dispatch('table:confirm-delete:close');
            return;
        }

        try {
            $row->delete();
            session()->flash('success', 'Request deleted.');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Unable to delete. Remove related items/attachments or enable ON DELETE CASCADE.');
            $this->dispatch('table:confirm-delete:close');
            return;
        }

        $this->confirmingDeleteId = null;
        $this->dispatch('table:confirm-delete:close');

        // Ensure pagination doesn’t get stuck on an empty page
        $this->resetPage();
        $this->dispatch('table-refresh');
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = CompanyMember::where('user_id',$user->id)->where('is_active',true)->value('company_id');

        $q = ProcurementRequest::query()->where('company_id', $companyId);

        if ($this->search !== '') {
            $s = '%'.$this->search.'%';
            $q->where(function($qq) use ($s){
                $qq->where('title','like',$s)
                   ->orWhere('id','like',$s);
            });
        }
        if ($this->status !== 'all') $q->where('status',$this->status);
        if ($this->type !== 'all')   $q->where('type',$this->type);
        if ($this->from) $q->whereDate('created_at','>=',$this->from);
        if ($this->to)   $q->whereDate('created_at','<=',$this->to);

        $rows = $q->orderByDesc('id')->paginate($this->perPage);

        return view('livewire.company.procurement.components.table', [
            'rows' => $rows
        ]);
    }
}
