<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsDetails extends Model
{
    protected $table = 'assets_details';
    public $timestamps = true;

     public function assets()
    {
        return $this->belongsTo(Assets::class,'assets_id','id');
    }

}
