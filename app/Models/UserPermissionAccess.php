<?php

namespace App\Models;

use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Model;

class UserPermissionAccess extends Model
{
    protected $table = 'user_permission_access';

    public $timestamps = true;
    protected $fillable = [
        'user_permission_id',
        'access_ids',
    ];
    public function user_permission()
    {
        return $this->belongsTo(UserPermission::class, 'id', 'user_permission_id');
    }
}
