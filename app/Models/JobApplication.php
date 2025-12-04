<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_code',
        'job_posting_id',
        'candidate_id',
        'status',
        'cover_letter',
        'expected_salary',
        'available_start_date',
        'questionnaire_responses',
        'assigned_recruiter',
        'stage',
        'overall_score',
        'recruiter_notes',
        'applied_at',
        'last_updated_at'
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'available_start_date' => 'date',
        'questionnaire_responses' => 'array',
        'overall_score' => 'decimal:2',
        'applied_at' => 'datetime',
        'last_updated_at' => 'datetime'
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function recruiter()
    {
        return $this->belongsTo(User::class, 'assigned_recruiter');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function offers()
    {
        return $this->hasMany(JobOffer::class);
    }

    public function workflowHistory()
    {
        return $this->hasMany(ApplicationWorkflow::class);
    }

    public function latestWorkflow()
    {
        return $this->hasOne(ApplicationWorkflow::class)->latest();
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeByRecruiter($query, $recruiterId)
    {
        return $query->where('assigned_recruiter', $recruiterId);
    }

    public function scopeByJobPosting($query, $jobPostingId)
    {
        return $query->where('job_posting_id', $jobPostingId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('applied_at', '>=', Carbon::now()->subDays($days));
    }

    public function getApplicationAgeAttribute()
    {
        return $this->applied_at->diffForHumans();
    }

    public function getDaysSinceApplicationAttribute()
    {
        return $this->applied_at->diffInDays(Carbon::now());
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getLatestInterviewAttribute()
    {
        return $this->interviews()->latest('scheduled_at')->first();
    }

    public function getActiveOfferAttribute()
    {
        return $this->offers()->whereIn('status', ['draft', 'sent'])->latest()->first();
    }

    public function updateStatus($newStatus, $userId, $notes = null, $reason = null)
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $newStatus,
            'last_updated_at' => Carbon::now()
        ]);

        $this->workflowHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => $userId,
            'notes' => $notes,
            'reason' => $reason
        ]);

        return $this;
    }

    public function canMoveTo($status)
    {
        $allowedTransitions = [
            'applied' => ['under_review', 'rejected'],
            'under_review' => ['shortlisted', 'rejected'],
            'shortlisted' => ['interview_scheduled', 'rejected'],
            'interview_scheduled' => ['interviewed', 'rejected'],
            'interviewed' => ['evaluation', 'rejected'],
            'evaluation' => ['offer_made', 'rejected'],
            'offer_made' => ['offer_accepted', 'offer_rejected'],
            'offer_accepted' => ['hired'],
        ];

        return in_array($status, $allowedTransitions[$this->status] ?? []);
    }
}