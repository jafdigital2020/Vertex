<?php


namespace App\Console\Commands;

use App\Mail\UserCredentialMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUserCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:send-credentials
                            {email : The user\'s email}
                            {username : The user\'s username}
                            {company : The user\'s company code}
                            {password : The user\'s password}
                            {fullName : The user\'s full name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send user credentials email.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the input arguments
        $email = $this->argument('email');
        $username = $this->argument('username');
        $company = $this->argument('company');
        $password = $this->argument('password');
        $fullName = $this->argument('fullName');

        // Log the input for debugging
        $this->info('Sending credentials email to: ' . $email);
        $this->info('Username: ' . $username);
        $this->info('Company: ' . $company);
        $this->info('Full Name: ' . $fullName);

        // Send the email
        try {
            Mail::to($email)->send(new UserCredentialMail(
                $fullName,
                $company,
                $username,
                $email,
                $password
            ));

            $this->info('Credentials email sent to ' . $email);
        } catch (\Exception $e) {
            $this->error('Error sending email: ' . $e->getMessage());
        }
    }
}
