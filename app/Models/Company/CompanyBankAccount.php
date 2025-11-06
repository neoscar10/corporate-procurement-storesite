<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyBankAccount extends Model
{
    protected $fillable = [
        'company_id',
        'bank_name', 'branch',
        'account_holder_name', 'account_number', 'ifsc',
        'preferred_payment_method', // NEFT|RTGS|UPI|IMPS|Cheque
        'credit_term',              // e.g., "Net 30"
        'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
}
