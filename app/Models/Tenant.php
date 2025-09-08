<?php

namespace App\Models;

use App\Models\Bank;
use App\Models\Policy;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{

    protected $table = 'tenants';

    protected $fillable = [
        'tenant_code',
        'tenant_name',
        'tenant_url',
    ];

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

    // bank relationship
    public function banks()
    {
        return $this->hasMany(Bank::class, 'tenant_id', 'id');
    }

    // Allowance relationship
    public function allowances()
    {
        return $this->hasMany(Allowance::class, 'tenant_id', 'id');
    }

    // Invoice relationship
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'tenant_id', 'id');
    }

    // Payment History relationship
    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'tenant_id', 'id');
    }
}
