<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'payroll_type',
        'payroll_period_start',
        'payroll_period_end',
        'total_worked_minutes',
        'total_late_minutes',
        'total_undertime_minutes',
        'total_overtime_minutes',
        'total_night_differential_minutes',
        'total_worked_days',
        'total_absent_days',
        'holiday_pay',
        'overtime_pay',
        'night_differential_pay',
        'late_deduction',
        'undertime_deduction',
        'absent_deduction',
        'earnings',
        'total_earnings',
        'taxable_income',
        'deminimis',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'withholding_tax',
        'loan_deductions',
        'deductions',
        'total_deductions',
        'basic_pay',
        'gross_pay',
        'net_salary',
        'payment_date',
        'processor_id',
        'processor_type',
        'updater_id',
        'updater_type',
        'status',
        'thirteenth_month_pay',
        'total_overtime_night_diff_minutes',
        'overtime_night_diff_pay',
        'restday_pay',
        'overtime_restday_pay',
        'leave_pay',
    ];

    protected $cast = [
        'earnings' => 'array',
        'deminimis' => 'array',
        'loan_deductions' => 'array',
        'deductions' => 'array',
        'payroll_period_start' => 'date',
        'payroll_period_end' => 'date',
        'payment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function processor()
    {
        return $this->morphTo();
    }

    public function updater()
    {
        return $this->morphTo();
    }

    public function getProcessorNameAttribute(): string
    {
        $processor = $this->processor;

        if ($processor instanceof \App\Models\User) {
            // Assuming User has a personalInformation relation with first_name
            return $processor->personalInformation->first_name
                ?? ($processor->name ?? 'Unnamed User');
        }

        if ($processor instanceof \App\Models\GlobalUser) {
            return $processor->username ?? 'Unnamed Global User';
        }

        return 'Unknown Processor';
    }

    public function getUpdaterNameAttribute(): string
    {
        $updater = $this->updater;

        if ($updater instanceof \App\Models\User) {
            return $updater->personalInformation->first_name
                ?? ($updater->name ?? 'Unnamed User');
        }

        if ($updater instanceof \App\Models\GlobalUser) {
            return $updater->username ?? 'Unnamed Global User';
        }

        return 'Unknown Updater';
    }
}
