<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyOnboardingProgress extends Model
{
    protected $table = 'company_onboarding_progress';
    protected $fillable = [
        'company_id','procurement_done','procurement_done_at',
        'kyc_done','kyc_done_at','billing_done','billing_done_at','completed_at'
    ];
    protected $casts = [
        'procurement_done' => 'bool',
        'kyc_done' => 'bool',
        'billing_done' => 'bool',
        'procurement_done_at' => 'datetime',
        'kyc_done_at' => 'datetime',
        'billing_done_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
