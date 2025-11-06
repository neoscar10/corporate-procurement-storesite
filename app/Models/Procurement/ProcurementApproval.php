<?php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProcurementApproval extends Model {
  protected $fillable = ['procurement_request_id','approver_id','status','approved_at','rejected_at','comment'];
  protected $casts = ['approved_at'=>'datetime','rejected_at'=>'datetime'];
  public function request(){ return $this->belongsTo(ProcurementRequest::class,'procurement_request_id'); }
  public function approver(){ return $this->belongsTo(User::class,'approver_id'); }
}
