<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permission';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'role_id',
        'menu_ids',
        'module_ids',
        'data_access_id',
        'user_permission_ids',
        'status',
    ];

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
  
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function data_access_level()
    {
        return $this->hasOne(DataAccessLevel::class, 'id', 'data_access_id');
 
    }
    public function user_permission_access()
    {
        return $this->hasOne(UserPermissionAccess::class, 'user_permission_id', 'id');
 
    }
}
