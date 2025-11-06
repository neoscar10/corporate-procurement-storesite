<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyPurchasePolicy extends Model
{
    protected $fillable = [
        'company_id', 'file_path', 'original_name',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
