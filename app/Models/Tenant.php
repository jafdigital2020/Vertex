<?php

namespace App\Models;

use App\Models\Policy;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{

    protected $table = 'tenants';
    public $timestamps = true;


    // holiday relationship
    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    // global user relationship
    public function globalUsers()
    {
        return $this->hasMany(GlobalUser::class, 'tenant_id', 'id');
    }

    // custom field relationship
    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'tenant_id', 'id');
    }

    // policy relationship
    public function policies()
    {
        return $this->hasMany(Policy::class, 'tenant_id', 'id');
    }
}
