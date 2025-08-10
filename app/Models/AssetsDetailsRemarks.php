<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsDetailsRemarks extends Model
{
    protected $table = 'assets_details_remarks';
    public $timestamps = true;

     public function assets_details()
    {
        return $this->belongsTo(AssetsDetails::class,'asset_detail_id','id');
    } 
}
