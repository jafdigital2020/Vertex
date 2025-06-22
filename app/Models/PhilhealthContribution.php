<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhilhealthContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'min_salary',
        'max_salary',
        'monthly_premium',
        'employee_share',
        'employer_share',
    ];
}
