<?php

namespace App\Models;

use App\Models\User;
use App\Models\SalaryRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_id',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'worked_days_per_year',
        'sss_contribution_override',
        'philhealth_contribution_override',
        'pagibig_contribution_override',
        'withholding_tax_override',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function salaryRecord()
    {
        return $this->belongsTo(SalaryRecord::class, 'salary_id');
    }
}
