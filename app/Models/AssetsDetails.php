<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsDetails extends Model
{
    protected $table = 'assets_details';
    public $timestamps = true;

    protected $fillable = [
        'asset_id',
        'deployed_to',
        'deployed_date',
        'order_no',
        'asset_condition',
        'status',  
    ];

     public function assets()
    {
        return $this->belongsTo(Assets::class,'asset_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'deployed_to');  
    }
    public function remarks(){

        return $this->hasMany(AssetsDetailsRemarks::class, 'asset_holder_id','deployed_to');
    }
}
