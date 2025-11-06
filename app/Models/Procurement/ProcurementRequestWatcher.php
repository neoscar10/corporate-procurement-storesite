<?php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ProcurementRequestWatcher extends Model
{
    protected $fillable = ['procurement_request_id','user_id'];
    public function request(){ return $this->belongsTo(ProcurementRequest::class,'procurement_request_id'); }
}
