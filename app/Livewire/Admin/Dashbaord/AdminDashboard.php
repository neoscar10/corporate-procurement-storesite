<?php

namespace App\Livewire\Admin\Dashbaord;

use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashbaord.admin-dashboard')->layout('layouts.admin', ['title' => 'Admin Dashboard • '.config('app.name')]);
    }
}
