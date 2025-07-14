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

    // Request Time In Only (Settings Preference)
    public function getTimeOnlyAttribute() // time_only
    {
        if (! $this->request_date_in) {
            return '';
        }

        // Kunin yung first (o iyong naka-use) settings row
        $settings = AttendanceSettings::first();
        $fmt = $settings && $settings->time_display_format == 12
            ? 'g:i A'     // 12-hour format
            : 'H:i';      // 24-hour format

        return $this->request_date_in->format($fmt);
    }

    // Request Time Out only (Settings Preference)
    public function getTimeOutOnlyAttribute() //time_out_only
    {
        if (! $this->request_date_out) {
            return null;
        }

        $settings = AttendanceSettings::first();
        $fmt = $settings && $settings->time_display_format == 12
            ? 'g:i A'
            : 'H:i';

        return $this->request_date_out->format($fmt);
    }

    // Request Total Request Minutes (Formatted)
    public function getTotalRequestMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_request_minutes;

        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} hr";
        }
        if ($mins > 0) {
            $parts[] = "{$mins} min";
        }

        return implode(' ', $parts);
    }

    // Request Total Request ND Minutes (Formatted)
    public function getTotalRequestNdMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_request_nd_minutes;

        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} hr";
        }
        if ($mins > 0) {
            $parts[] = "{$mins} min";
        }

        return implode(' ', $parts);
    }

    // Request Total Break Minutes (Formatted)
    public function getTotalBreakMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_break_minutes;

        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} hr";
        }
        if ($mins > 0) {
            $parts[] = "{$mins} min";
        }

        return implode(' ', $parts);
    }
}
