<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'company_id', 'name', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
    public function steps() { return $this->hasMany(ApprovalStep::class)->orderBy('position'); }
}
