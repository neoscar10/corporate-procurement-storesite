<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $fillable = [
        'company_id',
        'official_email', 'alternate_email',
        'primary_phone', 'contact_mobile', 'website',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
