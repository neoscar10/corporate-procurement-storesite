<?php
namespace App\Models\Procurement;

use App\Models\User;
use App\Models\Company\Company;
use App\Enums\Procurement\{RequestType,Priority,RequestStatus};
use Illuminate\Database\Eloquent\{Model,SoftDeletes};
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProcurementRequest extends Model {
  use SoftDeletes;

  protected $fillable = [
    'company_id','created_by','title','type','priority',
    'desired_response_at','expected_delivery_at',
    'currency','budget_min','budget_max','payment_terms',
    'delivery_location','preferred_vendor_region','notes',
    'status','stage','items_count','attachments_count','approved_at','published_at'
  ];

  protected $casts = [
    'type' => RequestType::class,
    'priority' => Priority::class,
    'status' => RequestStatus::class,
    'desired_response_at' => 'datetime',
    'expected_delivery_at' => 'datetime',
    'approved_at' => 'datetime',
    'published_at' => 'datetime',
  ];

  // relations
  public function company(){ return $this->belongsTo(Company::class); }
  public function creator(){ return $this->belongsTo(User::class,'created_by'); }
  public function items(){ return $this->hasMany(ProcurementItem::class); }
  public function approvals(){ return $this->hasMany(ProcurementApproval::class); }
  public function attachments(){ return $this->morphMany(\App\Models\Attachment::class,'attachable'); }

  public function watchers() {
    return $this->hasMany(\App\Models\Procurement\ProcurementRequestWatcher::class);
}

  // helpers
  public function isFullyApproved(): bool {
    return $this->approvals()->where('status','pending')->count() === 0
        && $this->status === RequestStatus::APPROVED;
  }
}
