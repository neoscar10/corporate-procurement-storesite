<?php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ServiceSpec extends Model {
  protected $table = 'procurement_service_specs';
  protected $fillable = ['procurement_item_id','scope_of_work','deliverables','key_personnels'];
  protected $casts = ['deliverables'=>'array','key_personnels'=>'array'];
  public function item(){ return $this->belongsTo(ProcurementItem::class,'procurement_item_id'); }
}
