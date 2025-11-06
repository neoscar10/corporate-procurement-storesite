<?php

namespace App\Livewire\Auth\Invite;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Permission;
use App\Models\Company\Company;
use App\Services\Auth\UserProvisioningService;

class InviteUserModal extends Component
{
    public bool $open = false;
    public ?int $companyId = null;

    public string $email = '';
    public bool $showPermissions = false;

    // NEW:
    public bool $autoPassword = true;
    public string $password = '';
    public string $password_confirmation = '';

    /** @var array<int, array{id:int,name:string,label:string}> */
    public array $available = [];

    /** @var array<string,bool> keyed by permission name */
    public array $selected = [];

    protected function rules(): array
    {
        return [
            'email' => ['required','email','max:255'],
            'autoPassword' => ['boolean'],

            // Only required if autoPassword = false; exclude from validation otherwise
            'password' => ['exclude_if:autoPassword,true','required','string','min:8','confirmed'],
            'password_confirmation' => ['exclude_if:autoPassword,true','required','string','min:8'],
        ];
    }

    #[On('invite.open')]
    public function open(int $companyId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->companyId = $companyId;
        $this->email = '';
        $this->showPermissions = false;
        $this->autoPassword = true;
        $this->password = '';
        $this->password_confirmation = '';

        $perms = Permission::query()->orderBy('label')->get(['id','name','label']);
        $this->available = $perms->map(fn($p) => [
            'id' => (int)$p->id,
            'name' => (string)$p->name,
            'label' => (string)($p->label ?: $p->name),
        ])->all();

        $this->selected = [];
        $this->open = true;
    }

    public function togglePermission(string $name): void
    {
        $this->selected[$name] = !($this->selected[$name] ?? false);
    }

    public function selectAll(): void
    {
        foreach ($this->available as $p) {
            $this->selected[$p['name']] = true;
        }
    }

    public function clearAll(): void
    {
        $this->selected = [];
    }

    public function submit(UserProvisioningService $svc): void
    {
        $this->validate();

        $company = Company::findOrFail((int)$this->companyId);
        $names   = collect($this->selected)->filter()->keys()->values()->all();

        // If auto, pass null to generate; else use provided password
        $plain = $this->autoPassword ? null : $this->password;

        $svc->inviteCompanyUserWithPassword($company, $this->email, $names, $plain);

        $this->open = false;
        $this->reset(['email','selected','showPermissions','autoPassword','password','password_confirmation']);

        $msg = 'Invitation sent. Login credentials have been emailed to the user (including their permissions).';

        // toast + on-page banner (no reload)
        $this->dispatch('toast', type: 'success', message: $msg);
        $this->dispatch('banner', type: 'success', message: $msg);
    }

    public function render()
    {
        return view('livewire.auth.invite.invite-user-modal');
    }
}
