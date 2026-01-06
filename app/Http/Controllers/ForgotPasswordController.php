<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\GlobalUser;
use App\Models\PasswordOtp;
use App\Mail\ForgotPasswordOtpMail;
use Illuminate\Support\Facades\Log;


class ForgotPasswordController extends Controller
{
    // Show the email input form
    public function showEmailForm()
    {
        return view('auth.forgot-password');
    }

    // Handle sending OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;

        $user = User::where('email', $email)->first()
                ?? GlobalUser::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found.']);
        }

        $this->generateAndSendOtp($email);

        session(['otp_email' => $email]);

        return redirect()->route('otp-form')
            ->with('status', 'OTP sent to your email.');
    }

    // Show OTP input form
    public function showOtpForm()
    {
        if (!session('otp_email')) {
            return redirect()->route('forgot-password');
        }

        return view('auth.otp-form');
    }

    // Verify the OTP
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        PasswordOtp::where('expires_at', '<', now())->delete();

        $email = session('otp_email');
        $record = PasswordOtp::where('email', $email)->first();

        if (
            !$record ||
            $record->expires_at->isPast() ||
            !Hash::check($request->otp, $record->otp_hash)
        ) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        session(['password_reset_allowed' => true]);

        return redirect()->route('reset-password-form');
    }


    // Resend OTP
    public function resendOtp()
    {
        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('forgot-password');
        }

        $this->generateAndSendOtp($email);

        return back()->with('status', 'OTP resent successfully.');
    }

    // Show password reset form
    public function showResetForm()
    {
        if (!session('password_reset_allowed')) {
            return redirect()->route('forgot-password');
        }

        return view('auth.reset-password-form');
    }

    // Handle password reset
    public function resetPassword(Request $request)
    {
        if (!session('password_reset_allowed')) {
            return redirect()->route('forgot-password');
        }

        $request->validate([
            'password' => 'required|confirmed|min:8'
        ]);

        $email = session('otp_email');

        $user = User::where('email', $email)->first()
            ?? GlobalUser::where('email', $email)->first();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        PasswordOtp::where('email', $email)->delete();

        session()->forget(['otp_email', 'password_reset_allowed']);

        return redirect()->route('login')
            ->with('status', 'Password reset successful.');
    }

    // Private: generate OTP, hash it, save, and send email
    private function generateAndSendOtp($email)
    {
        $otp = random_int(100000, 999999);

            Log::info('Generating OTP', [
        'email' => $email,
        'otp' => $otp, // ❗ REMOVE in production
    ]);

        PasswordOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp_hash'   => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        Mail::to($email)->send(new ForgotPasswordOtpMail($otp));
    }
}
