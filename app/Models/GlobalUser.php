<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\GlobalRole;
use App\Models\Organization;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class GlobalUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'tenant_id',
        'global_role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ✅ SINGLE appends definition
    protected $appends = ['role_data', 'tenant_name'];

    // ✅ Relationship should be belongsTo (correct semantics)
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function global_role()
    {
        return $this->belongsTo(GlobalRole::class, 'global_role_id');
    }

    // ✅ Tenant name accessor
    public function getTenantNameAttribute()
    {
        return $this->tenant?->tenant_name;
    }

    public function getRoleDataAttribute()
    {
        return [
            'role_id'             => 'global_user',
            'menu_ids'            => [],
            'module_ids'          => [],
            'user_permission_ids' => [],
            'status'              => null,
        ];
    }
}
