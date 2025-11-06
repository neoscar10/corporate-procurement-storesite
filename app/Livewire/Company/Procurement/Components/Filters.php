<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Component;

class Filters extends Component
{
    public string $search = '';
    public string $status = 'all';
    public string $type   = 'all';
    public ?string $from  = null;
    public ?string $to    = null;
    public int $perPage   = 10;
    public bool $canCreate = false;

    public function updated($field)
    {
        $this->dispatch('filters-changed', filters: [
            'search'=>$this->search,
            'status'=>$this->status,
            'type'=>$this->type,
            'from'=>$this->from,
            'to'=>$this->to,
            'perPage'=>$this->perPage,
        ]);
    }

    public function render() { return view('livewire.company.procurement.components.filters'); }
}
