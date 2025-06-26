<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MandatesContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_id',
        'year',
        'month',
        'cutoff_period',
        'basic_pay',
        'gross_pay',
        'philhealth_contribution',
        'sss_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'status',
    ];
}
