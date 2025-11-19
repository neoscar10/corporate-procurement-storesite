<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

// Optional type-hints for relations (safe to keep)
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Company\CompanyMember;
use App\Models\Company\Company;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name','username','email','phone','password',
        'role_id','is_admin','is_active',
        'two_factor_enabled','two_factor_secret','two_factor_recovery_codes',
        'avatar_path','timezone','locale',
        'auth_provider','auth_provider_id',
        'last_login_at','last_password_change_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password','remember_token',
        'two_factor_secret','two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at'        => 'datetime',
        'last_login_at'            => 'datetime',
        'last_password_change_at'  => 'datetime',
        'is_admin'                 => 'boolean',
        'is_active'                => 'boolean',
        'two_factor_enabled'       => 'boolean',
        'password'                 => 'hashed',
        'is_vendor' => 'boolean',
    ];

    // roles/permissions
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }

    public function hasPermission(string $name): bool
    {
        if ($this->is_admin) return true;
        return $this->permissions()
            ->where('name', $name)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /** ---------------------------
     * Company membership relations
     * -------------------------- */

    /** All company memberships for this user */
    public function companyMembers(): HasMany
    {
        return $this->hasMany(CompanyMember::class);
    }

    /** Latest active membership (if any) */
    public function activeCompanyMember(): HasOne
    {
        return $this->hasOne(CompanyMember::class)
            ->where('is_active', true)
            ->latestOfMany();
    }

    /** Convenience: companies via members pivot */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_members')
            ->withPivot(['role_label','department','is_active'])
            ->withTimestamps();
    }

    /** Helper: is this user a Company Admin for a given company? */
    public function isCompanyAdminFor(int $companyId): bool
    {
        // 1) Membership role check
        $viaRole = $this->companyMembers()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('role_label', ['CompanyAdmin','company_admin','admin','owner'])
            ->exists();

        // 2) Direct permission (per your saved convention)
        $viaPerm = method_exists($this, 'hasPermission') && $this->hasPermission('company_admin');

        // 3) Super admin shortcut
        return $viaRole || $viaPerm || (bool) ($this->is_admin ?? false);
    }

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->is_admin && class_exists(\App\Models\Permission::class)) {
                $ids = \App\Models\Permission::pluck('id');
                if ($ids->isNotEmpty()) {
                    $user->permissions()->sync(
                        $ids->mapWithKeys(fn ($id) => [$id => ['is_enabled' => true]])->all()
                    );
                }
            }
        });
    }
    public function vendor()
    {
        return $this->hasOne(\App\Models\Vendor\Vendor::class);
    }
}
