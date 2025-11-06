<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyCertification extends Model
{
    // type: iso|esg|government_registration|license|other
    protected $fillable = [
        'company_id', 'type', 'code',
        'name', 'issuer',
        'valid_from', 'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
