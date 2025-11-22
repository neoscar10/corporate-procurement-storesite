<?php

namespace App\Livewire\Company\Users;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Company\Company;
use App\Models\Company\CompanyMember;

class Index extends Component
{
    public Company $company;
    public bool $canInvite = false;

    public function mount(Company $company): void
    {
        $this->company = $company;

        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $companyId = (int) $this->company->id;

        // Same logic as your onboarding-success snippet
        $this->canInvite = CompanyMember::where('company_id', $companyId)
            ->where('user_id', (int) $user->id)
            ->where('role_label', 'CompanyAdmin')
            ->where('is_active', true)
            ->exists();

        // Fallbacks: direct permission or platform super-admin
        if (! $this->canInvite && method_exists($user, 'hasPermission')) {
            $this->canInvite = $user->hasPermission('manage_users') || (bool) $user->is_admin;
        }

        // Hard block if they shouldn’t even see the page
        if (! $this->canInvite) {
            abort(403);
        }
    }

    public function render()
    {
        $members = CompanyMember::with('user')
            ->where('company_id', $this->company->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.company.users.index', [
            'members' => $members,
        ])->layout('layouts.admin', ['title' => 'Company • Users | '.config('app.name')]);
    }

}

