<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyPlatformSetting extends Model
{
    protected $fillable = [
        'company_id',
        'communication_preference', // email|sms|whatsapp
        'preferred_language',       // e.g., en|hi|…
        'notification_frequency',   // immediate|daily
        'data_sharing_consent',     // bool
        'terms_accepted_at',
    ];

    protected $casts = [
        'data_sharing_consent' => 'boolean',
        'terms_accepted_at'    => 'datetime',
    ];

    public function company() { return $this->belongsTo(Company::class); }
}
