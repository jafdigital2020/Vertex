<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationWorkflow extends Model
{
    use HasFactory;

    protected $table = 'application_workflow';

    protected $fillable = [
        'job_application_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
        'reason',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function candidate()
    {
        return $this->hasOneThrough(Candidate::class, JobApplication::class, 'id', 'id', 'job_application_id', 'candidate_id');
    }

    public function getFromStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->from_status));
    }

    public function getToStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->to_status));
    }

    public function getChangeDescriptionAttribute()
    {
        return "Status changed from {$this->from_status_label} to {$this->to_status_label}";
    }

    public function scopeByApplication($query, $applicationId)
    {
        return $query->where('job_application_id', $applicationId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('to_status', $status);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}