<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class ProcurementPreference extends Model
{
    protected $fillable = [
        'company_id',
        'avg_monthly_budget',
        'procurement_type',   // goods|services|both
        'frequency',          // monthly|quarterly|annual|ad-hoc
        'preferred_payment_terms',
        'preferred_vendor_locations', // json string of regions/cities
    ];

    protected $casts = [
        'preferred_vendor_locations' => 'array',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
