<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class ProcurementCategory extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function companies() {
        return $this->belongsToMany(Company::class, 'company_procurement_category')->withTimestamps();
    }
}
