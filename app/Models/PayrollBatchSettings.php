<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollBatchSettings extends Model
{
    protected $table = 'payroll_batch_settings';

    protected $fillable = [
        'name',
        'tenant_id'
    ];

    public $timestamps = true;

    public function batchUsers()
    {
        return $this->hasMany(PayrollBatchUsers::class, 'pbsettings_id');
    }
}
