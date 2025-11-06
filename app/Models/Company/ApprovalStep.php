<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_workflow_id',
        'position',              // 1..n
        'label',                 // e.g., Manager, CFO
        'approver_user_id',      // optional fixed user
        'approver_role',         // or role label
        'threshold_amount',      // optional per-step rule
    ];

    protected $casts = ['threshold_amount' => 'decimal:2'];

    public function workflow() { return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id'); }
}
