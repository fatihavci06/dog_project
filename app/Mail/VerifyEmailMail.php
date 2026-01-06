<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerifyEmailMail extends Mailable implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $url;

    public function __construct(User $user)
    {
        $this->user = $user;

        $this->url = url("/api/verify-email/{$user->id}/" . sha1($user->email));
        $this->afterCommit = true;
    }

    public function build()
    {
        return $this->subject('E-posta Adresinizi Doğrulayın')
                    ->view('emails.verify-email')
                    ->with([
                        'user' => $this->user,
                        'url'  => $this->url,
                    ]);
    }
}
