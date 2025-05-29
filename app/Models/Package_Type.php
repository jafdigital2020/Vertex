<?php

namespace App\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package_Type extends Model
{
    use HasFactory;

    protected $table = 'packages_type';
    protected $fillable = [
        'package_type', 
    ];
    public $timestamps = true;
 
}
