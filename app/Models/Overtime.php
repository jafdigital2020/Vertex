<?php

namespace App\Models;

use App\Models\User;
use App\Models\Holiday;
use App\Models\OvertimeApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'holiday_id',
        'overtime_date',
        'date_ot_in',
        'date_ot_out',
        'ot_in_photo_path',
        'ot_out_photo_path',
        'total_ot_minutes',
        'is_rest_day',
        'is_holiday',
        'status',
        'file_attachment',
        'current_step',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'date_ot_in' => 'datetime',
        'date_ot_out' => 'datetime',
        'is_rest_day' => 'boolean',
        'is_holiday' => 'boolean',
        'current_step' => 'integer',
        'total_ot_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }

    public function overtimeApproval()
    {
        return $this->hasMany(OvertimeApproval::class);
    }

    // total_ot_minutes formatted as "X hr Y min"
    public function getTotalOtMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_ot_minutes;

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
