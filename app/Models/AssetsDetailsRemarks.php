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
        'item_no',
        'condition_remarks',
    ];
     public function assets_details()
    {
        return $this->belongsTo(AssetsDetails::class,'asset_detail_id','id');
    } 
    public function personalInformation()
    {
        return $this->hasOne(EmploymentPersonalInformation::class, 'user_id','asset_holder_id');
    }

}
