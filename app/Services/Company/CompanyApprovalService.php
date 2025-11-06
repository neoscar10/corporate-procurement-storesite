<?php

namespace App\Services\Company;

use App\Models\Company\Company;
use Illuminate\Support\Carbon;

class CompanyApprovalService
{
    public function setStatus(Company $company, string $status, ?string $reason = null): Company
    {
        $status = strtolower($status);
        if (!in_array($status, ['pending','approved','rejected','cancelled'], true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        // >>> add count when resubmitting to track
        $previous = $company->status;
        if ($previous === 'rejected' && $status === 'pending') {
            // safe even if null: cast to int, then increment
            $company->resubmission_count = (int) $company->resubmission_count + 1;
        }
        // <<<

        $company->fill([
            'status'            => $status,
            'status_reason'     => $reason,
            'status_changed_at' => Carbon::now(),
        ])->save();

        return $company->fresh();
    }
}
