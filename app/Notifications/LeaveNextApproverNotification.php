<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveNextApproverNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $leave;

    /**
     * Create a new notification instance.
     */
    public function __construct($leave)
    {
        $this->leave = $leave;
        $this->afterCommit = true;
        $this->onQueue('emails');
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

    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
        ];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->leave->loadMissing(['leaveType', 'user.personalInformation']);

        return (new MailMessage)
            ->subject('Leave Request: Next Approval Needed')
            ->view('tenant.email.leave.leave-next-approver', [
                'approver' => $notifiable,
                'requester' => $this->leave->user,
                'leave' => $this->leave,
                'actedBy' => $this->leave->last_acted_by,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
