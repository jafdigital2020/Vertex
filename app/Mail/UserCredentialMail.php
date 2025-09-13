<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class UserCredentialMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;
    public $company_code;
    public $username;
    public $email;
    public $password;

    /**
     * Create a new message instance.
     *
     * @param string $fullName
     * @param string $company_code
     * @param string $username
     * @param string $email
     * @param string $password
     */
    public function __construct($fullName, $company_code, $username, $email, $password)
    {
        $this->fullName = $fullName;
        $this->company_code = $company_code;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Timora Account Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'tenant.email.credentials.credential',
            with: [
                'fullName' => $this->fullName,
                'company_code' => $this->company_code,
                'username' => $this->username,
                'email' => $this->email,
                'password' => $this->password,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
