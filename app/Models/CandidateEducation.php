<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateEducation extends Model
{
    use HasFactory;

    protected $table = 'candidate_education';

    protected $fillable = [
        'candidate_id',
        'institution',
        'degree',
        'field_of_study',
        'start_year',
        'end_year',
        'is_current',
        'grade',
        'description'
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'is_current' => 'boolean'
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
                     ->whereNotNull('end_year');
    }

    public function getDurationAttribute()
    {
        if ($this->is_current) {
            return $this->start_year . ' - Present';
        }
        
        return $this->start_year . ($this->end_year ? ' - ' . $this->end_year : '');
    }
}