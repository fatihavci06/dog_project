<?php

namespace App\Mail;

use App\Models\ProfileFlag;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileFlaggedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $flag;

    public function __construct(ProfileFlag $flag)
    {
        $this->flag = $flag;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 Yeni Bir Profil Şikayet Edildi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profile_flagged',
        );
    }
}
