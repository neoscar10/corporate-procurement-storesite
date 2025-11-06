<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Permission;
use App\Models\Company\Company;
use App\Models\Company\CompanyMember;
use App\Services\Support\NotificationService;
use App\Services\Support\PermissionsRegistry;

class UserProvisioningService
{
    public function __construct(
        protected NotificationService $notify,
        protected PermissionsRegistry $perms
    ) {}

    /**
     * First (primary) company user: provision as CompanyAdmin,
     * grant admin-style permissions via PermissionsRegistry,
     * and send credentials (plaintext) using NotificationService.
     *
     * @return array{user:\App\Models\User,password:string}
     */
    public function provisionFirstCompanyUser(Company $company): array
    {
        $email = optional($company->representative)->email;
        $name  = optional($company->representative)->full_name ?: 'Company Admin';

        [$user, $password] = $this->createOrResetUser($email, $name);

        CompanyMember::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['role_label' => 'CompanyAdmin', 'is_active' => true]
        );

        // Assign direct permissions using your registry
        $this->perms->grantCompanyAdmin($user);

        // Notify with generated credentials (your existing behavior)
        $this->notify->sendCredentials($user, $password);

        return compact('user', 'password');
    }

    /**
     * Subsequent company users (legacy flow):
     * provision with "User" baseline, grant default user perms,
     * and send credentials (plaintext).
     *
     * @return array{user:\App\Models\User,password:string}
     */
    public function provisionCompanyUser(Company $company, string $email, ?string $name = null): array
    {
        [$user, $password] = $this->createOrResetUser($email, $name ?: 'User');

        CompanyMember::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['role_label' => 'User', 'is_active' => true]
        );

        $this->perms->grantCompanyUser($user);

        // Keep legacy credential email for this path
        $this->notify->sendCredentials($user, $password);

        return compact('user', 'password');
    }

    /**
     * ✅ New invite flow:
     * 1) Create/reuse user (random password never shown),
     * 2) Attach selected permissions (user_permission.is_enabled = true),
     * 3) Ensure CompanyMember (CompanyUser),
     * 4) Send a password reset link so invitee sets their own password.
     *
     * @return array{user:\App\Models\User,reset_status:string}
     */
    public function inviteCompanyUserByPasswordReset(
        Company $company,
        string $email,
        array $permissionNames = [],
        ?string $name = null
    ): array {
        // Create or reuse user without exposing a password
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name ?: Str::before($email, '@'),
                'password' => Hash::make(Str::random(40)),
                'is_active'=> true,
            ]
        );

        // Attach selected permissions directly to the user (enabled)
        $this->attachSelectedPermissions($user, $permissionNames);

        // Ensure company membership as CompanyUser
        CompanyMember::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['role_label' => 'CompanyUser', 'is_active' => true]
        );

        // Send reset link (Laravel password broker)
        $status = Password::sendResetLink(['email' => $email]);

        return ['user' => $user, 'reset_status' => $status];
    }

    /**
     * Internal helper for legacy flows: creates or RESETS password for an existing user,
     * returns the user + the newly generated plaintext password (for emailing).
     *
     * @return array{0:\App\Models\User,1:string}
     */
    private function createOrResetUser(string $email, string $name): array
    {
        $password = Str::password(12);

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'is_active'=> true,
            ]
        );

        return [$user, $password];
    }

    /**
     * Attach arbitrary permission names as enabled on the user_permission pivot.
     */
    private function attachSelectedPermissions(User $user, array $permissionNames): void
    {
        $permissionNames = array_values(array_unique(array_filter($permissionNames)));
        if (empty($permissionNames)) {
            return;
        }

        $ids = Permission::whereIn('name', $permissionNames)->pluck('id')->all();
        if (empty($ids)) {
            return;
        }

        $payload = collect($ids)->mapWithKeys(
            fn ($id) => [$id => ['is_enabled' => true]]
        )->all();

        $user->permissions()->syncWithoutDetaching($payload);
    }

    public function inviteCompanyUserWithPassword(
    Company $company,
    string $email,
    array $permissionNames = [],
    ?string $plainPassword = null,
    ?string $name = null
): array {
    // If not provided, auto-generate a secure password
    $plain = $plainPassword ?: \Illuminate\Support\Str::password(12);

    // Create/reuse user WITH the chosen password
    $user = \App\Models\User::updateOrCreate(
        ['email' => $email],
        [
            'name'     => $name ?: \Illuminate\Support\Str::before($email, '@'),
            'password' => \Illuminate\Support\Facades\Hash::make($plain),
            'is_active'=> true,
        ]
    );

    // Attach selected permissions
    $this->attachSelectedPermissions($user, $permissionNames);

    // Ensure company membership as CompanyUser
    \App\Models\Company\CompanyMember::updateOrCreate(
        ['company_id' => $company->id, 'user_id' => $user->id],
        ['role_label' => 'CompanyUser', 'is_active' => true]
    );

    // Build a readable permissions list (labels preferred)
    $labels = [];
    if (!empty($permissionNames)) {
        $labels = \App\Models\Permission::whereIn('name', $permissionNames)
            ->get(['name','label'])
            ->map(fn($p) => $p->label ?: $p->name)
            ->values()
            ->all();
    }

    // Notify the user with credentials + permissions list
    if (method_exists($this->notify, 'sendCredentialsWithPermissions')) {
        $this->notify->sendCredentialsWithPermissions($user, $plain, $labels);
    } else {
        // Fallback to existing method if you haven’t added the new one yet
        $this->notify->sendCredentials($user, $plain);
    }

    return ['user' => $user, 'password' => $plain, 'permissions' => $labels];
}

}
