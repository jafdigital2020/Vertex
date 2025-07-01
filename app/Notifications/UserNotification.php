<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database']; // Save to DB for in-app bell display
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => url('/some-link') // optional
        ];
    }
}
