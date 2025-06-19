<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';
    protected $fillable = [
        'role_name',
        'status',
    ];
    public $timestamps = true;
 
    public function data_access_level()
    {
        return $this->hasOne(DataAccessLevel::class, 'id', 'data_access_id');
    }

}
