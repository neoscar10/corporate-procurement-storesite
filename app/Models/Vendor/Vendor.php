<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id','name','email','phone','company_name',
        'provides_products','provides_services','is_active',
    ];

    protected $casts = [
        'provides_products' => 'boolean',
        'provides_services' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(VendorCategory::class, 'vendor_category_vendor')
            ->withTimestamps();
    }
}
