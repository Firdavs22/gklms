<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $magicLink;

    public function __construct(
        public User $user,
        public string $token
    ) {
        $this->magicLink = route('magic.login', ['token' => $token]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Вход в личный кабинет GloboKids',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.magic-link',
        );
    }
}
