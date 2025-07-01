<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'converted_days',
        'rate_per_day',
        'total_amount',
        'conversion_date',
    ];

    /**
     * Get the user that owns the leave conversion.
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Leave Type Relationship
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
