<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirteenthMonthPay extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'year',
        'from_month',
        'to_month',
        'monthly_breakdown',
        'total_basic_pay',
        'total_deductions',
        'total_thirteenth_month',
        'payment_date',
        'processor_type',
        'processor_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'monthly_breakdown' => 'array',
        'payment_date' => 'date',
        'total_basic_pay' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_thirteenth_month' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->morphTo();
    }

    public function getProcessorNameAttribute(): string
    {
        if (!$this->processor) {
            return 'Unknown Processor';
        }

        if ($this->processor instanceof \App\Models\User) {
            return $this->processor->personalInformation->full_name ?? 'Unnamed User';
        }

        if ($this->processor instanceof \App\Models\GlobalUser) {
            return $this->processor->username ?? 'Unnamed Global User';
        }

        return 'Unknown Processor';
    }
}
