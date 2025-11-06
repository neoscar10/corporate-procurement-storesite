<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    protected $fillable = [
        'company_id', 'type', // registered|corporate|billing
        'line1', 'line2', 'city', 'state', 'pin_code', 'country',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
