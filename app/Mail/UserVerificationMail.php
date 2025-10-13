<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    public function __construct($user)
    {
        $this->user = $user;
        $this->verificationUrl = url('verify-email/' . $user->verification_token);
    }

    public function build()
    {
        return $this->subject('Verify Your Account')
                    ->view('emails.user_verification');
    }
}