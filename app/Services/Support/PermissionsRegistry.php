<?php

namespace App\Services\Support;

use App\Models\User;
use App\Models\Permission;

class PermissionsRegistry
{
    // Map the sets you want to grant. Replace with your real list later.
    protected array $companyAdmin = [
        'create_procurement','approve_procurement','manage_company_members',
        'upload_kyc','manage_bank_accounts','manage_workflows',
    ];

    protected array $companyUser = [
        'create_procurement','upload_kyc',
    ];

    public function grantCompanyAdmin(User $user): void
    {
        $ids = Permission::whereIn('name', $this->companyAdmin)->pluck('id')->all();
        $this->syncWithEnabled($user, $ids);
    }

    public function grantCompanyUser(User $user): void
    {
        $ids = Permission::whereIn('name', $this->companyUser)->pluck('id')->all();
        $this->syncWithEnabled($user, $ids, append: true);
    }

    private function syncWithEnabled(User $user, array $permissionIds, bool $append = false): void
    {
        $payload = collect($permissionIds)
            ->mapWithKeys(fn ($id) => [$id => ['is_enabled' => true]])
            ->all();

        if ($append) {
            $user->permissions()->syncWithoutDetaching($payload);
        } else {
            $user->permissions()->sync($payload, false);
        }
    }
}
