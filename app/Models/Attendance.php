<?php

namespace App\Models;

use App\Models\Holiday;
use App\Models\AttendanceSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'shift_assignment_id',
        'geofence_id',
        'holiday_id',
        'attendance_date',
        'date_time_in',
        'date_time_out',
        'multiple_login',
        'multiple_logout',
        'break_in',
        'break_out',
        'status',
        'time_in_latitude',
        'time_in_longitude',
        'time_in_address',
        'time_out_latitude',
        'time_out_longitude',
        'time_out_address',
        'within_geofence',
        'time_in_photo_path',
        'time_out_photo_path',
        'clock_in_method',
        'clock_out_method',
        'is_rest_day',
        'is_holiday',
        'total_work_minutes',
        'total_late_minutes',
        'late_status_box',
        'total_night_diff_minutes',
        'total_undertime_minutes',
    ];

    protected $casts = [
        'attendance_date'     => 'date',
        'date_time_in'        => 'datetime',
        'date_time_out'       => 'datetime',
        'break_in'            => 'datetime',
        'break_out'           => 'datetime',
        'has_break'           => 'boolean',
        'multiple_login'      => 'array',
        'multiple_logout'     => 'array',
        'within_geofence'     => 'boolean',
        'geofence_radius'     => 'decimal:2',
        'is_rest_day'         => 'boolean',
        'total_late_minutes'  => 'integer', // Store late minutes as an integer
        'total_work_minutes'  => 'integer',  // Store as string (HH:MM:SS)
        'is_holiday'         => 'boolean',
        'total_night_diff_minutes' => 'integer',
        'total_undertime_minutes' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Holiday Accessor
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftList::class);
    }

    public function shiftAssignment()
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    public function attendanceSettings()
    {
        return $this->hasOne(AttendanceSettings::class);
    }

    // Accessors For Clock IN
    public function getDateOnlyAttribute() // date_only
    {
        if (! $this->date_time_in) {
            return '';    // or null, or '-'—whatever placeholder you prefer
        }

        return $this->date_time_in->format('Y-m-d');
    }

    // Time In
    public function getTimeOnlyAttribute() // time_only
    {
        if (! $this->date_time_in) {
            return '';
        }

        // Kunin yung first (o iyong naka-use) settings row
        $settings = AttendanceSettings::first();
        $fmt = $settings && $settings->time_display_format == 12
            ? 'g:i A'     // 12-hour format
            : 'H:i';      // 24-hour format

        return $this->date_time_in->format($fmt);
    }

    // Accessors For Clock OUT
    public function getDateOutOnlyAttribute() //date_out_only
    {
        // Baka null pa siya kung hindi pa naka-clock-out
        return $this->date_time_out
            ? $this->date_time_out->format('Y-m-d')
            : null;
    }

    // TIME only for clock-out based on settings
    public function getTimeOutOnlyAttribute() //time_out_only
    {
        if (! $this->date_time_out) {
            return null;
        }

        $settings = AttendanceSettings::first();
        $fmt = $settings && $settings->time_display_format == 12
            ? 'g:i A'
            : 'H:i';

        return $this->date_time_out->format($fmt);
    }

    // Total Late Formatted By Hour and Minutes 1hr 30mins
    public function getTotalLateFormattedAttribute()
    {
        $minutes = (int) $this->total_late_minutes;

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

    // Total Work Minutes Format
    public function getTotalWorkMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_work_minutes;

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

    public function getTotalNightDiffMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_night_diff_minutes;

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

    public function getTotalUndertimeMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_undertime_minutes;

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

    public function getIsLateAttribute()
    {
        return $this->status === 'late';
    }

    public function getIsWithinGeofenceAttribute()
    {
        return $this->within_geofence === true;
    }

    public function getFirstLoginAttribute()
    {
        if (is_array($this->multiple_logins) && count($this->multiple_logins)) {
            return $this->multiple_logins[0]['in'] ?? null;
        }
        return $this->date_time_in;
    }

    public function getLastLogoutAttribute()
    {
        if (is_array($this->multiple_logins) && count($this->multiple_logins)) {
            return end($this->multiple_logins)['out'] ?? null;
        }
        return $this->date_time_out;
    }
}
