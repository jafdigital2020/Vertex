<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollBatchUsers extends Model
{
   protected $table = 'payroll_batch_users';
    protected $fillable = [
        'user_id',  
    ];
    public $timestamps = true;

    public function batchSetting()
    {
        return $this->belongsTo(PayrollBatchSettings::class, 'pbsettings_id');
    }
}
