<?php

namespace App\Models;

use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZktecoDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'api_url',
        'serial_number',
        'device_type',
        'location',
        'status',
    ];

    // Attendance Log
    public function logs()
    {
        return $this->hasMany(AttendanceLog::class, 'device_id');
    }
}
