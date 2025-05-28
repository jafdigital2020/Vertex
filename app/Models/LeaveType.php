<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_entitle',
        'accrual_frequency',
        'max_carryover',
        'is_earned',
        'earned_rate',
        'earned_interval',
        'is_paid',
        'status',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];
}
