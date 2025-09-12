<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;

    public function __construct(string $token, string $email)
    {
        $this->url = url("/reset-password?token={$token}&email={$email}");
    }

    public function build()
    {
        return $this->subject('Şifre Sıfırlama Bağlantısı')
                    ->view('emails.reset-password')
                    ->with(['url' => $this->url]);
    }
}
