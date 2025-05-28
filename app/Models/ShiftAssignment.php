<?php

namespace App\Models;

use App\Models\User;
use App\Models\ShiftList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'type', // 'recurring' or 'custom'
        'start_date', // required for 'recurring'
        'end_date', // optional end_date
        'is_rest_day', // defult false true = rest day entry
        'days_of_week', // array of days for 'recurring' ['mon', 'tue', 'wed']
        'custom_dates', // array of dates for 'custom' ['2023-10-01', '2023-10-02']
        'excluded_dates', // array of excluded_dates
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'custom_dates' => 'array',
        'excluded_dates' => 'array',
        'is_rest_day' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(ShiftList::class, 'shift_id');
    }

    // Rest Day Accessor
    public function getRestDaysAttribute()
    {
        if ($this->type === 'recurring') {
            $allDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            return array_values(array_diff($allDays, $this->days_of_week ?? []));
        }

        return [];
    }

    // Helper
    public function getResolvedShift()
    {
        return $this->shift_id ? ShiftList::find($this->shift_id) : null;
    }
}
