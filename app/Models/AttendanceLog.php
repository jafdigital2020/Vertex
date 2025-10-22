<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ZktecoDevice;

class AttendanceLog extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'employee_id',
        'check_time',
        'status',
        'workcode',
    ];

    protected $casts = [
        'check_time' => 'datetime',
    ];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Zkteco Device Relationship
    public function device()
    {
        return $this->belongsTo(ZktecoDevice::class, 'device_id');
    }
}
