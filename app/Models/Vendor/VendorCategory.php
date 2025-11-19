<?php

namespace App\Models\Vendor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kind', 'name', 'slug', 'order', 'is_active', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
