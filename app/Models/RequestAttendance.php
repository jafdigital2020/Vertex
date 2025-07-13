<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_date',
        'request_date_in',
        'request_date_out',
        'total_break_minutes',
        'total_request_minutes',
        'total_request_nd_minutes',
        'file_attachment',
        'reason',
        'current_step',
        'status',
    ];

    protected $casts = [
        'request_date' => 'date',
        'request_date_in' => 'datetime',
        'request_date_out' => 'datetime',
        'total_break_minutes' => 'integer',
        'total_request_minutes' => 'integer',
        'total_request_nd_minutes' => 'integer',
        'current_step' => 'integer',
    ];

    // User Relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Request Attendance Approvals
    public function requestAttendanceApprovals()
    {
        return $this->hasMany(RequestAttendanceApproval::class);
    }

    // Get the latest approval for the request attendance
    public function latestApproval()
    {
        return $this->hasOne(RequestAttendanceApproval::class)
            ->latestOfMany('acted_at');
    }
}
