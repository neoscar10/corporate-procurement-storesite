<?php
namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

class ProductSpec extends Model {
  protected $table = 'procurement_product_specs';
  protected $fillable = ['procurement_item_id','brand','model','quality_level','packaging_requirement','inspection_required','technical_specs'];
  protected $casts = ['technical_specs'=>'array','inspection_required'=>'bool'];
  public function item(){ return $this->belongsTo(ProcurementItem::class,'procurement_item_id'); }
}
