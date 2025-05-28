<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'geotagging_enabled',
        'geofencing_enabled',
        'geofence_buffer',
        'geofence_allowed_geotagging',
        'allow_multiple_clock_ins',
        'require_photo_capture',
        'enable_break_hour_buttons',
        'lunch_break_limit',
        'coffee_break_limit',
        'rest_day_time_in_allowed',
        'enable_late_status_box',
        'maximum_allowed_hours',
        'time_display_format',
        'grace_period',
    ];
}
