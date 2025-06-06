<?php

namespace App\Models;

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
        'username', // Changed from name to username
        'email',
        'password',
        'organization_code',
        'global_role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function organization()
    {
        return $this->hasOne(Organization::class, 'code', 'organization_code');
    }
     public function global_role()
    {
        return $this->hasOne(GlobalRole::class, 'id', 'global_role_id');
    }
    
}
