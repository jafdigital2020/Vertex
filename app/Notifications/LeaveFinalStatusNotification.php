<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveFinalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $leave;
    protected $status;
    protected $actedBy;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct($leave, string $status, $actedBy, ?string $comment = null)
    {
        $this->leave = $leave;
        $this->status = $status;
        $this->actedBy = $actedBy;
        $this->comment = $comment;

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


    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->leave->loadMissing(['leaveType', 'user.personalInformation']);
        $this->actedBy->loadMissing('personalInformation');

        return (new MailMessage)
            ->subject($this->subjectLine())
            ->view('tenant.email.leave.leave-requester-decision', [
                'requester' => $notifiable,
                'leave'     => $this->leave,
                'status'    => $this->status,
                'actedBy'   => $this->actedBy,
                'comment'   => $this->comment,
            ]);
    }

    protected function subjectLine(): string
    {
        return match ($this->status) {
            'approved' => 'Leave Request Approved',
            'rejected' => 'Leave Request Rejected',
            default => 'Leave Request Update',
        };
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
