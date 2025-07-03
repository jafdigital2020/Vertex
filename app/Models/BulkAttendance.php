<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_from',
        'date_to',
        'regular_working_days',
        'regular_working_hours',
        'regular_overtime_hours',
        'regular_nd_hours',
        'regular_nd_overtime_hours',
        'rest_day_work',
        'rest_day_ot',
        'rest_day_nd',
        'regular_holiday_hours',
        'special_holiday_hours',
        'regular_holiday_ot',
        'special_holiday_ot',
        'regular_holiday_nd',
        'special_holiday_nd',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

