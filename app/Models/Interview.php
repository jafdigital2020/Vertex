<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Interview extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_code',
        'job_application_id',
        'title',
        'description',
        'type',
        'round',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'status',
        'primary_interviewer',
        'panel_interviewers',
        'agenda',
        'questions',
        'feedback',
        'score',
        'recommendation',
        'actual_start_time',
        'actual_end_time',
        'notes'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'panel_interviewers' => 'array',
        'questions' => 'array',
        'score' => 'decimal:2',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime'
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function candidate()
    {
        return $this->hasOneThrough(Candidate::class, JobApplication::class, 'id', 'id', 'job_application_id', 'candidate_id');
    }

    public function jobPosting()
    {
        return $this->hasOneThrough(JobPosting::class, JobApplication::class, 'id', 'id', 'job_application_id', 'job_posting_id');
    }

    public function primaryInterviewer()
    {
        return $this->belongsTo(User::class, 'primary_interviewer');
    }

    public function getPanelInterviewerUsersAttribute()
    {
        if (!$this->panel_interviewers) {
            return collect();
        }

        return User::whereIn('id', $this->panel_interviewers)->get();
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', Carbon::today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', Carbon::now());
    }

    public function scopeByInterviewer($query, $interviewerId)
    {
        return $query->where('primary_interviewer', $interviewerId)
                     ->orWhereJsonContains('panel_interviewers', $interviewerId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getIsUpcomingAttribute()
    {
        return $this->scheduled_at > Carbon::now();
    }

    public function getIsPastAttribute()
    {
        return $this->scheduled_at < Carbon::now();
    }

    public function getScheduledAtFormattedAttribute()
    {
        return $this->scheduled_at->format('M d, Y g:i A');
    }

    public function getTimeToInterviewAttribute()
    {
        if ($this->is_past) {
            return 'Past';
        }

        return $this->scheduled_at->diffForHumans();
    }

    public function getActualDurationAttribute()
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return null;
        }

        return $this->actual_start_time->diffInMinutes($this->actual_end_time);
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->type));
    }

    public function getRecommendationLabelAttribute()
    {
        if (!$this->recommendation) {
            return null;
        }

        return ucwords(str_replace('_', ' ', $this->recommendation));
    }

    public function getAllInterviewersAttribute()
    {
        $interviewers = collect([$this->primaryInterviewer]);
        
        if ($this->panel_interviewer_users) {
            $interviewers = $interviewers->merge($this->panel_interviewer_users);
        }

        return $interviewers->filter();
    }
}