<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsDetailsRemarks extends Model
{
    protected $table = 'assets_details_remarks';
    public $timestamps = true;

    protected $fillable = [
        'asset_detail_id',
        'asset_holder_id',
        'remarks_from',
        'condition_remarks',
    ];
     public function assets_details()
    {
        return $this->belongsTo(AssetsDetails::class,'asset_detail_id','id');
    } 
}
