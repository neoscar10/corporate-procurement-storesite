<?php

namespace App\Services\Verification;

use Illuminate\Support\Carbon;

class GovIdVerificationService
{
    // Placeholder: mark timestamps if values are present.
    public function verify(?string $cin, ?string $pan, ?string $gstin): array
    {
        $now = Carbon::now();
        return [
            'cin_verified_at'   => $cin   ? $now : null,
            'pan_verified_at'   => $pan   ? $now : null,
            'gstin_verified_at' => $gstin ? $now : null,
        ];
    }
}
