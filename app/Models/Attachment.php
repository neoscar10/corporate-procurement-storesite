<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model {
  protected $fillable=['attachable_type','attachable_id','company_id','disk','path','original_name','mime','size_bytes','url'];
  public function attachable(){ return $this->morphTo(); }
}
