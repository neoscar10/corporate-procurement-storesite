<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanyKycDocument extends Model
{
    // document_type: incorporation|pan|gst|udyam|bank_cheque|auth_letter|other
    protected $fillable = [
        'company_id', 'document_type', 'file_path', 'original_name',
        'verified_at', 'notes',
    ];

    protected $casts = ['verified_at' => 'datetime'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Public URL to the stored file.
     * - Absolute http(s) path: returned as-is
     * - /storage/... path: appended to APP_URL
     * - relative path: resolved via public disk (storage/app/public)
     */
    public function getPublicUrlAttribute(): ?string
    {
        $p = (string) ($this->file_path ?? '');
        if ($p === '') {
            return null;
        }

        // Absolute URL already
        if (preg_match('~^https?://|^//~i', $p)) {
            return $p;
        }

        // Already a /storage/... path
        if (str_starts_with($p, '/storage/')) {
            return url($p);
        }

        // Normal case: relative path on the public disk
        return Storage::disk('public')->url($p);
    }

    /**
     * Display name fallback.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->original_name ?: basename((string) $this->file_path);
    }
}
