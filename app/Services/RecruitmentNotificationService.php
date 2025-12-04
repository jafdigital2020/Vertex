<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\JobOffer;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RecruitmentNotificationService
{
    public function notifyApplicationReceived(JobApplication $application)
    {
        $this->sendCandidateEmail(
            $application->candidate->email,
            'Application Received',
            'emails.recruitment.application_received',
            [
                'candidate' => $application->candidate,
                'jobPosting' => $application->jobPosting,
                'application' => $application
            ]
        );

        if ($application->jobPosting->assigned_recruiter) {
            $this->createSystemNotification(
                $application->jobPosting->assigned_recruiter,
                'New Application Received',
                "New application from {$application->candidate->full_name} for {$application->jobPosting->title}",
                'job-application',
                $application->id
            );
        }

        return true;
    }

    public function notifyStatusUpdate(JobApplication $application, $oldStatus, $newStatus, $notes = null)
    {
        $statusMessages = [
            'under_review' => 'Your application is now under review',
            'shortlisted' => 'Congratulations! You have been shortlisted',
            'interview_scheduled' => 'An interview has been scheduled',
            'interviewed' => 'Thank you for the interview',
            'evaluation' => 'Your application is being evaluated',
            'offer_made' => 'Congratulations! We have made you an offer',
            'hired' => 'Welcome to the team!',
            'rejected' => 'Thank you for your interest'
        ];

        $subject = "Application Status Update - {$application->jobPosting->title}";
        $message = $statusMessages[$newStatus] ?? 'Your application status has been updated';

        $this->sendCandidateEmail(
            $application->candidate->email,
            $subject,
            'emails.recruitment.status_update',
            [
                'candidate' => $application->candidate,
                'jobPosting' => $application->jobPosting,
                'application' => $application,
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
                'message' => $message,
                'notes' => $notes
            ]
        );

        return true;
    }

    public function notifyInterviewScheduled(Interview $interview)
    {
        $this->sendCandidateEmail(
            $interview->jobApplication->candidate->email,
            'Interview Scheduled',
            'emails.recruitment.interview_scheduled',
            [
                'candidate' => $interview->jobApplication->candidate,
                'interview' => $interview,
                'jobPosting' => $interview->jobApplication->jobPosting
            ]
        );

        $this->sendInterviewerEmail(
            $interview->primaryInterviewer->email,
            'Interview Scheduled - Interviewer Notification',
            'emails.recruitment.interviewer_notification',
            [
                'interviewer' => $interview->primaryInterviewer,
                'interview' => $interview,
                'candidate' => $interview->jobApplication->candidate,
                'jobPosting' => $interview->jobApplication->jobPosting
            ]
        );

        if ($interview->panel_interviewers) {
            foreach ($interview->panel_interviewer_users as $panelInterviewer) {
                $this->sendInterviewerEmail(
                    $panelInterviewer->email,
                    'Panel Interview Scheduled',
                    'emails.recruitment.panel_interviewer_notification',
                    [
                        'interviewer' => $panelInterviewer,
                        'interview' => $interview,
                        'candidate' => $interview->jobApplication->candidate,
                        'jobPosting' => $interview->jobApplication->jobPosting
                    ]
                );
            }
        }

        return true;
    }

    public function notifyInterviewReminder(Interview $interview, $hoursBeforeInterview = 24)
    {
        if ($interview->scheduled_at->diffInHours(now()) <= $hoursBeforeInterview) {
            $this->sendCandidateEmail(
                $interview->jobApplication->candidate->email,
                'Interview Reminder',
                'emails.recruitment.interview_reminder',
                [
                    'candidate' => $interview->jobApplication->candidate,
                    'interview' => $interview,
                    'jobPosting' => $interview->jobApplication->jobPosting
                ]
            );

            $this->sendInterviewerEmail(
                $interview->primaryInterviewer->email,
                'Interview Reminder - Interviewer',
                'emails.recruitment.interviewer_reminder',
                [
                    'interviewer' => $interview->primaryInterviewer,
                    'interview' => $interview,
                    'candidate' => $interview->jobApplication->candidate
                ]
            );
        }

        return true;
    }

    public function notifyInterviewRescheduled(Interview $interview, $oldDateTime)
    {
        $this->sendCandidateEmail(
            $interview->jobApplication->candidate->email,
            'Interview Rescheduled',
            'emails.recruitment.interview_rescheduled',
            [
                'candidate' => $interview->jobApplication->candidate,
                'interview' => $interview,
                'oldDateTime' => $oldDateTime,
                'newDateTime' => $interview->scheduled_at
            ]
        );

        $this->sendInterviewerEmail(
            $interview->primaryInterviewer->email,
            'Interview Rescheduled - Interviewer Notification',
            'emails.recruitment.interviewer_rescheduled',
            [
                'interviewer' => $interview->primaryInterviewer,
                'interview' => $interview,
                'candidate' => $interview->jobApplication->candidate,
                'oldDateTime' => $oldDateTime,
                'newDateTime' => $interview->scheduled_at
            ]
        );

        return true;
    }

    public function notifyOfferSent(JobOffer $offer)
    {
        $this->sendCandidateEmail(
            $offer->jobApplication->candidate->email,
            'Job Offer',
            'emails.recruitment.offer_sent',
            [
                'candidate' => $offer->jobApplication->candidate,
                'offer' => $offer,
                'jobPosting' => $offer->jobApplication->jobPosting
            ]
        );

        $this->createSystemNotification(
            $offer->prepared_by,
            'Job Offer Sent',
            "Job offer sent to {$offer->jobApplication->candidate->full_name} for {$offer->position_title}",
            'job-offer',
            $offer->id
        );

        return true;
    }

    public function notifyOfferResponse(JobOffer $offer, $accepted)
    {
        $status = $accepted ? 'accepted' : 'rejected';
        $subject = $accepted ? 'Offer Accepted!' : 'Offer Update';

        $this->createSystemNotification(
            $offer->prepared_by,
            "Job Offer {$status}",
            "{$offer->jobApplication->candidate->full_name} has {$status} the offer for {$offer->position_title}",
            'job-offer',
            $offer->id
        );

        if ($offer->approved_by) {
            $this->createSystemNotification(
                $offer->approved_by,
                "Job Offer {$status}",
                "{$offer->jobApplication->candidate->full_name} has {$status} the offer for {$offer->position_title}",
                'job-offer',
                $offer->id
            );
        }

        return true;
    }

    public function notifyOfferExpiring(JobOffer $offer, $daysUntilExpiry)
    {
        if ($daysUntilExpiry <= 3 && $offer->status === 'sent') {
            $this->sendCandidateEmail(
                $offer->jobApplication->candidate->email,
                'Job Offer Expiring Soon',
                'emails.recruitment.offer_expiring',
                [
                    'candidate' => $offer->jobApplication->candidate,
                    'offer' => $offer,
                    'daysUntilExpiry' => $daysUntilExpiry
                ]
            );

            $this->createSystemNotification(
                $offer->prepared_by,
                'Job Offer Expiring',
                "Job offer to {$offer->jobApplication->candidate->full_name} expires in {$daysUntilExpiry} days",
                'job-offer',
                $offer->id
            );
        }

        return true;
    }

    public function notifyNewJobPosting(JobPosting $jobPosting)
    {
        if ($jobPosting->assigned_recruiter) {
            $this->createSystemNotification(
                $jobPosting->assigned_recruiter,
                'New Job Assignment',
                "You have been assigned to manage the job posting: {$jobPosting->title}",
                'job-posting',
                $jobPosting->id
            );
        }

        $hrUsers = User::whereHas('userPermission', function($query) {
            $query->whereRaw("FIND_IN_SET('20', module_ids)");
        })->get();

        foreach ($hrUsers as $user) {
            $this->createSystemNotification(
                $user->id,
                'New Job Posting',
                "New job posting created: {$jobPosting->title} in {$jobPosting->department->department_name}",
                'job-posting',
                $jobPosting->id
            );
        }

        return true;
    }

    public function sendBulkCandidateEmails($candidateEmails, $subject, $template, $data)
    {
        foreach ($candidateEmails as $email) {
            $this->sendCandidateEmail($email, $subject, $template, $data);
        }

        return true;
    }

    public function getTodaysInterviewReminders()
    {
        $todaysInterviews = Interview::whereDate('scheduled_at', today())
            ->where('status', 'scheduled')
            ->with(['jobApplication.candidate', 'primaryInterviewer'])
            ->get();

        foreach ($todaysInterviews as $interview) {
            $this->notifyInterviewReminder($interview, 2); // 2 hours before
        }

        return $todaysInterviews->count();
    }

    public function getExpiringOffers()
    {
        $expiringOffers = JobOffer::where('status', 'sent')
            ->whereBetween('offer_expiry_date', [today(), today()->addDays(3)])
            ->with(['jobApplication.candidate'])
            ->get();

        foreach ($expiringOffers as $offer) {
            $daysUntilExpiry = today()->diffInDays($offer->offer_expiry_date);
            $this->notifyOfferExpiring($offer, $daysUntilExpiry);
        }

        return $expiringOffers->count();
    }

    protected function sendCandidateEmail($email, $subject, $template, $data)
    {
        try {
            Mail::send($template, $data, function ($message) use ($email, $subject) {
                $message->to($email)
                       ->subject($subject);
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send candidate email: " . $e->getMessage());
            return false;
        }
    }

    protected function sendInterviewerEmail($email, $subject, $template, $data)
    {
        try {
            Mail::send($template, $data, function ($message) use ($email, $subject) {
                $message->to($email)
                       ->subject($subject);
            });
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send interviewer email: " . $e->getMessage());
            return false;
        }
    }

    protected function createSystemNotification($userId, $title, $message, $type, $relatedId = null)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_id' => $relatedId,
                'is_read' => false,
                'created_at' => now()
            ]);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to create system notification: " . $e->getMessage());
            return false;
        }
    }
}