<?php

namespace App\Models;

use App\Models\User;
use App\Models\LoanApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'loan_type',
        'loan_amount',
        'repayment_period',
        'interest_rate',
        'purpose',
        'collateral',
        'request_date',
        'file_attachment',
        'status',
        'current_step',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(LoanApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(LoanApproval::class)
            ->latestOfMany('acted_at');
    }
}
