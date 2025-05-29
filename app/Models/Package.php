<?php

namespace App\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';
    protected $fillable = [
        'package_name',
        'pricing',
        'employee_limit',
        'status', 
    ];
    public $timestamps = true;
   public function pack_type()
    {
        return $this->hasOne(Package_Type::class, 'id','package_type_id');
    }
}
