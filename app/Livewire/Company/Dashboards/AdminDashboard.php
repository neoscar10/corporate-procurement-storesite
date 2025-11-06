<?php

namespace App\Livewire\Company\Dashboards;

use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.company.dashboards.admin-dashboard')->layout('layouts.admin');
    }
}
