<?php

namespace App\Models;

use App\Models\HolidayException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'recurring',
        'month_day',
        'date',
        'is_paid',
        'status',
    ];

    // Attendance relationship
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    // HolidayException relationship
    public function holidayExceptions()
    {
        return $this->hasMany(HolidayException::class);
    }
}
