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
        'user_permission_ids',
        'status',
    ];

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }
}
