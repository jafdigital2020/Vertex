<?php

namespace App\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package_FeatureDetails extends Model
{
    use HasFactory;

    protected $table = 'packages_features_details';
    protected $fillable = [
        'type', 
    ];
    public $timestamps = true;
   
}
