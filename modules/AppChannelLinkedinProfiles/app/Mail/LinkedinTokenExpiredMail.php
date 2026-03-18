<?php

namespace Modules\AppChannelLinkedinProfiles\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LinkedinTokenExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  object  $user      stdClass with id, fullname, email
     * @param  array   $accounts  array of stdClass account rows (name, category)
     */
    public function __construct(
        public readonly object $user,
        public readonly array  $accounts
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->accounts);

        $subject = $count === 1
            ? __('Action Required: Your LinkedIn Account Needs Reconnection')
            : __('Action Required: :count LinkedIn Accounts Need Reconnection', ['count' => $count]);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'appchannellinkedinprofiles::emails.linkedin-token-expired',
        );
    }
}
