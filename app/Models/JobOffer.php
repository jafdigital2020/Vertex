<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_code',
        'job_application_id',
        'position_title',
        'department_id',
        'salary_offered',
        'salary_type',
        'benefits',
        'start_date',
        'employment_type',
        'probation_period_months',
        'terms_conditions',
        'offer_expiry_date',
        'status',
        'prepared_by',
        'approved_by',
        'sent_at',
        'responded_at',
        'candidate_response_notes',
        'offer_letter_path',
        'internal_notes'
    ];

    protected $casts = [
        'salary_offered' => 'decimal:2',
        'benefits' => 'array',
        'start_date' => 'date',
        'offer_expiry_date' => 'date',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime'
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    public function scopeExpired($query)
    {
        return $query->where('offer_expiry_date', '<', Carbon::today())
                     ->where('status', 'sent');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'sent')
                     ->where('offer_expiry_date', '>=', Carbon::today());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getIsExpiredAttribute()
    {
        return $this->offer_expiry_date < Carbon::today() && $this->status === 'sent';
    }

    public function getDaysToExpiryAttribute()
    {
        if ($this->status !== 'sent') {
            return null;
        }

        return Carbon::today()->diffInDays($this->offer_expiry_date, false);
    }

    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }

    public function getEmploymentTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->employment_type));
    }

    public function getSalaryTypeLabelAttribute()
    {
        return ucwords($this->salary_type);
    }

    public function getFormattedSalaryAttribute()
    {
        return number_format($this->salary_offered, 2) . ' ' . $this->salary_type_label;
    }

    public function getOfferLetterUrlAttribute()
    {
        return $this->offer_letter_path ? asset('storage/' . $this->offer_letter_path) : null;
    }

    public function getResponseTimeAttribute()
    {
        if (!$this->sent_at || !$this->responded_at) {
            return null;
        }

        return $this->sent_at->diffInDays($this->responded_at);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => Carbon::now()
        ]);

        return $this;
    }

    public function markAsAccepted($notes = null)
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => Carbon::now(),
            'candidate_response_notes' => $notes
        ]);

        $this->jobApplication->updateStatus('offer_accepted', auth()->id());

        return $this;
    }

    public function markAsRejected($notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'responded_at' => Carbon::now(),
            'candidate_response_notes' => $notes
        ]);

        $this->jobApplication->updateStatus('offer_rejected', auth()->id());

        return $this;
    }

    public function withdraw($notes = null)
    {
        $this->update([
            'status' => 'withdrawn',
            'internal_notes' => $notes
        ]);

        return $this;
    }
}