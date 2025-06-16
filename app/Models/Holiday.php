<?php

namespace App\Models;

use App\Models\Tenant;
use App\Models\Overtime;
use App\Models\HolidayException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', //regular , 'special-working', 'special-non-working'
        'recurring',
        'month_day',
        'date',
        'is_paid',
        'status',
        'tenant_id', // Foreign key for tenant
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

    // Overtime relationship
    public function overtime()
    {
        return $this->hasMany(Overtime::class);
    }

    // Tenant relationship
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
