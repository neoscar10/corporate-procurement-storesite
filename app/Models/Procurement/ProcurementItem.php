<?php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementItem extends Model {
  use SoftDeletes;

  protected $fillable = [
    'procurement_request_id','company_id','kind','name','short_description',
    'priority','unit','date_required','budget_amount',
    'service_budget_mode','service_payment_type',
    'is_draft','detail_completed_at','spec_completed_at','attachments_completed_at','completed_at','status', 'quantity',
  ];

  protected $casts = [
    'quantity' => 'integer',
    'date_required'=>'date',
    'is_draft'=>'bool',
    'detail_completed_at'=>'datetime',
    'spec_completed_at'=>'datetime',
    'attachments_completed_at'=>'datetime',
    'completed_at'=>'datetime',
  ];

  public function request(){ return $this->belongsTo(ProcurementRequest::class,'procurement_request_id'); }
  public function productSpec(){ return $this->hasOne(ProductSpec::class,'procurement_item_id'); }
  public function serviceSpec(){ return $this->hasOne(ServiceSpec::class,'procurement_item_id'); }
  public function attachments(){ return $this->morphMany(\App\Models\Attachment::class,'attachable'); }
}
