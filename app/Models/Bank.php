<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'bank_name',
        'bank_code',
        'bank_account_number',
        'bank_remarks',
    ];

    public function employeeBank()
    {
        return $this->hasOne(EmployeeBankDetail::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
