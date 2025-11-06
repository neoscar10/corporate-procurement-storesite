<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\ProcurementRequest;
use App\Models\Company\CompanyMember;

class Table extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $type   = 'all';
    public ?string $from  = null;
    public ?string $to    = null;
    public int $perPage   = 10;

    protected $listeners = ['table-refresh' => '$refresh'];

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
