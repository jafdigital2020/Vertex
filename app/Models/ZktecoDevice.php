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
        'last_activity',
        'biotime_server_url',
        'biotime_username',
        'biotime_password',
        'connection_method', // 'direct' or 'biotime'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    protected $hidden = [
        'biotime_password'
    ];

    // Attendance Log
    public function logs()
    {
        return $this->hasMany(AttendanceLog::class, 'device_id');
    }

    // Check if device uses BioTime
    public function usesBioTime()
    {
        return $this->connection_method === 'biotime' &&
            !empty($this->biotime_server_url);
    }

    // Check if device uses direct connection
    public function usesDirect()
    {
        return $this->connection_method === 'direct' ||
            empty($this->connection_method);
    }
}
