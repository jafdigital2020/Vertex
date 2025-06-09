<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permission'; 
    public $timestamps = true;
   
    public function role()
    {
        return $this->hasOne( Role::class, 'id','role_id');
    }

}
