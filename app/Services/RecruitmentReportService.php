<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\Candidate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecruitmentReportService
{
    public function getRecruitmentDashboardStats($dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ? Carbon::parse($dateFrom) : Carbon::now()->startOfMonth();
        $dateTo = $dateTo ? Carbon::parse($dateTo) : Carbon::now()->endOfMonth();

        return [
            'applications' => $this->getApplicationStats($dateFrom, $dateTo),
            'job_postings' => $this->getJobPostingStats($dateFrom, $dateTo),
            'interviews' => $this->getInterviewStats($dateFrom, $dateTo),
            'offers' => $this->getOfferStats($dateFrom, $dateTo),
            'candidates' => $this->getCandidateStats($dateFrom, $dateTo),
            'time_to_hire' => $this->getTimeToHireStats($dateFrom, $dateTo),
            'source_effectiveness' => $this->getSourceEffectivenessStats($dateFrom, $dateTo)
        ];
    }

    public function getApplicationStats($dateFrom, $dateTo)
    {
        $totalApplications = JobApplication::whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $previousPeriodApplications = JobApplication::whereBetween('applied_at', [
            $dateFrom->copy()->subDays($dateFrom->diffInDays($dateTo)),
            $dateFrom->copy()->subDay()
        ])->count();

        $statusBreakdown = JobApplication::whereBetween('applied_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => $totalApplications,
            'previous_period' => $previousPeriodApplications,
            'growth_rate' => $previousPeriodApplications > 0 ? 
                (($totalApplications - $previousPeriodApplications) / $previousPeriodApplications) * 100 : 0,
            'status_breakdown' => $statusBreakdown,
            'daily_applications' => $this->getDailyApplications($dateFrom, $dateTo)
        ];
    }

    public function getJobPostingStats($dateFrom, $dateTo)
    {
        $totalJobs = JobPosting::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $activeJobs = JobPosting::where('status', 'open')
            ->where('is_active', true)
            ->count();
        
        $jobsByDepartment = JobPosting::whereBetween('created_at', [$dateFrom, $dateTo])
            ->join('departments', 'job_postings.department_id', '=', 'departments.id')
            ->selectRaw('departments.department_name, COUNT(*) as count')
            ->groupBy('departments.department_name')
            ->pluck('count', 'department_name')
            ->toArray();

        $avgApplicationsPerJob = JobPosting::whereBetween('created_at', [$dateFrom, $dateTo])
            ->withCount('applications')
            ->avg('applications_count');

        return [
            'total_created' => $totalJobs,
            'active_jobs' => $activeJobs,
            'jobs_by_department' => $jobsByDepartment,
            'avg_applications_per_job' => round($avgApplicationsPerJob, 2),
            'fill_rate' => $this->calculateFillRate($dateFrom, $dateTo)
        ];
    }

    public function getInterviewStats($dateFrom, $dateTo)
    {
        $totalInterviews = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])->count();
        $completedInterviews = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->count();

        $interviewsByType = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $avgInterviewScore = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
            ->whereNotNull('score')
            ->avg('score');

        return [
            'total_scheduled' => $totalInterviews,
            'completed' => $completedInterviews,
            'completion_rate' => $totalInterviews > 0 ? ($completedInterviews / $totalInterviews) * 100 : 0,
            'interviews_by_type' => $interviewsByType,
            'avg_score' => round($avgInterviewScore, 2),
            'no_show_rate' => $this->calculateNoShowRate($dateFrom, $dateTo)
        ];
    }

    public function getOfferStats($dateFrom, $dateTo)
    {
        $totalOffers = JobOffer::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $acceptedOffers = JobOffer::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'accepted')
            ->count();
        $rejectedOffers = JobOffer::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'rejected')
            ->count();

        $avgSalaryOffered = JobOffer::whereBetween('created_at', [$dateFrom, $dateTo])
            ->avg('salary_offered');

        $avgResponseTime = JobOffer::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('responded_at')
            ->selectRaw('AVG(DATEDIFF(responded_at, sent_at)) as avg_days')
            ->value('avg_days');

        return [
            'total_sent' => $totalOffers,
            'accepted' => $acceptedOffers,
            'rejected' => $rejectedOffers,
            'acceptance_rate' => ($totalOffers - $rejectedOffers) > 0 ? 
                ($acceptedOffers / ($totalOffers - $rejectedOffers)) * 100 : 0,
            'avg_salary_offered' => round($avgSalaryOffered, 2),
            'avg_response_time_days' => round($avgResponseTime, 1)
        ];
    }

    public function getCandidateStats($dateFrom, $dateTo)
    {
        $totalCandidates = Candidate::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        
        $candidatesBySource = Candidate::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('source_type, COUNT(*) as count')
            ->groupBy('source_type')
            ->pluck('count', 'source_type')
            ->toArray();

        $candidatesByStatus = Candidate::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_new' => $totalCandidates,
            'candidates_by_source' => $candidatesBySource,
            'candidates_by_status' => $candidatesByStatus,
            'conversion_rate' => $this->calculateCandidateConversionRate($dateFrom, $dateTo)
        ];
    }

    public function getTimeToHireStats($dateFrom, $dateTo)
    {
        $hiredApplications = JobApplication::where('status', 'hired')
            ->whereBetween('applied_at', [$dateFrom, $dateTo])
            ->get();

        if ($hiredApplications->isEmpty()) {
            return [
                'avg_time_to_hire_days' => 0,
                'median_time_to_hire_days' => 0,
                'min_time_to_hire_days' => 0,
                'max_time_to_hire_days' => 0
            ];
        }

        $timeToHire = $hiredApplications->map(function ($application) {
            return $application->applied_at->diffInDays($application->last_updated_at ?: now());
        })->sort()->values();

        return [
            'avg_time_to_hire_days' => round($timeToHire->avg(), 1),
            'median_time_to_hire_days' => $timeToHire->median(),
            'min_time_to_hire_days' => $timeToHire->min(),
            'max_time_to_hire_days' => $timeToHire->max()
        ];
    }

    public function getSourceEffectivenessStats($dateFrom, $dateTo)
    {
        $sources = Candidate::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('source_type, COUNT(*) as total_candidates')
            ->groupBy('source_type')
            ->get();

        $effectiveness = [];

        foreach ($sources as $source) {
            $hiredCount = Candidate::where('source_type', $source->source_type)
                ->where('status', 'hired')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count();

            $effectiveness[$source->source_type] = [
                'total_candidates' => $source->total_candidates,
                'hired_candidates' => $hiredCount,
                'conversion_rate' => $source->total_candidates > 0 ? 
                    ($hiredCount / $source->total_candidates) * 100 : 0
            ];
        }

        return $effectiveness;
    }

    public function getRecruitmentFunnelStats($dateFrom, $dateTo)
    {
        $applications = JobApplication::whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $underReview = JobApplication::where('status', 'under_review')
            ->whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $shortlisted = JobApplication::where('status', 'shortlisted')
            ->whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $interviewed = JobApplication::whereIn('status', ['interviewed', 'evaluation'])
            ->whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $offered = JobApplication::where('status', 'offer_made')
            ->whereBetween('applied_at', [$dateFrom, $dateTo])->count();
        $hired = JobApplication::where('status', 'hired')
            ->whereBetween('applied_at', [$dateFrom, $dateTo])->count();

        return [
            'applications' => $applications,
            'under_review' => $underReview,
            'shortlisted' => $shortlisted,
            'interviewed' => $interviewed,
            'offered' => $offered,
            'hired' => $hired,
            'conversion_rates' => [
                'application_to_review' => $applications > 0 ? ($underReview / $applications) * 100 : 0,
                'review_to_shortlist' => $underReview > 0 ? ($shortlisted / $underReview) * 100 : 0,
                'shortlist_to_interview' => $shortlisted > 0 ? ($interviewed / $shortlisted) * 100 : 0,
                'interview_to_offer' => $interviewed > 0 ? ($offered / $interviewed) * 100 : 0,
                'offer_to_hire' => $offered > 0 ? ($hired / $offered) * 100 : 0
            ]
        ];
    }

    public function getRecruiterPerformanceReport($dateFrom, $dateTo)
    {
        $recruiters = JobApplication::whereBetween('applied_at', [$dateFrom, $dateTo])
            ->whereNotNull('assigned_recruiter')
            ->with('recruiter')
            ->get()
            ->groupBy('assigned_recruiter');

        $performance = [];

        foreach ($recruiters as $recruiterId => $applications) {
            $recruiter = $applications->first()->recruiter;
            $totalApplications = $applications->count();
            $hiredApplications = $applications->where('status', 'hired')->count();
            $avgTimeToHire = $applications->where('status', 'hired')
                ->map(function ($app) {
                    return $app->applied_at->diffInDays($app->last_updated_at ?: now());
                })->avg();

            $performance[] = [
                'recruiter' => $recruiter,
                'total_applications' => $totalApplications,
                'hired_count' => $hiredApplications,
                'hire_rate' => $totalApplications > 0 ? ($hiredApplications / $totalApplications) * 100 : 0,
                'avg_time_to_hire' => round($avgTimeToHire ?: 0, 1)
            ];
        }

        return collect($performance)->sortByDesc('hire_rate')->values()->toArray();
    }

    protected function getDailyApplications($dateFrom, $dateTo)
    {
        return JobApplication::whereBetween('applied_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(applied_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    protected function calculateFillRate($dateFrom, $dateTo)
    {
        $totalPositions = JobPosting::whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('vacancies');
            
        $filledPositions = JobApplication::where('status', 'hired')
            ->whereHas('jobPosting', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->count();

        return $totalPositions > 0 ? ($filledPositions / $totalPositions) * 100 : 0;
    }

    protected function calculateNoShowRate($dateFrom, $dateTo)
    {
        $totalInterviews = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])->count();
        $noShowInterviews = Interview::whereBetween('scheduled_at', [$dateFrom, $dateTo])
            ->where('status', 'no_show')
            ->count();

        return $totalInterviews > 0 ? ($noShowInterviews / $totalInterviews) * 100 : 0;
    }

    protected function calculateCandidateConversionRate($dateFrom, $dateTo)
    {
        $totalCandidates = Candidate::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $hiredCandidates = Candidate::where('status', 'hired')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        return $totalCandidates > 0 ? ($hiredCandidates / $totalCandidates) * 100 : 0;
    }

    public function generateRecruitmentReport($type, $dateFrom, $dateTo, $format = 'json')
    {
        switch ($type) {
            case 'dashboard':
                $data = $this->getRecruitmentDashboardStats($dateFrom, $dateTo);
                break;
            case 'funnel':
                $data = $this->getRecruitmentFunnelStats($dateFrom, $dateTo);
                break;
            case 'recruiter_performance':
                $data = $this->getRecruiterPerformanceReport($dateFrom, $dateTo);
                break;
            default:
                $data = $this->getRecruitmentDashboardStats($dateFrom, $dateTo);
        }

        if ($format === 'excel') {
            return $this->exportToExcel($data, $type);
        }

        return $data;
    }

    protected function exportToExcel($data, $type)
    {
        // Implementation for Excel export would go here
        // This would use PhpSpreadsheet to generate Excel files
        return $data;
    }
}