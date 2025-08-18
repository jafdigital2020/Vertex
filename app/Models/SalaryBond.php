<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_record_id',
        'date_issued',
        'amount',
        'payable_in', // Number of Cutoffs to be paid
        'payable_amount',
        'remaining_amount',
        'date_completed',
        'date_claimed',
        'remarks',
        'status',
    ];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Salary Record Relationship
    public function salaryRecord()
    {
        return $this->belongsTo(SalaryRecord::class, 'salary_record_id');
    }
}
