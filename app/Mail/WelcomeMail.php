<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Support\EmailReplacements;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        $templates = app(EmailTemplateService::class);

        return new Envelope(
            subject: $templates->subject(EmailTemplate::SLUG_WELCOME, EmailReplacements::forUser($this->user)),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
        );
    }
}
