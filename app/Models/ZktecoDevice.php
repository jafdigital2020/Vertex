<?php

namespace App\Models;

use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZktecoDevice extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'branch_id',
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
        'connection_method', 
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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
