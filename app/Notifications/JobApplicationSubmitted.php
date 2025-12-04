<?php

namespace App\Notifications;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $application;
    protected $job;

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application, JobPosting $job)
    {
        $this->application = $application;
        $this->job = $job;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Application Submitted Successfully - ' . $this->job->title)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('Thank you for applying for the position of ' . $this->job->title . ' at our company.')
            ->line('We have successfully received your application.')
            ->line('**Application Details:**')
            ->line('Application Code: ' . $this->application->application_code)
            ->line('Position: ' . $this->job->title)
            ->line('Department: ' . $this->job->department->department_name)
            ->line('Application Date: ' . $this->application->applied_at->format('M d, Y h:i A'))
            ->line('Current Status: ' . ucfirst($this->application->status))
            ->line('')
            ->line('Our HR team will review your application and contact you if you are shortlisted for the next round.')
            ->line('You can check your application status anytime using your application code.')
            ->action('View Application Status', route('career.index'))
            ->line('Thank you for your interest in joining our team!')
            ->salutation('Best regards, HR Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'job_id' => $this->job->id,
            'job_title' => $this->job->title,
            'application_code' => $this->application->application_code,
            'status' => $this->application->status,
        ];
    }
}
