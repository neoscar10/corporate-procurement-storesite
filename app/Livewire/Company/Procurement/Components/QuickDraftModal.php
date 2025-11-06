<?php

namespace App\Livewire\Company\Procurement\Components;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuickDraftModal extends Component
{
    public bool $show = false;
    public string $title = '';
    public string $type = 'REQ';       // RFI|REQ|PO|RFP
    public string $priority = 'normal';// low|normal|high

    protected $rules = [
        'title'    => ['required','string','min:3','max:190'],
        'type'     => ['required','in:RFI,REQ,PO,RFP'],
        'priority' => ['required','in:low,normal,high'],
    ];

    #[On('open-quick-draft')]
    public function open(): void
    {
        $this->resetValidation();
        $this->title    = '';
        $this->type     = 'REQ';
        $this->priority = 'normal';
        $this->show     = true;

        $this->dispatch('quick-draft:show'); // tell Bootstrap to open
    }

    public function create()
    {
        $this->validate();
        // ... create logic as before ...
        $this->show = false;
        $this->dispatch('quick-draft:hide'); // close before redirect (optional)
        session()->flash('success', 'Draft created. You can continue filling it out.');
        return redirect()->to("/procure/requests/{$id}");
    }

    public function render()
    {
        return view('livewire.company.procurement.components.quick-draft-modal');
    }
}
