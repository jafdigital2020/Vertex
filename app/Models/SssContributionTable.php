<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SssContributionTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'range_from',
        'range_to',
        'monthly_salary_credit',
        'employer_regular_ss',
        'employer_mpf',
        'employer_ec',
        'employer_total',
        'employee_regular_ss',
        'employee_mpf',
        'employee_total',
        'total_contribution'
    ];


}
