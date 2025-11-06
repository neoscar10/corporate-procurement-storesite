<?php

namespace App\Observers;

use App\Models\Company\Company;
use Illuminate\Support\Facades\DB;

class CompanyObserver
{
    public function updated(Company $company): void
    {
        if (! $company->wasChanged('status')) {
            return;
        }

        $from = $company->getOriginal('status');
        $to   = $company->status;

        // Count only resubmissions after a rejection
        if ($from === 'rejected' && $to === 'pending') {
            // Raw increment to avoid re-triggering model events
            DB::table('companies')
                ->where('id', $company->id)
                ->update(['resubmission_count' => DB::raw('resubmission_count + 1')]);
        }
    }
}
