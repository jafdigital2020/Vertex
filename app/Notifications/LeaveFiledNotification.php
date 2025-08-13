<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveFiledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $employee;
    protected $leaveRequest;

    public function __construct($employee, $leaveRequest)
    {
        $this->employee      = $employee;
        $this->leaveRequest  = $leaveRequest;

        $this->afterCommit = true;

        $this->onQueue('emails');
    }

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

    public function toMail($notifiable)
    {
        // avoid nulls in blade
        $this->leaveRequest->loadMissing(['leaveType', 'user.personalInformation']);
        $this->employee->loadMissing(['personalInformation', 'employmentDetail']);

        return (new MailMessage)
            ->subject('New Leave Request Filed')
            ->view('tenant.email.leave.leave-request', [
                'approver'     => $notifiable,
                'employee'     => $this->employee,
                'leaveRequest' => $this->leaveRequest,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
