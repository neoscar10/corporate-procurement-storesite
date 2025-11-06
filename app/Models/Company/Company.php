<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legal_name', 'brand_name',
        'cin', 'pan', 'gstin',
        'organization_type', 'industry', 'nature_of_business',
        'cin_verified_at', 'pan_verified_at', 'gstin_verified_at',
        'status','status_reason','status_changed_at',
    ];

    protected $casts = [
        'cin_verified_at' => 'datetime',
        'pan_verified_at' => 'datetime',
        'gstin_verified_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'resubmission_count' => 'integer',
    ];

    public function onboardingProgress()
    {
        return $this->hasOne(CompanyOnboardingProgress::class);
    }

    // Contacts & addresses
    public function contact() { return $this->hasOne(CompanyContact::class); }
    public function addresses() { return $this->hasMany(CompanyAddress::class); }

    // Primary representative
    public function representative() { return $this->hasOne(CompanyRepresentative::class); }

    // Procurement preferences & categories
    public function preference() { return $this->hasOne(ProcurementPreference::class); }
    public function categories() {
        return $this->belongsToMany(ProcurementCategory::class, 'company_procurement_category')
                    ->withTimestamps();
    }

    // Workflows
    public function workflows() { return $this->hasMany(ApprovalWorkflow::class); }

    // Documents & policy
    public function kycDocuments() { return $this->hasMany(CompanyKycDocument::class); }
    public function purchasePolicies() { return $this->hasMany(CompanyPurchasePolicy::class); }

    // Banking, certifications, members, settings
    public function bankAccounts() { return $this->hasMany(CompanyBankAccount::class); }
    public function certifications() { return $this->hasMany(CompanyCertification::class); }
    public function members() { return $this->hasMany(CompanyMember::class); }
    public function platformSetting() { return $this->hasOne(CompanyPlatformSetting::class); }
}
