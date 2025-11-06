<?php

namespace App\Livewire\Company\Procurement;

use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Company\CompanyMember;
use App\Models\User;

class Index extends Component
{
    public string $search = '';
    public string $status = 'all';
    public string $type   = 'all';
    public ?string $from  = null;
    public ?string $to    = null;
    public int $perPage   = 10;

    public ?int $deleteId = null;

    public function getCanCreateProperty(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        if ((bool) ($user->is_admin ?? false)) return true;
        if ($this->isCompanyAdmin($user)) return true;

        return method_exists($user, 'hasPermission') && $user->hasPermission('create_procurement');
    }

    /** Open the 2-step creation wizard (no DB write yet) */
   public function openCreate(): void
    {
        if (! $this->canCreate) {
            session()->flash('error', 'You do not have permission to create a request.');
            return;
        }

        // For server-side Livewire listeners (CreateWizard::openStep1)
        $this->dispatch('open-create-wizard')
            ->to('company.procurement.create-wizard'); // target by alias

        // For browser-listening modal open (Bootstrap)
        $this->dispatch('open-create-step1');
    }



    #[On('filters-changed')]
    public function updateFilters(array $filters): void
    {
        $this->search  = (string)($filters['search'] ?? '');
        $this->status  = (string)($filters['status'] ?? 'all');
        $this->type    = (string)($filters['type']   ?? 'all');
        $this->from    = $filters['from'] ?? null;
        $this->to      = $filters['to']   ?? null;
        $this->perPage = (int)($filters['perPage'] ?? 10);
    }

    #[On('request-delete-ask')]
    public function confirmDelete(int $id): void { $this->deleteId = $id; }

    public function deleteDraft(): void
    {
        if (! $this->deleteId) return;

        $user = Auth::user();
        $companyId = CompanyMember::where('user_id', $user->id)->where('is_active', true)->value('company_id');

        $row = DB::table('procurement_requests')
            ->where('id', $this->deleteId)
            ->where('company_id', $companyId)
            ->first(['id','status']);

        if (! $row) {
            $this->deleteId = null;
            session()->flash('error', 'Request not found or not allowed.');
            return;
        }

        $st = strtolower((string) $row->status);
        if (! in_array($st, ['draft','cancelled','canceled'], true)) {
            $this->deleteId = null;
            session()->flash('error', 'Only drafts or cancelled requests can be deleted.');
            return;
        }

        DB::table('procurement_requests')->where('id', $this->deleteId)->delete();
        $this->deleteId = null;
        session()->flash('success', 'Request deleted.');
        $this->dispatch('table-refresh');
    }

    public function render()
    {
        return view('livewire.company.procurement.index')
            ->layout('layouts.admin', ['title' => 'Procurement • Requests | '.config('app.name')]);
    }

    private function activeMembership(User $user): ?CompanyMember
    {
        return CompanyMember::where('user_id', $user->id)->where('is_active', true)
            ->latest('id')->first(['id','company_id','user_id','role_label']);
    }

    private function isCompanyAdmin(User $user): bool
    {
        $m = $this->activeMembership($user);
        if (! $m) return false;

        $label = strtolower((string) ($m->role_label ?? ''));
        if (in_array($label, ['company_admin','company-admin','admin','owner'], true)) return true;

        $first = CompanyMember::where('company_id', $m->company_id)->where('is_active', true)
            ->orderBy('id','asc')->first(['user_id']);

        return (int) ($first->user_id ?? 0) === (int) $user->id;
    }
}
