<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CandidateExperience extends Model
{
    use HasFactory;

    protected $table = 'candidate_experience';

    protected $fillable = [
        'candidate_id',
        'company',
        'position',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'location',
        'achievements'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'achievements' => 'array'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_current', false)
                     ->whereNotNull('end_date');
    }

    public function getDurationInMonthsAttribute()
    {
        $endDate = $this->is_current ? Carbon::now() : $this->end_date;
        return $this->start_date->diffInMonths($endDate);
    }

    public function getDurationInYearsAttribute()
    {
        return round($this->duration_in_months / 12, 1);
    }

    public function getDurationTextAttribute()
    {
        if ($this->is_current) {
            return $this->start_date->format('M Y') . ' - Present';
        }
        
        return $this->start_date->format('M Y') . 
               ($this->end_date ? ' - ' . $this->end_date->format('M Y') : '');
    }

    public function getFormattedDurationAttribute()
    {
        $months = $this->duration_in_months;
        $years = floor($months / 12);
        $remainingMonths = $months % 12;

        if ($years > 0 && $remainingMonths > 0) {
            return "{$years} year" . ($years > 1 ? 's' : '') . 
                   " {$remainingMonths} month" . ($remainingMonths > 1 ? 's' : '');
        } elseif ($years > 0) {
            return "{$years} year" . ($years > 1 ? 's' : '');
        } else {
            return "{$months} month" . ($months > 1 ? 's' : '');
        }
    }
}