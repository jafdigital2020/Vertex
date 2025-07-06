<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialBusiness extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ob_date',
        'date_ob_in',
        'date_ob_out',
        'total_ob_minutes',
        'purpose',
        'status',
        'remarks',
        'current_step',
        'file_attachment',
    ];

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // OB Minutes Formatted
    public function getObMinutesFormattedAttribute()
    {
        $minutes = (int) $this->total_ob_minutes;

        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} hrs";
        }
        if ($mins > 0) {
            $parts[] = "{$mins} mins";
        }

        return implode(' ', $parts);
    }

    // Official Business Approval relationship
    public function officialBusinessApproval()
    {
        return $this->hasMany(OfficialBusinessApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(OfficialBusinessApproval::class)
            ->latestOfMany('acted_at');
    }
}
