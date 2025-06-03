<?php

namespace App\Models;

use App\Models\User;
use App\Models\DeductionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deduction_type_id',
        'type',
        'amount', // Amount of percentage or amount of fixed override
        'frequency', // every_payroll, every_other, one_time
        'effective_start_date', // Start date for the deduction to be effective
        'effective_end_date', // End date for the deduction to be effective (nullable)
        'status', // active, inactive, completed, hold
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $casts = [
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class);
    }

    public function createdBy()
    {
        return $this->morphTo();
    }

    public function updatedBy()
    {
        return $this->morphTo();
    }

    public function getCreatorNameAttribute()
    {
        if ($this->createdBy instanceof \App\Models\User) {
            return $this->createdBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->createdBy instanceof \App\Models\GlobalUser) {
            return $this->createdBy->username ?? 'Unnamed Global User';
        }

        return 'Unknown Creator';
    }

    public function getUpdaterNameAttribute()
    {
        if ($this->updatedBy instanceof \App\Models\User) {
            return $this->updatedBy->personalInformation->first_name ?? 'Unnamed User';
        }

        if ($this->updatedBy instanceof \App\Models\GlobalUser) {
            return $this->updatedBy->username ?? 'Unnamed Global User';
        }

        return 'N/A';
    }
}
