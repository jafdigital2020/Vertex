<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleAccess extends Model
{
    use HasFactory;

    protected $table = 'role_access';
     public $timestamps = true;
 
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

}
