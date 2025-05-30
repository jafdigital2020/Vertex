<?php

namespace App\Models;

use App\Models\Organization; 
use App\Models\Package_FeatureDetails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package_Feature extends Model
{
    use HasFactory;

    protected $table = 'packages_features';
    protected $fillable = [
        'feature', 
    ];
    public $timestamps = true;
   
    public function feature_det()
    {
        return $this->hasMany(Package_FeatureDetails::class, 'feature_id','id');
    }


}
