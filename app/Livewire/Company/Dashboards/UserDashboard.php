<?php

namespace App\Livewire\Company\Dashboards;

use Livewire\Component;

class UserDashboard extends Component
{
    public function render()
    {
        return view('livewire.company.dashboards.user-dashboard')->layout('layouts.admin');
    }
}
