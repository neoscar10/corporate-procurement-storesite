<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyRepresentative extends Model
{
    protected $fillable = [
        'company_id', 'full_name', 'designation',
        'email', 'mobile',
        'govt_id_type', 'govt_id_number',
        'signature_path',
        'user_id', // optional link to users table
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
